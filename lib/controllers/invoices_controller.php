<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsInvoicesController' ) ) :


	class OsInvoicesController extends OsController {

		function __construct() {
			parent::__construct();

			$this->action_access['public'] = array_merge( $this->action_access['public'], [ 'view_by_key', 'payment_form', 'summary_before_payment' ] );

			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'invoices/';
		}


		public function payment_form(){
			$invoice_access_key = sanitize_text_field($this->params['key']);
			if(empty($invoice_access_key)) exit;

			$invoice = OsInvoicesHelper::get_invoice_by_key($invoice_access_key);
			if($invoice->is_new_record()) exit;

			$errors = [];
			$order = $invoice->get_order();

	        $transaction_intent = new OsTransactionIntentModel();
	        $transaction_intent->charge_amount = $invoice->charge_amount;
	        $transaction_intent->invoice_id = $invoice->id;
	        $transaction_intent->order_id = $order->id;
	        $transaction_intent->customer_id = $order->customer_id;
	        $transaction_intent->payment_data_arr['time'] = LATEPOINT_PAYMENT_TIME_NOW;
	        $transaction_intent->payment_data_arr['portion'] = ($order->get_total_amount_paid_from_transactions() > 0) ? LATEPOINT_PAYMENT_PORTION_REMAINING : LATEPOINT_PAYMENT_PORTION_FULL;

			$form_prev_button = esc_html__('Back', 'latepoint');
			$form_next_button = esc_html__('Next', 'latepoint');
			$invoice_link = false;

			$selected_payment_method = $this->params['payment_method'] ?? false;
			$selected_payment_processor = $this->params['payment_processor'] ?? false;
			$payment_token = $this->params['payment_token'] ?? false;

			$enabled_payment_methods = OsPaymentsHelper::get_enabled_payment_methods_for_payment_time( LATEPOINT_PAYMENT_TIME_NOW );
			// if only one available, force select it
			if(count($enabled_payment_methods) == 1) $selected_payment_method = array_key_first($enabled_payment_methods);

			if($selected_payment_method){
				$enabled_payment_processors = OsPaymentsHelper::get_enabled_payment_processors_for_payment_time_and_method( LATEPOINT_PAYMENT_TIME_NOW, $selected_payment_method);
				if(count($enabled_payment_processors) == 1) $selected_payment_processor = array_key_first($enabled_payment_processors);
			}

			if(!$selected_payment_method){
				$current_step = 'methods';
				$form_heading = __( 'Payment Methods', 'latepoint' );
				$form_prev_button = false;
			}else{
		        $transaction_intent->payment_data_arr['method'] = $selected_payment_method;
				if(!$selected_payment_processor){
					$current_step = 'processors';
					$form_heading = __( 'Payment Processors', 'latepoint' );

					// hide prev button if we don't need to pick a payment methods
					if(count($enabled_payment_methods) <= 1) $form_prev_button = false;
				}else{
			        $transaction_intent->payment_data_arr['processor'] = $selected_payment_processor;
					$form_next_button = sprintf(esc_html__('Pay %s', 'latepoint'), OsMoneyHelper::format_price($transaction_intent->charge_amount, true, false));
					$form_heading = __( 'Payment Form', 'latepoint' );
					// hide prev button if we don't need to pick a payment method or processor
					if(count($enabled_payment_methods) <= 1 && count($enabled_payment_processors) <= 1) $form_prev_button = false;
					if(!$payment_token){
						$current_step = 'pay';
					}else{
				        $transaction_intent->payment_data_arr['token'] = $payment_token;
						$transaction_id = $transaction_intent->convert_to_transaction();
						if($transaction_id){
							$transaction = new OsTransactionModel($transaction_id);
							$form_next_button = false;
							$form_prev_button = false;
							$invoice_link = true;
							$current_step = 'confirmation';
							$this->vars['transaction'] = $transaction;
							$form_heading = __( 'Confirmation', 'latepoint' );;
						}else{
							$current_step = 'pay';
							$errors[] = implode(', ', $transaction_intent->get_error_messages());
						}
					}
				}
			}


			$this->vars['invoice_link'] = $invoice_link;
			$this->vars['form_heading'] = $form_heading;
			$this->vars['errors'] = $errors;
			$this->vars['transaction_intent'] = $transaction_intent;
			$this->vars['current_step'] = $current_step;
			$this->vars['selected_payment_method'] = $selected_payment_method;
			$this->vars['selected_payment_processor'] = $selected_payment_processor;

			$this->vars['form_next_button'] = $form_next_button;
			$this->vars['form_prev_button'] = $form_prev_button;


			$this->vars['order'] = $order;

			$this->format_render( __FUNCTION__ );
		}

		public function summary_before_payment(){
			$invoice_access_key = sanitize_text_field($this->params['key']);

			$invoice = OsInvoicesHelper::get_invoice_by_key($invoice_access_key);

			$this->vars['invoice'] = $invoice;
			$this->vars['order'] = $invoice->get_order();

			if ( $this->get_return_format() == 'json' ) {
				$this->vars['in_lightbox'] = true;
				$this->set_layout( 'none' );
				$response_html = $this->format_render_return( __FUNCTION__ );
				$this->send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ] );
			}else{
				$this->vars['in_lightbox'] = false;
				$this->set_layout( 'clean' );
				$this->format_render( __FUNCTION__ );
			}
		}


		function view_by_key() {
			$invoice_access_key = sanitize_text_field($this->params['key']);
			$invoice = new OsInvoiceModel();
			$invoice = $invoice->where(['access_key' => $invoice_access_key])->set_limit(1)->get_results_as_models();
			$this->vars['invoice'] = $invoice;

			$this->set_layout( 'clean' );
			$this->format_render( __FUNCTION__ );
		}

		function view() {
			if ( ! filter_var( $this->params['id'], FILTER_VALIDATE_INT ) ) {
				return;
			}

			$invoice = new OsInvoiceModel( $this->params['id'] );

			$this->vars['invoice']      = $invoice;

			$this->set_layout( 'none' );
			$response_html = $this->format_render_return( __FUNCTION__ );

			$status = LATEPOINT_STATUS_SUCCESS;

			if ( $this->get_return_format() == 'json' ) {

				$this->send_json( [ 'status' => $status, 'message' => $response_html ] );
			}
		}
	}

endif;
