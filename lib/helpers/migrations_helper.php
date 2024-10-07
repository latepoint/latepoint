<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsMigrationsHelper {

	function __construct() {
	}

	public static function migrate_from_version_4(): string {


		$bookings            = new OsBookingModel();
		$bookings_to_migrate = $bookings->where( [ 'order_item_id' => 'IS NOT NULL' ] )->get_results_as_models();
		$report = '';
		foreach ( $bookings_to_migrate as $booking ) {
			$report     .= '<div>Start migrating totals for Booking ID:' . $booking->id . '</div>';
			$order_item = new OsOrderItemModel( $booking->order_item_id );
			if ( $order_item->is_new_record() ) {
				$report .= '<div>Skipping as there is no associated order item</div>';
			} else {
				$order_item->total           = $booking->price;
				$order_item->subtotal        = $booking->subtotal;
				$order_item->coupon_code     = $booking->coupon_code;
				$order_item->coupon_discount = $booking->coupon_discount;
				$tax_total                   = $booking->total - $booking->subtotal - $booking->coupon_discount;
				$order_item->tax_total       = ( $tax_total > 0 ) ? $tax_total : 0;
				$order_item->save();
				if ( $tax_total > 0 ) {
					$order = new OsOrderModel( $order_item->order_id );
					if ( $order->is_new_record() ) {

					}
				}
			}
		}


		$report      = '';
		$bookings    = new OsBookingModel();
		$v4_bookings = $bookings->where( [ 'order_item_id' => 'IS NULL' ] )->get_results_as_models();
		foreach ( $v4_bookings as $booking ) {
			$report                 .= '<div>Start migrating Booking ID:' . $booking->id . '</div>';
			$order                  = new OsOrderModel();
			$order->total           = $booking->price;
			$order->subtotal        = $booking->subtotal;
			$order->customer_id     = $booking->customer_id;
			$order->coupon_code     = $booking->coupon_code;
			$order->coupon_discount = $booking->coupon_discount;
			try {
				$tax_total        = $booking->total - $booking->subtotal - $booking->coupon_discount;
				$order->tax_total = ( $tax_total > 0 ) ? $tax_total : 0;
			} catch ( Exception $e ) {
				$report           .= '<div>Error calculating Total Tax:' . $booking->id . '</div>';
				$order->tax_total = 0;
			}
			$order->ip_address       = $booking->ip_address;
			$order->source_id        = $booking->source_id;
			$order->source_url       = $booking->source_url;
			$order->price_breakdown  = $booking->get_meta_by_key( 'price_breakdown' );
			$order->customer_comment = $booking->customer_comment;
			$order->updated_at       = $booking->updated_at;
			$order->created_at       = $booking->created_at;
			if ( $booking->status == LATEPOINT_BOOKING_STATUS_CANCELLED ) {
				$order->status = LATEPOINT_ORDER_STATUS_CANCELLED;
			} else {
				$order->status = $booking->is_upcoming() ? LATEPOINT_ORDER_STATUS_OPEN : LATEPOINT_ORDER_STATUS_COMPLETED;
			}
			$order->fulfillment_status = ( $order->status == LATEPOINT_ORDER_STATUS_COMPLETED ) ? LATEPOINT_ORDER_FULFILLMENT_STATUS_FULFILLED : LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED;
			$order->payment_status     = $booking->payment_status;
			if ( $order->save() ) {
				$report                      .= '<div>Created Order ID:' . $order->id . '</div>';
				$order_item                  = new OsOrderItemModel();
				$order_item->order_id        = $order->id;
				$order_item->total           = $order->total;
				$order_item->subtotal        = $order->subtotal;
				$order_item->coupon_code     = $order->coupon_code;
				$order_item->coupon_discount = $order->coupon_discount;
				$order_item->tax_total       = $order->tax_total;
				$order_item->variant         = LATEPOINT_ITEM_VARIANT_BOOKING;
				$order_item->updated_at      = $booking->updated_at;
				$order_item->created_at      = $booking->created_at;
				$order_item->item_data       = wp_json_encode( $booking->generate_params_for_booking_form(), true );
				if ( $order_item->save() ) {
					$report                 .= '<div>Created Order Item ID:' . $order_item->id . '</div>';
					$booking->order_item_id = $order_item->id;
					if ( $booking->save() ) {
						$report .= '<div>Migration Finished Booking ID:' . $booking->id . '</div>';
					} else {
						$report .= '<div>Migration Error [booking]</div>';
						OsDebugHelper::log( 'Error updating booking for v4 booking', 'v4_update_migration_error', $booking->get_error_messages() );
					}
				} else {
					$report .= '<div>Migration Error [order item]</div>';
					OsDebugHelper::log( 'Error creating order item for v4 booking', 'v4_update_migration_error', $order_item->get_error_messages() );
				}
			} else {
				$report .= '<div>Migration Error [order]</div>';
				OsDebugHelper::log( 'Error creating order for v4 booking', 'v4_update_migration_error', $order->get_error_messages() );
			}
		}
		$transactions    = new OsTransactionModel();
		$v4_transactions = $transactions->where( [ 'order_id' => 'IS NULL' ] )->get_results_as_models();
		foreach ( $v4_transactions as $transaction ) {
			$report .= '<div>Start migrating Transaction ID:' . $transaction->id . '</div>';
			if ( empty( $transaction->booking_id ) ) {
				continue;
			}
			$booking = new OsBookingModel( $transaction->booking_id );
			if ( $booking->id && $booking->order_item_id ) {
				$order_item = new OsOrderItemModel( $booking->order_item_id );
				if ( $order_item->id && $order_item->order_id ) {
					$transaction->order_id = $order_item->order_id;
					$transaction->kind     = LATEPOINT_TRANSACTION_KIND_CAPTURE;
					if ( ! empty( $transaction->funds_status ) ) {
						switch ( $transaction->funds_status ) {
							case 'captured':
								$transaction->kind = LATEPOINT_TRANSACTION_KIND_CAPTURE;
								break;
							case 'authorized':
								$transaction->kind = LATEPOINT_TRANSACTION_KIND_AUTHORIZATION;
								break;
						}
					}
					if ( $transaction->save() ) {
						$report .= '<div>Migration Finished Transaction ID:' . $transaction->id . '</div>';
					} else {
						$report .= '<div>Migration Error [transaction]</div>';
						OsDebugHelper::log( 'Error updating transaction', 'v4_update_migration_error', $transaction->get_error_messages() );
					}
				} else {
					$report .= '<div>Migration Error [order item ID: ' . $booking->order_item_id . ']</div>';
				}
			} else {
				$report .= '<div>Migration Error [booking ID: ' . $transaction->booking_id . ']</div>';
			}
		}

		// migrate steps
		$report .= '<div>Migrating Steps</div>';
		self::migrate_step_descriptions_from_version_4();
		$report .= '<div>Finished Migrating Steps</div>';

		return $report;
	}

	public static function migrate_step_descriptions_from_version_4(): string {
		global $wpdb;
		$report = '';

		$steps_table = $wpdb->prefix . 'latepoint_step_settings';
		$steps_rows  = $wpdb->get_results( $wpdb->prepare('SELECT label, value, step FROM %i', esc_sql($steps_table) ));
		$steps       = [];

		$conversions = [
			'sub_title'        => 'main_panel_heading',
			'title'            => 'side_panel_heading',
			'description'      => 'side_panel_description',
			'icon_image_id'    => 'side_panel_custom_image_id',
			'use_custom_image' => 'use_custom_image'
		];

		foreach ( $steps_rows as $step ) {
			if ( isset( $conversions[ $step->label ] ) ) {
				$steps[ $step->step ][ $conversions[ $step->label ] ] = $step->value;
			}
		}

		$steps_settings = OsStepsHelper::get_steps_settings();

		foreach ( $steps as $old_step_code => $data ) {
			$new_step_codes = self::clean_up_step_code_from_version_4( $old_step_code );
			if ( ! empty( $new_step_codes ) ) {
				foreach ( $new_step_codes as $step_code ) {
					$steps_settings[ $step_code ]['main_panel_heading']     = $data['main_panel_heading'];
					$steps_settings[ $step_code ]['side_panel_heading']     = $data['side_panel_heading'];
					$steps_settings[ $step_code ]['side_panel_description'] = $data['side_panel_description'];
					if ( $data['use_custom_image'] == 'on' && ! empty( $data['side_panel_custom_image_id'] ) ) {
						$steps_settings[ $step_code ]['side_panel_custom_image_id'] = $data['side_panel_custom_image_id'];
					}
					$report .= '<div>- ' . $step_code . '</div>';
				}
			}
		}
		$steps_support_text = OsSettingsHelper::get_settings_value( 'steps_support_text', '' );
		if ( ! empty( $steps_support_text ) ) {
			$steps_settings['shared']['steps_support_text'] = $steps_support_text;
			$report                                         .= '<div>- Support Text</div>';
		}
		OsStepsHelper::save_steps_settings( $steps_settings );

		return $report;
	}

	public static function clean_up_step_code_from_version_4( string $step_code ): array {
		$steps = [
			'services'                  => [
				'booking__services',
				'booking__service_extras',
				'booking__service_durations',
				'booking__group_bookings',
			],
			'locations'                 => [ 'booking__locations' ],
			'agents'                    => [ 'booking__agents' ],
			'datepicker'                => [ 'booking__datepicker' ],
			'contact'                   => [ 'customer' ],
			'custom_fields_for_booking' => [ 'booking__custom_fields' ],
			'payment'                   => [
				'payment__methods',
				'payment__times',
				'payment__portions',
				'payment__methods',
				'payment__processors',
				'payment__pay'
			],
			'verify'                    => [ 'verify' ],
			'confirmation'              => [ 'confirmation' ]
		];

		return $steps[ $step_code ] ?? [];
	}
}