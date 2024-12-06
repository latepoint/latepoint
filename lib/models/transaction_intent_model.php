<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsTransactionIntentModel extends OsModel {
	var $id,
		$intent_key,
		$order_id,
		$customer_id,
		$invoice_id,
		$transaction_id,
		$payment_data = '',
		$payment_data_arr,
		$other_data,
		$charge_amount,
		$specs_charge_amount,
		$status,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_TRANSACTION_INTENTS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	protected function params_to_sanitize() {
		return [
			'charge_amount'        => 'money',
		];
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


	public function is_processing(): bool {
		return $this->status == LATEPOINT_TRANSACTION_INTENT_STATUS_PROCESSING;
	}

	public function convert_to_transaction() {
		if($this->is_converted()){
			return $this->transaction_id;
		}

		if($this->is_processing()){
			$this->add_error( 'transaction_intent_error', __('Can not convert to transaction, because transaction intent conversion is being processed', 'latepoint') );
			return false;
		}

		$this->mark_as_processing();

		try {

			// process payment if there is amount due
			$transaction = OsPaymentsHelper::process_payment_for_transaction_intent( $this );
			if(!$transaction || $transaction->status != LATEPOINT_TRANSACTION_STATUS_SUCCEEDED){
				if(!$transaction){
					$this->add_error('transaction_intent_error', __('No payment processor available to process this transaction intent', 'latepoint'));
				}else{
					if ( $transaction->get_error() ) {
						$this->add_error('transaction_intent_error', $transaction->get_error_messages());
					}
				}
				$this->mark_as_new();
				return false;
			}

			/**
			 * Filters transaction right before it's about to be saved when converting from a transaction intent
			 *
			 * @param {OsTransactionModel} $transaction Transaction to be filtered
			 * @returns {OsTransactionModel} The filtered transaction
			 *
			 * @since 5.0.0
			 * @hook latepoint_before_transaction_save_from_transaction_intent
			 *
			 */
			$transaction = apply_filters( 'latepoint_before_transaction_save_from_transaction_intent', $transaction );


			if ( $transaction->save() ) {
				$this->mark_as_converted( $transaction );

				/**
				 * Transaction was created
				 *
				 * @param {OsTransactionModel} $transaction instance of transaction model that was created
				 *
				 * @since 5.0.0
				 * @hook latepoint_transaction_created
				 *
				 */
				do_action( 'latepoint_transaction_created', $transaction );

				return $transaction->id;
			} else {
				$this->add_error( 'transaction_intent_error', $transaction->get_error_messages() );

				$this->mark_as_new();
				return false;
			}
		} catch ( Exception $e ) {
			$this->mark_as_new();
			// translators: %s is the error description
			$this->add_error( 'transaction_intent_error', sprintf(__('Error: %s', 'latepoint'), $e->getMessage() ));
			OsDebugHelper::log( 'Error converting transaction intent to a transaction', 'transaction_intent_error', $e->getMessage() );
			return false;
		}
	}

	public function get_by_intent_key( $intent_key ) {
		return $this->where( [ 'intent_key' => $intent_key ] )->set_limit( 1 )->get_results_as_models();
	}

	public function mark_as_converted( OsTransactionModel $transaction ) {
		if ( empty( $transaction->id ) ) {
			return false;
		}

		$this->update_attributes( [ 'transaction_id' => $transaction->id, 'status' => LATEPOINT_TRANSACTION_INTENT_STATUS_CONVERTED ] );
		/**
		 * Transaction intent is converted to transaction
		 *
		 * @param {OsTransactionIntentModel} $transaction_intent Instance of transaction intent model that has been converted to transaction
		 * @param {OsTransactionModel} $transaction Instance of transaction model that transaction intent was converted to
		 *
		 * @since 5.0.0
		 * @hook latepoint_transaction_intent_converted
		 *
		 */
		do_action( 'latepoint_transaction_intent_converted', $this, $transaction );
	}

	public function mark_as_processing() {
		$this->update_attributes( [ 'status' => LATEPOINT_TRANSACTION_INTENT_STATUS_PROCESSING ] );
		/**
		 * Order intent is marked as processing
		 *
		 * @param {OsTransactionIntentModel} $order_intent Instance of order intent model that has started processing
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_intent_processing
		 *
		 */
		do_action( 'latepoint_order_intent_processing', $this );
	}

	public function mark_as_new() {
		$this->update_attributes( [ 'status' => LATEPOINT_TRANSACTION_INTENT_STATUS_NEW ] );
		/**
		 * Order intent is marked as new
		 *
		 * @param {OsTransactionIntentModel} $order_intent Instance of order intent model that is being marked as new
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_intent_new
		 *
		 */
		do_action( 'latepoint_order_intent_new', $this );
	}

	// Determines if order intent has been converted into a order already
	public function is_converted() : bool {
		if ( empty( $this->transaction_id ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function generate_data_vars(): array {
		$vars = [
			'id'                    => $this->id,
			'intent_key'            => $this->intent_key,
			'payment_data'          => !empty($this->payment_data) ? json_decode( $this->payment_data, true ) : [],
			'order_id'              => $this->order_id,
			'transaction_id'              => $this->transaction_id,
			'updated_at'            => $this->updated_at,
			'created_at'            => $this->created_at,
		];

		return $vars;
	}

	public function get_page_url_with_intent() {
		$booking_page_url      = $this->booking_form_page_url;
		$existing_var_position = strpos( $booking_page_url, 'latepoint_transaction_intent_key=' );
		if ( $existing_var_position === false ) {
			// no intent variable in url
			$question_position = strpos( $booking_page_url, '?' );
			if ( $question_position === false ) {
				// no ?query params
				$hash_position = strpos( $booking_page_url, '#' );
				if ( $hash_position === false ) {
					// no hashtag in url
					$booking_page_url = $booking_page_url . '?latepoint_transaction_intent_key=' . $this->intent_key;
				} else {
					// hashtag in url and no ?query, prepend the hashtag with query
					$booking_page_url = substr_replace( $booking_page_url, '?latepoint_transaction_intent_key=' . $this->intent_key . '#', $hash_position, 1 );
				}
			} else {
				// ?query string exists, add intent key to it
				$booking_page_url = substr_replace( $booking_page_url, '?latepoint_transaction_intent_key=' . $this->intent_key . '&', $question_position, 1 );
			}
		} else {
			// intent key variable exist in url
			preg_match( '/latepoint_transaction_intent_key=([\d,\w]*)/', $booking_page_url, $matches );
			if ( isset( $matches[1] ) ) {
				$booking_page_url = str_replace( 'latepoint_transaction_intent_key=' . $matches[1], 'latepoint_transaction_intent_key=' . $this->intent_key, $booking_page_url );
			}
		}

		return $booking_page_url;
	}

	public function generate_intent_key() {
		$this->intent_key = bin2hex( openssl_random_pseudo_bytes( 10 ) );
	}


	protected function before_create() {
		if ( empty( $this->intent_key ) ) {
			$this->intent_key = bin2hex( openssl_random_pseudo_bytes( 10 ) );
		}
		if ( empty( $this->status ) ) {
			$this->status = LATEPOINT_TRANSACTION_INTENT_STATUS_NEW;
		}
	}

	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'payment_data',
			'intent_key',
			'order_id',
			'customer_id',
			'invoice_id',
			'transaction_id',
			'status',
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'payment_data',
			'intent_key',
			'charge_amount',
			'specs_charge_amount',
			'order_id',
			'customer_id',
			'invoice_id',
			'transaction_id',
			'status',
		);

		return $params_to_save;
	}


	protected function properties_to_validate() {
		$validations = array(
			'order_id' => array( 'presence' ),
		);

		return $validations;
	}
}