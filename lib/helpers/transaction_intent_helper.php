<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsTransactionIntentHelper {


	public static function generate_continue_intent_url( $transaction_intent_key ) {
		return OsRouterHelper::build_admin_post_link( [
			'orders',
			'continue_transaction_intent'
		], [ 'transaction_intent_key' => $transaction_intent_key ] );
	}


	public static function get_order_id_from_intent_key( $intent_key ) {
		if ( empty( $intent_key ) ) {
			return false;
		}
		$transaction_intent = new OsTransactionIntentModel();
		$transaction_intent = $transaction_intent->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();

		if ( $transaction_intent && $transaction_intent->order_id ) {
			return $transaction_intent->order_id;
		} else {
			return null;
		}
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

	public static function get_transaction_intent_by_intent_key( string $intent_key ) : OsTransactionIntentModel {
		$transaction_intent = new OsTransactionIntentModel();
		if(empty($intent_key)) return $transaction_intent;
		$transaction_intent = $transaction_intent->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();
		if(!empty($transaction_intent)){
			return $transaction_intent;
		}else{
			return new OsTransactionIntentModel();
		}
	}

	public static function is_converted( $transaction_intent_id ) {
		$transaction_intent = new OsTransactionIntentModel( $transaction_intent_id );
		if ( ! empty( $transaction_intent->transaction_id ) ) {
			return $transaction_intent->transaction_id;
		} else {
			return false;
		}
	}

}