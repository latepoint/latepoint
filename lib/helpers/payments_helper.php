<?php

class OsPaymentsHelper {

	public static function get_payment_processors_for_select( $enabled_only = false, $include_other = false ) {
		$processors_for_select = [];
		$processors            = self::get_payment_processors( $enabled_only );
		foreach ( $processors as $processor ) {
			$processors_for_select[ $processor['code'] ] = $processor['name'];
		}
		if ( $include_other ) {
			$processors_for_select['other'] = __( 'Other', 'latepoint' );
		}

		return apply_filters( 'latepoint_payment_processors_for_select', $processors_for_select, $enabled_only, $include_other );
	}

	public static function get_payment_processors( bool $enabled_only = false ) {
		$payment_processors = [];

		/**
		 * Result an array of registered payment processors, array looks like this:
		 * $payment_processors['stripe'] = ['code' => 'stripe', 'name' => 'Stripe', 'image_url' => 'URL_TO_LOGO'];
		 *
		 * @param {array} $payment_processors The array of payment processors
		 *
		 * @returns {array} The array of payment processors
		 * @since 5.0.0
		 * @hook latepoint_payment_processors
		 *
		 */
		$payment_processors = apply_filters( 'latepoint_payment_processors', $payment_processors );
		if ( $enabled_only ) {
			$enabled_processors = [];
			foreach ( $payment_processors as $payment_processor ) {
				if ( OsPaymentsHelper::is_payment_processor_enabled( $payment_processor['code'] ?? '' ) ) {
					$enabled_processors[] = $payment_processor;
				}
			}
			$payment_processors = $enabled_processors;
		}

		return $payment_processors;
	}

	public static function get_nice_payment_method_name( $code ) {
		$payment_methods = OsPaymentsHelper::get_all_payment_methods_for_select();

		return $payment_methods[ $code ] ?? $code;
	}


	public static function get_nice_payment_processor_name( $code ) {
		$processors = OsPaymentsHelper::get_payment_processors();

		return $processors[ $code ]['name'] ?? $code;
	}

	public static function get_enabled_payment_processors() {
		return self::get_payment_processors( true );
	}

	public static function is_local_payments_enabled() {
		return OsSettingsHelper::is_on( 'enable_payments_local' );
	}

	public static function is_accepting_payments(): bool {
		$enabled_payment_methods = self::get_enabled_payment_methods_for_payment_time( LATEPOINT_PAYMENT_TIME_NOW );
		return ! empty( $enabled_payment_methods );
	}

	public static function is_payment_processor_enabled( $processor_code ) {
		return OsSettingsHelper::is_on( 'enable_payment_processor_' . $processor_code );
	}

	public static function should_processor_handle_payment_for_transaction_intent( string $processor_code, OsTransactionIntentModel $transaction_intent ): bool {
		if ( $transaction_intent->get_payment_data_value('processor') != $processor_code ) {
			return false;
		}
		$payment_times = self::get_enabled_payment_times();

		return ! empty( $payment_times[ LATEPOINT_PAYMENT_TIME_NOW ][ $transaction_intent->get_payment_data_value('method') ][ $transaction_intent->get_payment_data_value('processor' )] );
	}

	public static function should_processor_handle_payment_for_cart( string $processor_code, OsCartModel $cart ): bool {
		if ( $cart->payment_processor != $processor_code ) {
			return false;
		}
		$payment_times = self::get_enabled_payment_times();

		return ! empty( $payment_times[ $cart->payment_time ][ $cart->payment_method ][ $cart->payment_processor ] );
	}

	public static function should_processor_handle_payment_for_order_intent( string $processor_code, OsOrderIntentModel $order_intent ): bool {
		$cart                    = new OsCartModel();
		$cart->payment_processor = $order_intent->get_payment_data_value( 'processor' );
		$cart->payment_time      = $order_intent->get_payment_data_value( 'time' );
		$cart->payment_method    = $order_intent->get_payment_data_value( 'method' );
		$cart->payment_portion   = $order_intent->get_payment_data_value( 'portion' );

		return self::should_processor_handle_payment_for_cart( $processor_code, $cart );
	}

