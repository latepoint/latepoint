<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsOrderIntentModel extends OsModel {
	var $id,
		$intent_key,
		$customer_id,
		$booking_form_page_url,
		$cart_items_data,
		$restrictions_data,
		$presets_data,
		$payment_data,
		$payment_data_arr,
		$other_data,
		$charge_amount,
		$specs_charge_amount,
		$coupon_code,
		$coupon_discount,
		$total,
		$subtotal,
		$price_breakdown,
		$order_id,
		$tax_total,
		$status,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_ORDER_INTENTS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}


	public function delete_meta_by_key( $meta_key ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsOrderIntentMetaModel();

		return $meta->delete_by_key( $meta_key, $this->id );
	}

	public function get_meta_by_key( $meta_key, $default = false ) {
		if ( $this->is_new_record() ) {
			return $default;
		}

		$meta = new OsOrderIntentMetaModel();

		return $meta->get_by_key( $meta_key, $this->id, $default );
	}

	public function save_meta_by_key( $meta_key, $meta_value ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsOrderIntentMetaModel();

		return $meta->save_by_key( $meta_key, $meta_value, $this->id );
	}

	public function get_customer() {
		if ( $this->customer_id ) {
			if ( ! isset( $this->customer ) || ( isset( $this->customer ) && ( $this->customer->id != $this->customer_id ) ) ) {
				$this->customer = new OsCustomerModel( $this->customer_id );
			}
		} else {
			$this->customer = new OsCustomerModel();
		}

		return $this->customer;
	}

	public function build_cart_object(): OsCartModel {
		$cart                  = new OsCartModel();
		$cart->total           = $this->total;
		$cart->subtotal        = $this->subtotal;
		$cart->coupon_code     = $this->coupon_code;
		$cart->coupon_discount = $this->coupon_discount;
		$cart->tax_total       = $this->tax_total;

		// add items from intent
		$intent_cart_items = json_decode( $this->cart_items_data, true );
		foreach ( $intent_cart_items as $cart_item_data ) {
			$cart->add_item( OsCartsHelper::create_cart_item_from_item_data( $cart_item_data ), false );
		}

		// restore payment info
		$payment_data            = json_decode( $this->payment_data, true );
		$cart->payment_method    = $payment_data['method'];
		$cart->payment_time      = $payment_data['time'];
		$cart->payment_portion   = $payment_data['portion'];
		$cart->payment_token     = $payment_data['token'];
		$cart->payment_processor = $payment_data['processor'];

		return $cart;
	}

	public function get_payment_data_value( string $key ): string {
		if ( ! isset( $this->payment_data_arr ) ) {
			$this->payment_data_arr = json_decode( $this->payment_data, true );
		}

		return $this->payment_data_arr[ $key ] ?? '';
	}

	public function set_payment_data_value( string $key, string $value, bool $save = true ) {
		$this->payment_data_arr         = json_decode( $this->payment_data, true );
		$this->payment_data_arr[ $key ] = $value;
		$this->payment_data             = wp_json_encode( $this->payment_data_arr );
		if ( $save ) {
			$this->update_attributes( [ 'payment_data' => $this->payment_data ] );
		}
	}

	public function is_bookable(): bool {
		$cart = $this->build_cart_object();
		// loop items and check if bookings are still available
		foreach ( $cart->get_items() as $cart_item ) {
			switch ( $cart_item->variant ) {
				case LATEPOINT_ITEM_VARIANT_BOOKING:
					$booking = $cart_item->build_original_object_from_item_data();
					if ( ! $booking->is_bookable( false ) ) {
						$this->add_error( 'send_to_step', $booking->get_error_messages(), 'booking__datepicker' );

						return false;
					}
					break;
				case LATEPOINT_ITEM_VARIANT_BUNDLE:
					break;
			}
		}

		return true;
	}

	public function is_processing(): bool {
		return $this->status == LATEPOINT_ORDER_INTENT_STATUS_PROCESSING;
	}

	public function convert_to_order() {
		if($this->is_converted()){
			return $this->order_id;
		}

		if($this->is_processing()){
			$this->add_error( 'order_error', 'Can not convert to order, because order intent conversion is being processed' );
			return false;
		}

		$this->mark_as_processing();

		try {

			// process is cart -> order intent -> order
			if ( ! $this->is_bookable() ) {
				$this->mark_as_new();
				return false;
			}

			// process payment if there is amount due
			$transaction = OsPaymentsHelper::process_payment_for_order_intent( $this );

			// payment processing can take a while, make sure to check if the intent wasn't converted already in the meantime
			$converted_order_id = OsOrderIntentHelper::is_converted( $this->id );
			if ( $converted_order_id ) {
				$order = new OsOrderModel($converted_order_id);
				$this->mark_as_converted($order);
				return $converted_order_id;
			}

			if ( $this->get_error() ) {
				OsDebugHelper::log( 'Error converting intent to an order', 'order_error', $this->get_error_messages() );

				$this->mark_as_new();
				return false;
			}


			$cart_from_intent = $this->build_cart_object();

			$order                      = new OsOrderModel();
			$order->customer_id         = $this->customer_id;
			$order->total               = $this->total;
			$order->subtotal            = $this->subtotal;
			$order->coupon_code         = $this->coupon_code;
			$order->coupon_discount     = $this->coupon_discount;
			$order->tax_total           = $this->tax_total;
			$order->source_url          = $this->booking_form_page_url;
			$order->customer_comment    = $this->customer->notes;
			$payment_data_arr = json_decode( $this->payment_data, true );
			$payment_data_arr['initial_charge_amount'] = $this->charge_amount;
			$order->payment_data        = wp_json_encode($payment_data_arr);
			// order's price breakdown should only hold cart items, and never holds total, subtotal, balance variables because those are stored on order model itself and/or generated on the fly
			$order->price_breakdown = wp_json_encode( $cart_from_intent->generate_price_breakdown_rows(['balance', 'total', 'subtotal']));

			/**
			 * Filters order right before it's about to be saved when converting from an order intent
			 *
			 * @param {OsOrderModel} $order Order to be filtered
			 * @returns {OsOrderModel} The filtered order
			 *
			 * @since 5.0.0
			 * @hook latepoint_before_order_save_from_order_intent
			 *
			 */
			$order = apply_filters( 'latepoint_before_order_save_from_order_intent', $order );


			if ( $order->save() ) {
				$this->mark_as_converted( $order );


				foreach ( $cart_from_intent->get_items() as $cart_item ) {
					$order_item           = OsOrdersHelper::create_order_item_from_cart_item( $cart_item );
					$order_item->order_id = $order->id;
					$order_item->save();
				}

				if ( $transaction ) {
					$transaction->order_id = $order->id;
					if ( $transaction->save() ) {
						do_action( 'latepoint_transaction_created', $transaction );
					} else {
						OsDebugHelper::log( 'Error creating transaction', 'transaction_error', $transaction->get_error_messages() );
					}
				}
				$order_bookings = $order->get_bookings_from_order_items();
				if ( $order_bookings ) {
					foreach ( $order_bookings as $order_item_id => $order_booking ) {
						$order_booking->order_item_id = $order_item_id;
						$order_booking->customer_id   = $order->customer_id;
						$order_booking->end_time      = $order_booking->calculate_end_time();
						$order_booking->end_date      = $order_booking->calculate_end_date();
						$order_booking->set_utc_datetimes();
						$service                         = new OsServiceModel( $order_booking->service_id );
						$order_booking->buffer_before    = $service->buffer_before;
						$order_booking->buffer_after     = $service->buffer_after;
						$order_booking->customer_comment = $order->customer->notes;
						if ( $order_booking->save() ) {

							/**
							 * Booking was created
							 *
							 * @param {OsBookingModel} $booking instance of booking model that was created
							 *
							 * @since 5.0.0
							 * @hook latepoint_booking_created
							 *
							 */
							do_action( 'latepoint_booking_created', $order_booking );
							// set booking id to the one that was created for item data property
							$order_item      = new OsOrderItemModel( $order_item_id );
							$item_data       = json_decode( $order_item->item_data, true );
							$item_data['id'] = $order_booking->id;
							$order_item->update_attributes( [ 'item_data' => wp_json_encode( $item_data ) ] );
						} else {
							OsDebugHelper::log( 'Unable to save booking', 'booking_save_error', $order_booking->get_error_messages() );
						}
					}
				}
				// update connected cart with created order id
				$carts = new OsCartModel();
				$carts = $carts->where( [ 'order_intent_id' => $this->id ] )->get_results_as_models();
				if ( ! empty( $carts ) ) {
					foreach ( $carts as $cart ) {
						$cart->order_id = $order->id;
						$cart->save();
					}
				}
				$order->determine_payment_status();
				$order->items = OsOrdersHelper::get_items_for_order_id( $order->id );

				/**
				 * Order was created
				 *
				 * @param {OsOrderModel} $order instance of order model that was created
				 *
				 * @since 5.0.0
				 * @hook latepoint_order_created
				 *
				 */
				do_action( 'latepoint_order_created', $order );

				return $order->id;
			} else {
				$this->add_error( 'order_error', $order->get_error_messages() );

				$this->mark_as_new();
				return false;
			}
		} catch ( Exception $e ) {
			$this->mark_as_new();
			// translators: %s is the error description
			$this->add_error( 'order_error', sprintf(__('Error: %s', 'latepoint'), $e->getMessage() ));
			OsDebugHelper::log( 'Error converting intent to an order', 'order_error', $e->getMessage() );
			return false;
		}
	}

	public function get_by_intent_key( $intent_key ) {
		return $this->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();
	}

	public function mark_as_converted( OsOrderModel $order ) {
		if ( empty( $order->id ) ) {
			return false;
		}

		$this->update_attributes( [ 'order_id' => $order->id, 'status' => LATEPOINT_ORDER_INTENT_STATUS_CONVERTED ] );
		/**
		 * Order intent is converted to order
		 *
		 * @param {OsOrderIntentModel} $order_intent Instance of order intent model that has been converted to order
		 * @param {OsOrderModel} $order Instance of order model that order intent was converted to
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_intent_converted
		 *
		 */
		do_action( 'latepoint_order_intent_converted', $this, $order );
	}

	public function mark_as_processing() {
		$this->update_attributes( [ 'status' => LATEPOINT_ORDER_INTENT_STATUS_PROCESSING ] );
		/**
		 * Order intent is marked as processing
		 *
		 * @param {OsOrderIntentModel} $order_intent Instance of order intent model that has started processing
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_intent_processing
		 *
		 */
		do_action( 'latepoint_order_intent_processing', $this );
	}

	public function mark_as_new() {
		$this->update_attributes( [ 'status' => LATEPOINT_ORDER_INTENT_STATUS_NEW ] );
		/**
		 * Order intent is marked as new
		 *
		 * @param {OsOrderIntentModel} $order_intent Instance of order intent model that is being marked as new
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_intent_new
		 *
		 */
		do_action( 'latepoint_order_intent_new', $this );
	}

	// Determines if order intent has been converted into a order already
	public function is_converted() : bool {
		if ( empty( $this->order_id ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function generate_data_vars(): array {
		$vars = [
			'id'                    => $this->id,
			'intent_key'            => $this->intent_key,
			'customer_id'           => $this->customer_id,
			'booking_form_page_url' => $this->booking_form_page_url,
			'cart_items_data'       => !empty($this->cart_items_data) ? json_decode( $this->cart_items_data, true ) : [],
			'restrictions_data'     => !empty($this->restrictions_data) ? json_decode( $this->restrictions_data, true ) : [],
			'presets_data'          => !empty($this->presets_data) ? json_decode( $this->presets_data, true ) : [],
			'payment_data'          => !empty($this->payment_data) ? json_decode( $this->payment_data, true ) : [],
			'other_data'            => !empty($this->other_data) ? json_decode( $this->other_data, true ) : [],
			'order_id'              => $this->order_id,
			'coupon_code'           => $this->coupon_code,
			'coupon_discount'       => $this->coupon_discount,
			'tax_total'             => $this->tax_total,
			'updated_at'            => $this->updated_at,
			'created_at'            => $this->created_at,
		];

		return $vars;
	}

	public function get_page_url_with_intent() {
		$booking_page_url      = $this->booking_form_page_url;
		$existing_var_position = strpos( $booking_page_url, 'latepoint_order_intent_key=' );
		if ( $existing_var_position === false ) {
			// no intent variable in url
			$question_position = strpos( $booking_page_url, '?' );
			if ( $question_position === false ) {
				// no ?query params
				$hash_position = strpos( $booking_page_url, '#' );
				if ( $hash_position === false ) {
					// no hashtag in url
					$booking_page_url = $booking_page_url . '?latepoint_order_intent_key=' . $this->intent_key;
				} else {
					// hashtag in url and no ?query, prepend the hashtag with query
					$booking_page_url = substr_replace( $booking_page_url, '?latepoint_order_intent_key=' . $this->intent_key . '#', $hash_position, 1 );
				}
			} else {
				// ?query string exists, add intent key to it
				$booking_page_url = substr_replace( $booking_page_url, '?latepoint_order_intent_key=' . $this->intent_key . '&', $question_position, 1 );
			}
		} else {
			// intent key variable exist in url
			preg_match( '/latepoint_order_intent_key=([\d,\w]*)/', $booking_page_url, $matches );
			if ( isset( $matches[1] ) ) {
				$booking_page_url = str_replace( 'latepoint_order_intent_key=' . $matches[1], 'latepoint_order_intent_key=' . $this->intent_key, $booking_page_url );
			}
		}

		return $booking_page_url;
	}


	protected function before_create() {
		if ( empty( $this->intent_key ) ) {
			$this->intent_key = bin2hex( openssl_random_pseudo_bytes( 10 ) );
		}
		if ( empty( $this->status ) ) {
			$this->status = LATEPOINT_ORDER_INTENT_STATUS_NEW;
		}
	}

	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'customer_id',
			'cart_items_data',
			'restrictions_data',
			'presets_data',
			'payment_data',
			'other_data',
			'booking_form_page_url',
			'intent_key',
			'order_id',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'status',
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'customer_id',
			'cart_items_data',
			'restrictions_data',
			'presets_data',
			'payment_data',
			'other_data',
			'booking_form_page_url',
			'intent_key',
			'total',
			'subtotal',
			'charge_amount',
			'specs_charge_amount',
			'price_breakdown',
			'order_id',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'status',
		);

		return $params_to_save;
	}


	protected function properties_to_validate() {
		$validations = array(
			'customer_id' => array( 'presence' ),
		);

		return $validations;
	}
}