<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsStripeConnectController' ) ) :


	class OsStripeConnectController extends OsController {


		function __construct() {
			parent::__construct();

			$this->action_access['public']   = array_merge( $this->action_access['public'], [ 'webhook' ] );
			$this->action_access['customer'] = array_merge( $this->action_access['customer'], [ 'create_payment_intent' ] );
			$this->views_folder              = LATEPOINT_VIEWS_ABSPATH . 'stripe_connect/';
		}

		public function webhook() {
			$payload = @file_get_contents( 'php://input' );
			$data    = json_decode( $payload, true );
			if ( empty( $data['server_token'] ) || $data['server_token'] != OsStripeConnectHelper::get_server_token() || $data['stripe_account_id'] != OsStripeConnectHelper::get_connect_account_id() ) {
				http_response_code( 400 );
				echo 'Error converting order intent';
				exit();
			}
			$event = $data['event'];
			// Handle the event
			switch ( $event['type'] ) {
				case 'payment_intent.succeeded':
					if ( ! empty( $event['data']['order_intent_key'] ) ) {
						$order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $event['data']['order_intent_key'] );
						if ( $order_intent->is_new_record() ) {
							OsDebugHelper::log( 'Error processing stripe connect webhook: Order intent not found for key' );
							http_response_code( 400 );
							exit();
						}
						if ( $order_intent->convert_to_order() ) {
							http_response_code( 200 );
						} else {
							http_response_code( 400 );
							OsDebugHelper::log( 'Error converting order intent' );
						}
					}
					break;
			}
			exit();
		}

		private function get_env_from_params(): string {
			return ( ! empty( $this->params['env'] && in_array( $this->params['env'], [
					LATEPOINT_PAYMENTS_ENV_LIVE,
					LATEPOINT_PAYMENTS_ENV_DEV
				] ) ) ) ? $this->params['env'] : OsSettingsHelper::get_payments_environment();
		}

		public function start_connect_process() {
			$env = $this->get_env_from_params();
			OsSettingsHelper::save_setting_by_name( OsSettingsHelper::append_payment_env_key( 'enable_payment_processor_stripe_connect', $env ), LATEPOINT_VALUE_ON );
			$url = OsStripeConnectHelper::get_connect_url( $env );
			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'url' => $url, 'message' => __( 'Redirecting to Stripe', 'latepoint' ) ) );
		}

		public function disconnect_connect_account() {
			$env = $this->get_env_from_params();
			try {
				$path     = 'server-tokens/' . OsStripeConnectHelper::get_server_token( $env ) . '/disconnect/';
				$response = OsStripeConnectHelper::do_account_request( $path, $env, '', 'DELETE' );
				if ( $response['status']['code'] == 200 ) {
					OsSettingsHelper::remove_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_charges_enabled' ) );
					OsSettingsHelper::remove_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_account_id' ) );
				} else {
					OsDebugHelper::log( 'Stripe Connect Error', 'stripe_connect_disconnect_error', $response );
				}
			} catch ( Exception $e ) {
				OsDebugHelper::log( 'Error getting status of a stripe connection', 'stripe', [ 'error_message' => $e->getMessage() ] );
				$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $e->getMessage() ) );
			}
			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => OsStripeConnectHelper::get_connection_buttons_and_status( $env ) ) );
		}


		public function check_connect_status() {
			$env = $this->get_env_from_params();
			try {
				$response = OsStripeConnectHelper::do_request( 'server-tokens/' . OsStripeConnectHelper::get_server_token( $env ) . '/status', '', 'GET', [], [], $env );
				if ( ! empty( $response['data'] ) && ! empty( $response['data']['account_id'] ) ) {
					OsSettingsHelper::save_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_account_id', $env ), $response['data']['account_id'] );
					if ( ! empty( $response['data']['charges_enabled'] ) ) {
						OsSettingsHelper::save_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_charges_enabled', $env ), LATEPOINT_VALUE_ON );
					} else {
						OsSettingsHelper::remove_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_charges_enabled', $env ) );
					}
				} else {
					OsSettingsHelper::remove_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_charges_enabled', $env ) );
					OsSettingsHelper::remove_setting_by_name( OsSettingsHelper::append_payment_env_key( 'stripe_connect_account_id', $env ) );
				}
				if ( ! empty( $response['data']['error'] ) ) {
					OsDebugHelper::log( 'Error checking status of server token', 'stripe_connect_error', [ 'error_message' => $response['data']['error'] ] );
				}
			} catch ( Exception $e ) {
				OsDebugHelper::log( 'Error getting status of a stripe connection', 'stripe_connect_error', [ 'error_message' => $e->getMessage() ] );
				$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $e->getMessage() ) );
			}
			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => OsStripeConnectHelper::get_connection_buttons_and_status( $env ) ) );
		}


		public function heartbeat() {
			$payload = @file_get_contents( 'php://input' );
			$data    = json_decode( $payload, true );

			if ( empty( $data['wp_latepoint_server_token'] ) ) {
				$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => 'Token is missing' ), 404 );
			}
			if ( $data['wp_latepoint_server_token'] != OsStripeConnectHelper::get_server_token() ) {
				$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => 'Invalid Token' ), 404 );
			}

			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => 'Heartbeat detected' ), 200 );
		}

		public function create_payment_intent() {
			try {
				OsStepsHelper::set_required_objects( $this->params );

				$booking_form_page_url = $this->params['booking_form_page_url'] ?? wp_get_original_referer();
				$order_intent          = OsOrderIntentHelper::create_or_update_order_intent( OsStepsHelper::$cart_object, OsStepsHelper::$restrictions, OsStepsHelper::$presets, $booking_form_page_url );

				if ( ! $order_intent->is_bookable() ) {
					throw new Exception( __( 'Booking slot is not available anymore.', 'latepoint' ) );
				}

				if ( OsSettingsHelper::get_settings_value( OsSettingsHelper::append_payment_env_key( 'stripe_connect_account_id' ) ) ) {
					$payment_intent_data          = OsStripeConnectHelper::generate_payment_intent_id_and_secret_from_connect( $order_intent );
					$payment_intent_id            = $payment_intent_data['id'];
					$payment_intent_client_secret = $payment_intent_data['client_secret'];
				} else {
					throw new Exception( __( 'Stripe connect account ID not set', 'latepoint' ) );
				}


				// update cart with payment intent id
				OsStepsHelper::$cart_object->payment_token = $payment_intent_id;

				// cart_item_data might be changed after filters run, make sure to get the latest version
				$cart_items_data = json_decode( $order_intent->cart_items_data, true );
				$payment_data    = json_decode( $order_intent->payment_data, true );

				$payment_data['token'] = $payment_intent_id;
				$order_intent->update_attributes( [
					'cart_items_data' => wp_json_encode( $cart_items_data ),
					'payment_data'    => wp_json_encode( $payment_data )
				] );
				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( [
						'status'                    => LATEPOINT_STATUS_SUCCESS,
						'continue_order_intent_url' => OsOrderIntentHelper::generate_continue_intent_url( $order_intent->intent_key ),
						'payment_intent_id'         => $payment_intent_id,
						'payment_intent_secret'     => $payment_intent_client_secret,
						'order_intent_key'          => $order_intent->intent_key
					] );
				}
			} catch ( Exception $e ) {
				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $e->getMessage() ) );
				}
			}

		}
	}


endif;