	public static function is_cart_payment_enabled( OsCartModel $cart ): bool {
		$payment_times = self::get_enabled_payment_times();

		return ! empty( $payment_times[ $cart->payment_time ][ $cart->payment_method ][ $cart->payment_processor ] );
	}

	public static function get_all_payment_methods_for_select(): array {
		$payment_methods_for_select = [];
		$payment_times              = self::get_all_payment_times();
		foreach ( $payment_times as $payment_time_code => $payment_time_methods ) {
			foreach ( $payment_time_methods as $payment_time_method_code => $payment_time_processors ) {
				foreach ( $payment_time_processors as $payment_time_processor_code => $payment_time_method ) {
					$payment_methods_for_select[ $payment_time_method_code ] = $payment_time_method['label'];
				}
			}
		}

		$payment_methods_for_select['other'] = __( 'Other', 'latepoint' );

		/**
		 * List of all payment methods for select field
		 *
		 * @param {array} $payment_methods_for_select Array of payment methods for select field
		 *
		 * @returns {array} Filtered array of payment methods for select field
		 * @since 5.0.0
		 * @hook latepoint_all_payment_methods_for_select
		 *
		 */
		return apply_filters( 'latepoint_all_payment_methods_for_select', $payment_methods_for_select );
	}

	public static function get_local_payment_method_info(): array {
		return [
			'code'      => LATEPOINT_PAYMENT_METHOD_LOCAL,
			'label'     => __( 'Pay Locally', 'latepoint' ),
			'image_url' => LATEPOINT_IMAGES_URL . 'payment_later.png'
		];
	}


	public static function get_enabled_payment_methods_for_payment_time( string $payment_time ): array {
		$enabled_payment_methods_for_time = [];

		$enabled_payment_times = self::get_enabled_payment_times();
		if ( ! empty( $enabled_payment_times[ $payment_time ] ) ) {
			$enabled_payment_methods_for_time = $enabled_payment_times[ $payment_time ];
		}

		/**
		 * List of enabled payment methods for a specific payment time
		 *
		 * @param {array} $enabled_payment_methods_for_time Array of enabled payment methods for requested time
		 * @param {string} $payment_time Payment time that should be supported by payment methods
		 *
		 * @returns {array} Filtered array of enabled payment methods for requested time type
		 * @since 5.0.0
		 * @hook latepoint_enabled_payment_methods_for_payment_time
		 *
		 */
		return apply_filters( 'latepoint_enabled_payment_methods_for_payment_time', $enabled_payment_methods_for_time, $payment_time );
	}

	public static function get_enabled_payment_processors_for_payment_time_and_method( string $payment_time, string $payment_method ): array {
		$enabled_payment_processors_for_payment_time_and_method = [];

		$enabled_payment_methods_for_time = self::get_enabled_payment_methods_for_payment_time( $payment_time );
		if ( ! empty( $enabled_payment_methods_for_time[ $payment_method ] ) ) {
			$enabled_payment_processors_for_payment_time_and_method = $enabled_payment_methods_for_time[ $payment_method ];
		}

		/**
		 * List of enabled payment processors for a specific payment time and method
		 *
		 * @param {array} $enabled_payment_processors_for_payment_time_and_method Array of enabled payment processors for requested time and method
		 * @param {string} $payment_time Payment time that should be supported by payment processors
		 * @param {string} $payment_method Payment method that should be supported by payment processors
		 *
		 * @returns {array} Filtered array of enabled payment processors for requested time and method
		 * @since 5.0.0
		 * @hook latepoint_enabled_payment_processors_for_payment_time_and_method
		 *
		 */
		return apply_filters( 'latepoint_enabled_payment_processors_for_payment_time_and_method', $enabled_payment_processors_for_payment_time_and_method, $payment_time, $payment_method );
	}


	public static function get_all_payment_times(): array {
		$payment_times                                                                                = [
			LATEPOINT_PAYMENT_TIME_NOW   => [],
			LATEPOINT_PAYMENT_TIME_LATER => []
		];
		$payment_times[ LATEPOINT_PAYMENT_TIME_LATER ][ LATEPOINT_PAYMENT_METHOD_LOCAL ]['latepoint'] = self::get_local_payment_method_info();


		/**
		 * List of all payment times
		 *
		 * @param {array} $payment_times Array of payment times
		 *
		 * @returns {array} Filtered array of enabled payment times
		 * @since 5.0.0
		 * @hook latepoint_get_all_payment_times
		 *
		 */
		return apply_filters( 'latepoint_get_all_payment_times', $payment_times );
	}

