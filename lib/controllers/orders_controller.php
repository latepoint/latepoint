<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsOrdersController' ) ) :


	class OsOrdersController extends OsController {

		function __construct() {
			parent::__construct();

			$this->views_folder          = LATEPOINT_VIEWS_ABSPATH . 'orders/';
			$this->vars['page_header']   = OsMenuHelper::get_menu_items_by_id( 'orders' );
			$this->vars['breadcrumbs'][] = array(
				'label' => __( 'Orders', 'latepoint' ),
				'link'  => OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'orders', 'index' ) )
			);

			$this->action_access['public'] = array_merge( $this->action_access['public'], [ 'continue_order_intent' ] );
			$this->action_access['customer'] = array_merge( $this->action_access['customer'], [ 'summary_before_payment', 'payment_form' ] );
		}

		public function summary_before_payment(){
			if(!filter_var($this->params['order_id'], FILTER_VALIDATE_INT)) exit;

			$order = new OsOrderModel($this->params['order_id']);

			$this->vars['order'] = $order;

			$this->format_render( __FUNCTION__ );
		}

		public function payment_form(){
			if(!filter_var($this->params['order_id'], FILTER_VALIDATE_INT)) exit;
			$errors = [];
			$order = new OsOrderModel($this->params['order_id']);
	        $transaction_intent = new OsTransactionIntentModel();
	        $transaction_intent->charge_amount = $order->get_total_balance_due();
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

		public function view_order_log() {
			$activities = new OsActivityModel();
			$activities = $activities->where( [ 'order_id' => absint($this->params['order_id']) ] )->order_by( 'id desc' )->get_results_as_models();

			$order = new OsOrderModel( $this->params['order_id'] );

			$this->vars['order']      = $order;
			$this->vars['activities'] = $activities;

			$this->format_render( __FUNCTION__ );
		}


		public function continue_order_intent() {
			$order_intent_key = $this->params['order_intent_key'];
			$order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key($order_intent_key);

			if($order_intent->is_new_record()){
				http_response_code( 400 );
				OsDebugHelper::log('Order intent not found, id:'. $order_intent_key);
				exit();
			}else{

				$order_intent->convert_to_order();

				if ( $order_intent ) {
					nocache_headers();
					wp_redirect( $order_intent->get_page_url_with_intent(), 302 );
				}
			}

		}

		/*
			Update order (used in admin on quick side form save)
		*/

		public function update() {
			$this->create_or_update();
		}


		/*
			Create order (used in admin on quick side form save)
		*/

		public function create() {
			$this->create_or_update();
		}


		// Create/Update order from quick form in admin
		public function create_or_update() {
			$validation_errors = [];

			if ( ! empty( $this->params['order']['id'] ) ) {
				$this->check_nonce( 'edit_order_' . $this->params['order']['id'] );
			} else {
				$this->check_nonce( 'new_order' );
			}

			$order_params    = $this->params['order'];
			$customer_params = $this->params['customer'];


			$order_items_params = $this->params['order_items'] ?? [];


			$order = new OsOrderModel( $order_params['id'] );

			// if we are updating a order - save a copy by cloning old order
			$old_order = ( $order->is_new_record() ) ? [] : clone $order;
			$order->set_data( $order_params );


			// first validate & create a customer the customer
			if ( $order->customer_id ) {
				$customer          = new OsCustomerModel( $order->customer_id );
				$old_customer_data = $customer->get_data_vars();
				$is_new_customer   = false;
			} else {
				$customer        = new OsCustomerModel();
				$is_new_customer = true;
			}
			$customer->set_data( $customer_params );
			if ( $customer->save() ) {
				if ( $is_new_customer ) {
					do_action( 'latepoint_customer_created', $customer );
					$this->fields_to_update['order[customer_id]'] = $customer->id;
				} else {
					do_action( 'latepoint_customer_updated', $customer, $old_customer_data );
				}

				$order->customer_id = $customer->id;
			}else{
				$this->send_json( [
					'status' => LATEPOINT_STATUS_ERROR,
					// translators: %s is the description of an error
					'message' => sprintf(__( 'Error: %s', 'latepoint'), implode( ', ', $customer->get_error_messages() ) ),
					]
				);
			}

			// validate order items
			foreach ( $order_items_params as $order_item_id => $order_item ) {
				foreach ( $order_item['bookings'] as $booking_id => $booking_params ) {
					$booking                = OsOrdersHelper::create_booking_object_from_booking_data_form( $booking_params );
					$booking->customer_id   = $order->customer_id;
					if ( !$booking->validate(false, ['order_item_id']) ) {
						$validation_errors = array_merge($validation_errors, $booking->get_error_messages());
					}
				}
			}

			// check if there are errors saving bookings
			if($validation_errors){
				// translators: %s is the description of an error
				$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => sprintf(__( 'Error: %s', 'latepoint'), implode( ', ', $validation_errors ) ) ) );
			}

			if ( $old_order ) {
				// make sure old order items are still there, if not - remove them
				$order_items = $order->get_items();
				foreach ( $order_items as $order_item ) {
					if ( ! isset( $order_items_params[ $order_item->id ] ) ) {
						$order_item_id_to_delete = $order_item->id;
						/**
						 * Fires right before an order item is about to be deleted
						 *
						 * @param {integer} $order_item_id ID of the Order Item which will be deleted
						 *
						 * @since 5.0.0
						 * @hook latepoint_order_item_will_be_deleted
						 *
						 */
						do_action( 'latepoint_order_item_will_be_deleted', $order_item_id_to_delete );

						$order_item->delete();

						/**
						 * Fires right after an order item has been deleted
						 *
						 * @param {integer} $order_item_id ID of the Order Item that was deleted
						 *
						 * @since 5.0.0
						 * @hook latepoint_order_item_deleted
						 *
						 */
						do_action( 'latepoint_order_item_deleted', $order_item_id_to_delete );
						OsActivitiesHelper::log_order_item_deleted($order_item);
					} else {
						// it's a bundle order item - search for bookings that are attached to this bundle and remove them if not found in passed params list
						if ( $order_item->is_bundle() ) {
							$old_bundle_bookings = OsOrdersHelper::get_bookings_for_order_item( $order_item->id );
							if ( $old_bundle_bookings ) {
								foreach ( $old_bundle_bookings as $old_bundle_booking ) {
									if ( empty( $order_items_params[ $order_item->id ]['bookings'][ $old_bundle_booking->id ] ) ) {

										if ( OsRolesHelper::can_user_make_action_on_model_record( $old_bundle_booking, 'delete' ) ) {
											$booking_id_to_delete = $old_bundle_booking->id;

											/**
											 * Fires right before a booking is about to be deleted
											 *
											 * @param {integer} $booking_id ID of the booking that will be deleted
											 *
											 * @since 5.0.0
											 * @hook latepoint_booking_will_be_deleted
											 *
											 */
											do_action( 'latepoint_booking_will_be_deleted', $booking_id_to_delete );

											$old_bundle_booking->delete();
											/**
											 * Fires right after a booking has been deleted
											 *
											 * @param {integer} $booking_id ID of the booking that was deleted
											 *
											 * @since 5.0.0
											 * @hook latepoint_booking_deleted
											 *
											 */
											do_action( 'latepoint_booking_deleted', $booking_id_to_delete );
											OsActivitiesHelper::log_booking_deleted($old_bundle_booking);
										} else {
											OsDebugHelper::log( 'Not allowed: Deleting Booking', 'permissions_error' );
										}
									}
								}
							}
						}
					}
				}
			}

			// Because price is not in allowed_params to bulk set, check if it's passed in params and set it, OTHERWISE CALCULATE IT
			if ( isset( $order_params['total'] ) ) {
				$order->total = OsParamsHelper::sanitize_param( $order_params['total'], 'money' );
			}
			if ( isset( $order_params['subtotal'] ) ) {
				$order->subtotal = OsParamsHelper::sanitize_param( $order_params['subtotal'], 'money' );
			}

			// save price breakdown, we only need to save before and after subtotal, as total and subtotal values are stored on the Order record itself
			if ( ! empty( $this->params['price_breakdown'] ) ) {
				$order->price_breakdown = wp_json_encode( OsOrdersHelper::generate_price_breakdown_from_params($this->params['price_breakdown']) );
			}

			if ( $order->save() ) {

				// save transactions
				if ( ! empty( $this->params['transactions'] ) ) {
					foreach ( $this->params['transactions'] as $transaction_params ) {
						if ( ! empty( $transaction_params['id'] ) && filter_var( $transaction_params['id'], FILTER_VALIDATE_INT ) ) {
							// update existing transaction
							$transaction        = new OsTransactionModel( $transaction_params['id'] );
							$is_new_transaction = false;
						} else {
							// new transaction
							$transaction        = new OsTransactionModel();
							$is_new_transaction = true;
						}
						unset( $transaction_params['id'] );
						$transaction->set_data( $transaction_params );
						$transaction->order_id    = $order->id;
						$transaction->customer_id = $customer->id;
						$transaction->status      = LATEPOINT_TRANSACTION_STATUS_SUCCEEDED;
						$transaction->save();
						if ( $is_new_transaction ) {
							/**
							 * Transaction for order was created
							 *
							 * @param {OsTransactionModel} $transaction instance of transaction model that was created
							 *
							 * @since 5.0.0
							 * @hook latepoint_transaction_created
							 *
							 */
							do_action( 'latepoint_transaction_created', $transaction );
						}
					}
				}
				foreach ( $order_items_params as $order_item_id => $order_item ) {
					$order_item_model          = new OsOrderItemModel();
					$order_item_model->variant = $order_item['variant'];
					if ( strpos( $order_item_id, 'new_' ) === false ) {
						$order_item_model->load_by_id( $order_item_id );
					}
					if ( $order_item_model->is_bundle() ) {
						$order_item_model->item_data = base64_decode( $order_item['item_data'] );
						$order_item_model->recalculate_prices();
					}


					$order_item_model->order_id = $order->id;
					if ( $order_item_model->save() ) {
						foreach ( $order_item['bookings'] as $booking_id => $booking_params ) {
							$booking     = new OsBookingModel();
							$old_booking = false;
							if ( strpos( $order_item_id, 'new_' ) === false ) {
								$booking->load_by_id( $booking_id );
								if ( ! $booking->is_new_record() ) {
									$old_booking = clone $booking;
								}
							}

							$booking                = OsOrdersHelper::create_booking_object_from_booking_data_form( $booking_params );
							$booking->customer_id   = $order->customer_id;
							$booking->order_item_id = $order_item_model->id;
							$booking->form_id       = $booking_id;
							if ( $booking->save() ) {
								if ( $order_item_model->is_booking() ) {
									$order_item_model->item_data = $booking->generate_item_data();
									$order_item_model->recalculate_prices();
									$order_item_model->save();
								}
								if ( $old_booking ) {
									do_action( 'latepoint_booking_updated', $booking, $old_booking );
									if($old_booking->status != $booking->status){
										do_action( 'latepoint_booking_change_status', $booking, $old_booking );
										OsActivitiesHelper::log_booking_change_status($booking, $old_booking);
									}
								} else {
									do_action( 'latepoint_booking_created', $booking );
								}
							} else {
								OsDebugHelper::log( 'Error saving booking (admin)', 'booking_save_error', $booking->get_error_messages() );
							}
						}
					} else {
						OsDebugHelper::log( 'Error saving order item (admin)', 'order_item_save_error', $order_item_model->get_error_messages() );
					}
				}



				if ( $old_order ) {
					/**
					 * Order was updated
					 *
					 * @param {OsOrderModel} $order instance of order model after it was updated
					 * @param {OsOrderModel} $old_order instance of order model before it was updated
					 *
					 * @since 5.0.0
					 * @hook latepoint_order_updated
					 *
					 */
					do_action( 'latepoint_order_updated', $order, $old_order );
				} else {
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
				}

				$status        = LATEPOINT_STATUS_SUCCESS;
				// translators: %s is a link to the updated order
				$response_html = sprintf( ( ( $old_order ) ? __( 'Order Updated ID: %s', 'latepoint' ) : __( 'Order Created ID: %s', 'latepoint' ) ), '<span class="os-notification-link" ' . OsOrdersHelper::quick_order_btn_html( $order->id ) . '>' . $order->id . '</span>' );
			} else {
				OsDebugHelper::log( 'Error saving order (admin)', 'order_save_error', $order->get_error_messages() );
				$status        = LATEPOINT_STATUS_ERROR;

				// translators: %s is an error message
				$response_html = sprintf(__( 'Error: %s', 'latepoint'), implode( ', ', $order->get_error_messages() ));
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html ) );
			}

		}


		// reloads a section of a quick edit form that has a price breakdown
		public function reload_price_breakdown() {
			$order = new OsOrderModel();
			$order->set_data( $this->params['order'] );

			$order_items_params = $this->params['order_items'] ?? [];
			foreach ( $order_items_params as $order_items_param ) {
				$order->items[] = OsOrdersHelper::create_order_item_object( $order_items_param );
			}

			$order->subtotal = $order->recalculate_subtotal();
			$order->total    = $order->recalculate_total();

			/**
			 * Reloads price breakdown rows
			 *
			 * @since 5.0.0
			 * @hook latepoint_register_role
			 *
			 * @param {OsOrderModel} $order Order for which to reload price breakdown
			 * @returns {OsOrderModel} Filtered order with updated price breakdown rows
			 */
			$order           = apply_filters( 'latepoint_order_reload_price_breakdown', $order );

			// we don't need to generate balance and payments info as it is printed in a separate block
			$this->vars['price_breakdown_rows'] = $order->generate_price_breakdown_rows( [ 'balance', 'payments' ], true );

			$this->vars['order'] = $order;
			$this->format_render( __FUNCTION__ );
		}

		function reload_balance_and_payments() {
			$order_params = $this->params['order'];

			$order = new OsOrderModel();
			$order->set_data( $order_params );


			// Because price is not in allowed_params to bulk set, check if it's passed in params and set it, OTHERWISE CALCULATE IT
			if ( isset( $order_params['total'] ) ) {
				$order->total = OsParamsHelper::sanitize_param( $order_params['total'], 'money' );
			}
			if ( isset( $order_params['subtotal'] ) ) {
				$order->subtotal = OsParamsHelper::sanitize_param( $order_params['subtotal'], 'money' );
			}


			$this->vars['order'] = $order;
			$this->format_render( __FUNCTION__ );
		}

		function generate_bundle_order_item_block() {
			$bundle = new OsBundleModel( $this->params['bundle_id'] );

			$order_item_id = OsUtilHelper::generate_form_id();
			$response_html = '<div class="order-item order-item-variant-bundle" data-order-item-id="' . $order_item_id . '">';
			$response_html .= OsOrdersHelper::generate_order_item_pill_for_bundle( $bundle, $order_item_id );
			$response_html .= '</div>';

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ) );
			}
		}

		function generate_booking_order_item_block() {

			if ( ! empty( $this->params['order_item_variant'] ) && ( $this->params['order_item_variant'] == LATEPOINT_ITEM_VARIANT_BUNDLE ) ) {
				// booking for bundle, we don't need to wrap in order-item block because order item is a bundle
				$booking       = OsBookingHelper::build_booking_model_from_item_data( json_decode( base64_decode( $this->params['booking_item_data'] ), true ) );
				$response_html = OsOrdersHelper::booking_data_form_for_order_item_id( $this->params['order_item_id'], $booking, LATEPOINT_ITEM_VARIANT_BUNDLE, false );
			} else {
				// regular booking
				$booking       = OsBookingHelper::prepare_new_from_params( $this->params );
				$order_item_id = OsUtilHelper::generate_form_id();
				$response_html = '<div class="order-item order-item-variant-booking" data-order-item-id="' . $order_item_id . '">';
				$response_html .= OsOrdersHelper::booking_data_form_for_order_item_id( $order_item_id, $booking, LATEPOINT_ITEM_VARIANT_BOOKING, false );
				$response_html .= '</div>';
			}


			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ) );
			}
		}

		function fold_booking_data_form() {
			// input fields are formatted in customer preferred format, we need to convert that to database format Y-m-d
			$order_item_id = $this->params['order_item_id'];
			$booking_id    = $this->params['booking_id'];

			$booking_params = $this->params['order_items'][ $order_item_id ]['bookings'][ $booking_id ];
			$booking        = OsOrdersHelper::create_booking_object_from_booking_data_form( $booking_params );

			if ( $this->params['order_items'][ $order_item_id ]['variant'] == LATEPOINT_ITEM_VARIANT_BUNDLE ) {
				$response_html = OsOrdersHelper::generate_order_item_pill_for_bundle_booking( $booking, $order_item_id );
			} else {
				$response_html = OsOrdersHelper::generate_order_item_pill_for_booking( $booking, $order_item_id );
			}
			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ) );
		}

		function generate_order_item_booking_data_form() {
			$order_item          = new OsOrderItemModel();
			$order_item->variant = $this->params['order_item_variant'] ?? LATEPOINT_ITEM_VARIANT_BOOKING;
			if ( ! empty( $this->params['order_item_id'] ) ) {
				// existing order item
				if ( strpos( $this->params['order_item_id'], 'new_' ) !== false ) {
					$order_item->form_id = $this->params['order_item_id'];
				} else {
					$order_item->id = $this->params['order_item_id'];
				}
				$order_item->item_data = ! empty( $this->params['order_item_item_data'] ) ? base64_decode( $this->params['order_item_item_data'] ) : '';
				if ( $order_item->is_bundle() ) {
					$booking_item_data = ! empty( $this->params['booking_item_data'] ) ? base64_decode( $this->params['booking_item_data'] ) : '';
					$booking           = OsBookingHelper::build_booking_model_from_item_data( json_decode( $booking_item_data, true ) );
				} else {
					$booking = $order_item->build_original_object_from_item_data();
				}
			} else {
				// new order item
				$booking = OsBookingHelper::prepare_new_from_params( $this->params );
			}

			$response_html = OsOrdersHelper::booking_data_form_for_order_item_id( $order_item->get_form_id(), $booking, $order_item->variant );
			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ) );
		}

		function quick_edit() {

			$customers = new OsCustomerModel();
			// only load customers that belong to logged in agent, if any
			if ( ! OsRolesHelper::are_all_records_allowed( 'agent' ) ) {
				$customers->select( LATEPOINT_TABLE_CUSTOMERS . '.*' )->join( LATEPOINT_TABLE_BOOKINGS, [ 'customer_id' => LATEPOINT_TABLE_CUSTOMERS . '.id' ] )->group_by( LATEPOINT_TABLE_CUSTOMERS . '.id' )->where( [ LATEPOINT_TABLE_BOOKINGS . '.agent_id' => OsRolesHelper::get_allowed_records( 'agent' ) ] );
			}


			$customers_arr           = $customers->order_by( 'first_name asc, last_name asc' )->set_limit( 20 )->get_results_as_models();
			$this->vars['customers'] = $customers_arr;

			// CREATING NEW ORDER
			$order    = new OsOrderModel();
			$order_id = $this->params['id'] ?? false;

			if ( ! empty( $this->params['booking_id'] ) ) {
				$preselected_booking    = new OsBookingModel( $this->params['booking_id'] );
				$preselected_order_item = new OsOrderItemModel( $preselected_booking->order_item_id );
				$order_id               = $preselected_order_item->order_id;
			} else {
				$preselected_booking    = false;
				$preselected_order_item = false;
			}

			if ( $order_id ) {
				// EDITING EXISTING ORDER
				$order = new OsOrderModel( $order_id );
				// TODO add this check for order
//				if(!OsRolesHelper::can_user_make_action_on_model_record($order, 'view')){
//					$this->send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => 'Not Allowed'));
//				}

				$transactions = $order->get_transactions();

			} else {
				// NEW ORDER

				// LOAD FROM PASSED PARAMS
				$order->status             = OsOrdersHelper::get_default_order_status();
				$order->fulfillment_status = $order->get_default_fulfillment_status();

				if ( ! empty( $this->params['customer_id'] ) ) {
					$order->customer_id = $this->params['customer_id'];
				}

				$new_booking                 = OsBookingHelper::prepare_new_from_params( $this->params );
				$new_booking                 = apply_filters( 'latepoint_prepare_booking_for_quick_view', $new_booking );
				$order_item_model            = new OsOrderItemModel();
				$order_item_model->variant   = LATEPOINT_ITEM_VARIANT_BOOKING;
				$order_item_model->item_data = $new_booking->generate_item_data();

				$order->items[] = $order_item_model;

				$order->total    = $order->recalculate_total();
				$order->subtotal = $order->recalculate_subtotal();

				$transactions = [];
			}

			$bundles               = new OsBundleModel();
			$bundles               = $bundles->should_be_active()->get_results_as_models();
			$this->vars['bundles'] = $bundles;


			$this->vars['price_breakdown_rows'] = $order->generate_price_breakdown_rows();

			$order = apply_filters( 'latepoint_prepare_order_for_quick_view', $order );

			$order_bookings = $order->get_bookings_from_order_items();
			$order_bundles  = $order->get_bundles_from_order_items();


			$this->vars['selected_customer']           = new OsCustomerModel( $order->customer_id );
			$this->vars['order']                       = $order;
			$this->vars['preselected_booking']         = $preselected_booking;
			$this->vars['preselected_order_item']      = $preselected_order_item;
			$this->vars['show_only_preselected_items'] = $preselected_booking && $preselected_order_item && ( ( count( $order_bookings ) > 1 ) || ( count( $order_bundles ) ) || ( $order_bundles && $order_bookings ) );

			$this->vars['order_bookings']              = $order_bookings;
			$this->vars['order_bundles']               = $order_bundles;
			$this->vars['transactions']                = $transactions;
			$this->vars['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
			$this->format_render( __FUNCTION__ );
		}

		public function edit_form() {
			$order = ( empty( $this->params['id'] ) ) ? new OsOrderModel() : new OsOrderModel( $this->params['id'] );
			// legacy fix for older orders that didn't have portion column, get it from connected order
			if ( ! $order->is_new_record() && empty( $order->payment_portion ) && ! empty( $order->booking_id ) ) {
				$booking = new OsBookingModel( $order->booking_id );
				if ( ! empty( $booking->id ) ) {
					$order->payment_portion = $booking->payment_portion;
				}
			}
			$this->vars['real_or_rand_id'] = ( $order->is_new_record() ) ? 'new_order_' . OsUtilHelper::random_text( 'alnum', 5 ) : $order->id;
			$this->vars['order']           = $order;

			$this->format_render( __FUNCTION__ );
		}

		public function destroy() {
			if ( filter_var( $this->params['id'], FILTER_VALIDATE_INT ) ) {
				$this->check_nonce( 'destroy_order_' . $this->params['id'] );
				$order = new OsOrderModel( $this->params['id'] );
				if ( $order->delete() ) {
					$status        = LATEPOINT_STATUS_SUCCESS;
					$response_html = __( 'Order Removed', 'latepoint' );
				} else {
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'Error Removing Order', 'latepoint' );
				}
			} else {
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = __( 'Error Removing Order', 'latepoint' );
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html ) );
			}
		}

		/*
			Index of orders
		*/

		public function index() {

			$per_page = OsSettingsHelper::get_number_of_records_per_page();
			$page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;

			$this->vars['page_header'] = false;

			$orders = new OsOrderModel();


			// TABLE SEARCH FILTERS
			$filter     = $this->params['filter'] ?? false;
			$query_args = [];
			if ( $filter ) {
				if ( ! empty( $filter['id'] ) ) {
					$query_args['id'] = $filter['id'];
				}
				if ( ! empty( $filter['total'] ) ) {
					$query_args['total'] = $filter['total'];
				}
				if ( ! empty( $filter['status'] ) ) {
					$query_args['status'] = $filter['status'];
				}
				if ( ! empty( $filter['payment_status'] ) ) {
					$query_args['payment_status'] = $filter['payment_status'];
				}
				if ( ! empty( $filter['fulfillment_status'] ) ) {
					$query_args['fulfillment_status'] = $filter['fulfillment_status'];
				}
				if ( ! empty( $filter['confirmation_code'] ) ) {
					$query_args['confirmation_code LIKE'] = '%' . $filter['confirmation_code'] . '%';
				}

				if ( ! empty( $filter['customer']['full_name'] ) ) {
					$orders->select( LATEPOINT_TABLE_ORDERS . '.*, ' . LATEPOINT_TABLE_CUSTOMERS . '.first_name, ' . LATEPOINT_TABLE_CUSTOMERS . '.last_name' );
					$orders->join( LATEPOINT_TABLE_CUSTOMERS, [ 'id' => LATEPOINT_TABLE_ORDERS . '.customer_id' ] );

					$query_args[ 'concat_ws(" ", ' . LATEPOINT_TABLE_CUSTOMERS . '.first_name,' . LATEPOINT_TABLE_CUSTOMERS . '.last_name) LIKE' ] = '%' . $filter['customer']['full_name'] . '%';
					$this->vars['customer_name_query']                                                                                             = $filter['customer']['full_name'];

				}

				if ( ! empty( $filter['created_at_from'] ) && ! empty( $filter['created_at_to'] ) ) {
					$query_args['created_at >='] = $filter['created_at_from'] . ' 00:00:00';
					$query_args['created_at <='] = $filter['created_at_to'] . ' 23:59:59';
				}
			}


			// OUTPUT CSV IF REQUESTED
			if ( isset( $this->params['download'] ) && $this->params['download'] == 'csv' ) {
				$csv_filename = 'payments_' . OsUtilHelper::random_text() . '.csv';

				header( "Content-Type: text/csv" );
				header( "Content-Disposition: attachment; filename={$csv_filename}" );

				$labels_row = [
					__( 'ID', 'latepoint' ),
					__( 'Token', 'latepoint' ),
					__( 'Booking ID', 'latepoint' ),
					__( 'Customer', 'latepoint' ),
					__( 'Processor', 'latepoint' ),
					__( 'Method', 'latepoint' ),
					__( 'Amount', 'latepoint' ),
					__( 'Status', 'latepoint' ),
					__( 'Type', 'latepoint' ),
					__( 'Date', 'latepoint' )
				];


				$orders_data   = [];
				$orders_data[] = $labels_row;


				$orders_arr = $orders->where( $query_args )->filter_allowed_records()->get_results_as_models();

				if ( $orders_arr ) {
					foreach ( $orders_arr as $order ) {
						$values_row    = [
							$order->id,
							$order->token,
							$order->booking_id,
							( $order->customer_id ? $order->customer->full_name : 'n/a' ),
							$order->processor,
							$order->payment_method,
							OsMoneyHelper::format_price( $order->amount, true, false ),
							$order->status,
							$order->kind,
							$order->created_at,
						];
						$values_row    = apply_filters( 'latepoint_order_row_for_csv_export', $values_row, $order, $this->params );
						$orders_data[] = $values_row;
					}

				}

				$orders_data = apply_filters( 'latepoint_orders_data_for_csv_export', $orders_data, $this->params );
				OsCSVHelper::array_to_csv( $orders_data );

				return;
			}

			if ( $query_args ) {
				$orders->where( $query_args );
			}
			$orders->filter_allowed_records();


			$count_orders = clone $orders;
			$total_orders = $count_orders->count();

			$orders = $orders->order_by( LATEPOINT_TABLE_ORDERS . '.created_at desc' )->set_limit( $per_page );
			if ( $page_number > 1 ) {
				$orders = $orders->set_offset( ( $page_number - 1 ) * $per_page );
			}

			$this->vars['orders'] = $orders->get_results_as_models();

			$this->vars['total_orders']        = $total_orders;
			$this->vars['current_page_number'] = $page_number;
			$this->vars['per_page']            = $per_page;
			$total_pages                       = ceil( $total_orders / $per_page );
			$this->vars['total_pages']         = $total_pages;

			$this->vars['showing_from'] = ( ( $page_number - 1 ) * $per_page ) ? ( ( $page_number - 1 ) * $per_page ) : 1;
			$this->vars['showing_to']   = min( $page_number * $per_page, $total_orders );

			$this->format_render( [
				'json_view_name' => '_table_body',
				'html_view_name' => __FUNCTION__
			], [], [
				'total_pages'   => $total_pages,
				'showing_from'  => $this->vars['showing_from'],
				'showing_to'    => $this->vars['showing_to'],
				'total_records' => $total_orders
			] );
		}


	}


endif;