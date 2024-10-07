<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsOrderIntentHelper {

	public static function generate_continue_intent_url( $order_intent_key ) {
		return OsRouterHelper::build_admin_post_link( [
			'orders',
			'continue_order_intent'
		], [ 'order_intent_key' => $order_intent_key ] );
	}

	public static function get_order_id_from_intent_key( $intent_key ) {
		if ( empty( $intent_key ) ) {
			return false;
		}
		$order_intent = new OsOrderIntentModel();
		$order_intent = $order_intent->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();

		if ( $order_intent && $order_intent->order_id ) {
			return $order_intent->order_id;
		} else {
			return null;
		}
	}

	public static function set_order_intent_data_from_cart( OsOrderIntentModel $order_intent, OsCartModel $cart ): OsOrderIntentModel {

		$cart_items = $cart->get_items();

		$cart_items_data = [];
		foreach ( $cart_items as $cart_item ) {
			$cart_item_data = json_decode( $cart_item->item_data, true );
			// cart item could have been added while user was not logged in, make sure to update customer id when creating order intent
			$cart_item_data['customer_id'] = $order_intent->customer_id;


			$cart_items_data[] = [
				'variant'   => $cart_item->variant,
				'item_data' => $cart_item_data,
				'subtotal' => $cart_item->get_subtotal(),
				'total' => $cart_item->get_total(),
				'coupon_discount' => $cart_item->get_coupon_discount(),
				'coupon_code' => $cart_item->get_coupon_code(),
				'tax_total' => $cart_item->get_tax_total(),
			];
		}

		$order_intent->cart_items_data     = wp_json_encode( $cart_items_data );
		$order_intent->charge_amount       = $cart->amount_to_charge();
		$order_intent->specs_charge_amount = $cart->specs_calculate_amount_to_charge();
		$order_intent->total               = $cart->get_total();
		$order_intent->subtotal            = $cart->get_subtotal();
		$order_intent->tax_total           = $cart->get_tax_total();
		$order_intent->coupon_code         = $cart->get_coupon_code();
		if ( ! empty( $cart->get_coupon_code() ) ) {
			$order_intent->coupon_discount = $cart->get_coupon_discount();
		}


		// hide "payments & credits" row if we are not accepting payments
		$rows_to_hide                  = ( ! OsPaymentsHelper::is_accepting_payments() ) ? [ 'payments' ] : [];
		$order_intent->price_breakdown = wp_json_encode( $cart->generate_price_breakdown_rows( $rows_to_hide ) );
		$order_intent->payment_data    = wp_json_encode( [
			'processor' => $cart->payment_processor,
			'time'      => $cart->payment_time,
			'method'    => $cart->payment_method,
			'portion'   => $cart->payment_portion,
			'token'     => $cart->payment_token
		] );

		/**
		 * Sets order intent from a cart
		 *
		 * @param {OsOrderIntentModel} $order_intent Order intent
		 * @param {OsCartModel} $cart Cart that order intent is using
		 * @returns {OsOrderIntentModel} The filtered order intent object
		 *
		 * @since 5.0.0
		 * @hook set_order_intent_data_from_cart
		 *
		 */
		return apply_filters( 'set_order_intent_data_from_cart', $order_intent, $cart );
	}

	/**
	 * @param OsCartItemModel $cart
	 * @param array $restrictions_data
	 * @param array $presets_data
	 * @param string $booking_form_page_url
	 *
	 * @return OsOrderIntentModel
	 */
	public static function create_or_update_order_intent( OsCartModel $cart, array $restrictions_data = [], array $presets_data = [], string $booking_form_page_url = '' ): OsOrderIntentModel {
		if ( empty( $booking_form_page_url ) ) {
			$booking_form_page_url = wp_get_original_referer();
		}
		$order_intent = new OsOrderIntentModel();
		if ( ! empty( $cart->order_intent_id ) ) {
			$order_intent->load_by_id( $cart->order_intent_id );
		}
		$is_new = $order_intent->is_new_record();

		if ( ! $is_new ) {
			if($order_intent->is_converted()){
				return $order_intent;
			}
			$old_order_intent = clone $order_intent;
		}

		$order_intent->restrictions_data     = wp_json_encode( $restrictions_data );
		$order_intent->presets_data          = wp_json_encode( $presets_data );
		// override only if not empty
		if(!empty($booking_form_page_url)) $order_intent->booking_form_page_url = urldecode( $booking_form_page_url );

		// set customer id from session, do not trust submitted data
		$order_intent->customer_id = OsAuthHelper::get_logged_in_customer_id();

		$order_intent = self::set_order_intent_data_from_cart( $order_intent, $cart );


		/**
		 * Filters order intent right before it's about to be saved when created or updated from cart
		 *
		 * @param {OsOrderIntentModel} $order_intent Order intent to be filtered
		 * @returns {OsOrderIntentModel} The filtered order intent
		 *
		 * @since 5.0.0
		 * @hook latepoint_before_order_intent_save_from_cart
		 *
		 */
		$order_intent = apply_filters( 'latepoint_before_order_intent_save_from_cart', $order_intent );
		if ( $order_intent->save() ) {
			if ( $is_new ) {
				$cart->update_attributes( [ 'order_intent_id' => $order_intent->id ] );
				/**
				 * Order intent is created
				 *
				 * @param {OsOrderIntentModel} $order_intent Instance of order intent model that was created
				 *
				 * @since 5.0.0
				 * @hook latepoint_order_intent_created
				 *
				 */
				do_action( 'latepoint_order_intent_created', $order_intent );
			} else {
				/**
				 * Order intent is updated
				 *
				 * @param {OsOrderIntentModel} $order_intent Updated instance of order intent model
				 * @param {OsOrderIntentModel} $old_order_intent Instance of order intent model before it was updated
				 *
				 * @since 5.0.0
				 * @hook latepoint_order_intent_updated
				 *
				 */
				do_action( 'latepoint_order_intent_updated', $order_intent, $old_order_intent );
			}
		} else {
			$action_type = $is_new ? 'creating' : 'updating';
			OsDebugHelper::log( 'Error ' . $action_type . ' order intent', 'error_saving_order_intent', $order_intent->get_error_messages() );
		}

		return $order_intent;
	}

	public static function get_order_intent_by_intent_key( string $intent_key ) : OsOrderIntentModel {
		$order_intent = new OsOrderIntentModel();
		if(empty($intent_key)) return $order_intent;
		$order_intent = $order_intent->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();
		if(!empty($order_intent)){
			return $order_intent;
		}else{
			return new OsOrderIntentModel();
		}
	}

	public static function is_converted( $order_intent_id ) {
		$order_intent = new OsOrderIntentModel( $order_intent_id );
		if ( ! empty( $order_intent->order_id ) ) {
			return $order_intent->order_id;
		} else {
			return false;
		}
	}

}