	/**
	 *
	 * Array of payment times that have at least one payment method enabled
	 *
	 * @return array
	 */
	public static function get_enabled_payment_times(): array {
		$enabled_payment_times = [];
		if ( self::is_local_payments_enabled() ) {
			$enabled_payment_times[ LATEPOINT_PAYMENT_TIME_LATER ][ LATEPOINT_PAYMENT_METHOD_LOCAL ]['latepoint'] = self::get_local_payment_method_info();
		}

		/**
		 * List of only enabled payment times
		 *
		 * @param {array} $enabled_payment_times Array of payment times
		 *
		 * @returns {array} Filtered array of enabled payment times
		 * @since 5.0.0
		 * @hook latepoint_get_enabled_payment_times
		 *
		 */
		return apply_filters( 'latepoint_get_enabled_payment_times', $enabled_payment_times );
	}


	public static function get_transactions_for_select() {
		$transactions         = new OsTransactionModel();
		$transactions         = $transactions->set_limit( 100 )->get_results_as_models();
		$transactions_options = [];
		foreach ( $transactions as $transaction ) {
			$name                   = $transaction->token . ', ' . OsMoneyHelper::format_price( $transaction->amount, true, false ) . ' [' . $transaction->processor . '/' . $transaction->payment_method . ' ' . $transaction->status . ']';
			$transactions_options[] = [ 'value' => $transaction->id, 'label' => $name ];
		}

		return $transactions_options;
	}


	public static function get_payment_portions_list() {
		$payment_portions = [
			LATEPOINT_PAYMENT_PORTION_FULL      => __( 'Full Balance', 'latepoint' ),
			LATEPOINT_PAYMENT_PORTION_REMAINING => __( 'Remaining Balance', 'latepoint' ),
			LATEPOINT_PAYMENT_PORTION_DEPOSIT   => __( 'Deposit', 'latepoint' )
		];

		return apply_filters( 'latepoint_payment_portions', $payment_portions );
	}

	public static function get_list_of_transaction_kinds() {
		$statuses = [
			LATEPOINT_TRANSACTION_KIND_CAPTURE       => __( 'Capture', 'latepoint' ),
			LATEPOINT_TRANSACTION_KIND_AUTHORIZATION => __( 'Authorization', 'latepoint' ),
			LATEPOINT_TRANSACTION_KIND_VOID          => __( 'Void', 'latepoint' ),
			LATEPOINT_TRANSACTION_KIND_REFUND        => __( 'Refund', 'latepoint' ),
		];

		return apply_filters( 'latepoint_transaction_kinds', $statuses );
	}

	public static function get_nice_transaction_kind_name( $kind ) {
		$kids_list = OsPaymentsHelper::get_list_of_transaction_kinds();

		return $kids_list[ $kind ] ?? $kind;
	}

	public static function get_transaction_statuses_list() {
		$statuses = [
			LATEPOINT_TRANSACTION_STATUS_SUCCEEDED  => __( 'Succeeded', 'latepoint' ),
			LATEPOINT_TRANSACTION_STATUS_PROCESSING => __( 'Processing', 'latepoint' ),
			LATEPOINT_TRANSACTION_STATUS_FAILED     => __( 'Failed', 'latepoint' )
		];

		return apply_filters( 'latepoint_transaction_statuses', $statuses );
	}


	public static function get_nice_transaction_status_name( $status ) {
		$statuses_list = OsPaymentsHelper::get_transaction_statuses_list();

		return $statuses_list[ $status ] ?? $status;
	}

	public static function display_transaction_payment_method_info( $payment_method ) {
		switch ( $payment_method ) {
			case LATEPOINT_PAYMENT_METHOD_CARD:
				echo '<div class="lp-method-logo"><i class="latepoint-icon latepoint-icon-credit-card"></i></div>';
				break;
			case LATEPOINT_PAYMENT_METHOD_PAYPAL:
				echo '<div class="lp-method-logo"><i class="latepoint-icon latepoint-icon-paypal"></i></div>';
				break;
			default:
				echo '<div class="lp-method-name">' . esc_html( $payment_method ) . '</div>';
				break;
		}
	}

	public static function process_payment_for_order_intent( OsOrderIntentModel $order_intent ) {
		if ( ( $order_intent->charge_amount <= 0 ) || OsSettingsHelper::is_env_demo() ) {
			return false;
		}
		$payment_processing_result = [];


		/**
		 * Hook to change a result of payment processing when order intent is being converted to order and payment is required
		 *
		 * @param {array} $result Array that holds result of payment processing
		 * @param {OsOrderIntentModel} $order_intent Order intent which is being converted to Order which payment is being processed
		 *
		 * @returns {array} Filtered array that holds result of payment processing
		 * @since 5.0.0
		 * @hook latepoint_process_payment_for_order_intent
		 *
		 */
		$payment_processing_result = apply_filters( 'latepoint_process_payment_for_order_intent', $payment_processing_result, $order_intent );
		if ( $payment_processing_result && $payment_processing_result['status'] == LATEPOINT_STATUS_SUCCESS ) {
			$transaction                  = new OsTransactionModel();
			$transaction->token           = $payment_processing_result['charge_id'];
			$transaction->payment_method  = $order_intent->get_payment_data_value( 'method' );
			$transaction->payment_portion = $order_intent->get_payment_data_value( 'portion' );
			$transaction->amount          = $order_intent->charge_amount;
			$transaction->customer_id     = $order_intent->customer_id;
			$transaction->processor       = $payment_processing_result['processor'];
			$transaction->kind            = $payment_processing_result['kind'] ?? LATEPOINT_TRANSACTION_KIND_CAPTURE;
			$transaction->status          = LATEPOINT_TRANSACTION_STATUS_SUCCEEDED;
		} else {
			$transaction = false;
		}

		return $transaction;
	}

	public static function process_payment_for_transaction_intent( OsTransactionIntentModel $transaction_intent ) {
		if ( ( $transaction_intent->charge_amount <= 0 ) || OsSettingsHelper::is_env_demo() ) {
			return false;
		}
		$payment_processing_result = [];


		/**
		 * Hook to change a result of payment processing when transaction intent is being converted to transaction and payment is required
		 *
		 * @param {array} $result Array that holds result of payment processing
		 * @param {OsTransactionIntentModel} $transaction_intent Transaction intent which is being converted to Transaction which payment is being processed
		 *
		 * @returns {array} Filtered array that holds result of payment processing
		 * @since 5.0.0
		 * @hook latepoint_process_payment_for_transaction_intent
		 *
		 */
		$payment_processing_result = apply_filters( 'latepoint_process_payment_for_transaction_intent', $payment_processing_result, $transaction_intent );
		if ( $payment_processing_result && $payment_processing_result['status'] == LATEPOINT_STATUS_SUCCESS ) {
			$transaction                  = new OsTransactionModel();
			$transaction->token           = $payment_processing_result['charge_id'];
			$transaction->payment_method  = $transaction_intent->get_payment_data_value( 'method' );
			$transaction->payment_portion = $transaction_intent->get_payment_data_value( 'portion' );
			$transaction->amount          = $transaction_intent->charge_amount;
			$transaction->order_id        = $transaction_intent->order_id;
			$transaction->customer_id     = $transaction_intent->customer_id;
			$transaction->processor       = $payment_processing_result['processor'];
			$transaction->kind            = $payment_processing_result['kind'] ?? LATEPOINT_TRANSACTION_KIND_CAPTURE;
			$transaction->status          = LATEPOINT_TRANSACTION_STATUS_SUCCEEDED;
		} else {
			$transaction = false;
		}

		return $transaction;
	}


	public static function convert_charge_amount_to_requirements( $charge_amount, OsCartModel $cart ) {

		/**
		 * Hook to convert amount to requirements based on a payment processor and method selected in a cart
		 *
		 * @param {float} $chart_amount Amount to charge
		 * @param {OsCartModel} $cart Cart object
		 *
		 * @returns {float} Filtered amount to spec
		 * @since 5.0.0
		 * @hook latepoint_convert_charge_amount_to_requirements
		 *
		 */
		return apply_filters( 'latepoint_convert_charge_amount_to_requirements', $charge_amount, $cart );
	}

}