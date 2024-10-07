<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsAuthController' ) ) :


	class OsAuthController extends OsController {

		function __construct() {
			parent::__construct();
			$this->action_access['public'] = array_merge( $this->action_access['public'], [
				'logout_customer',
				'login_customer',
				'login_customer_using_social_data',
				'login_customer_using_google_token',
				'login_customer_using_facebook_token'
			] );
			$this->views_folder            = LATEPOINT_VIEWS_ABSPATH . 'auth/';
		}


		// Logs out customer and shows blank contact step
		public function logout_customer() {
			OsAuthHelper::logout_customer();

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => __( 'You have been logged out of your account.', 'latepoint' ) ) );
			}
		}

		// Login customer and show contact step with prefilled info
		public function login_customer() {
			$customer = OsAuthHelper::login_customer( $this->params['email'], $this->params['password'] );
			if ( $customer ) {
				$status        = LATEPOINT_STATUS_SUCCESS;
				$customer_id   = $customer->id;
				$response_html = __( 'Welcome back', 'latepoint' );
			} else {
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = __( 'Sorry, that email or password didn\'t work.', 'latepoint' );
				$customer_id   = '';
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html, 'customer_id' => $customer_id ) );
			}
		}

		public function login_customer_using_social_data( $network, $social_user ) {
			$customer_id = '';
			if ( isset( $social_user['social_id'] ) ) {
				$customer_was_updated = false;
				$old_customer_data    = [];
				$social_id_field_name = $network . '_user_id';
				$status               = LATEPOINT_STATUS_SUCCESS;
				$response_html        = $social_user['social_id'];
				// Search for existing customer with email that google provided
				$customer = new OsCustomerModel();
				$customer = $customer->where( array( 'email' => $social_user['email'] ) )->set_limit( 1 )->get_results_as_models();
				if ( OsAuthHelper::wp_users_as_customers() ) {
					if ( $customer->wordpress_user_id != email_exists( $social_user['email'] ) ) {
						$old_customer_data = $customer->get_data_vars();
						$customer->update_attributes( [ 'wordpress_user_id' => null ] );
						$wp_user_id           = OsCustomerHelper::create_wp_user_for_customer( $customer );
						$customer_was_updated = true;
						if ( ! $wp_user_id ) {
							$status        = LATEPOINT_STATUS_ERROR;
							$response_html = __( 'Error creating wp user', 'latepoint' );
						}
					}
				}
				// Create customer if its not found
				if ( ! $customer ) {
					$customer                        = new OsCustomerModel();
					$customer->first_name            = $social_user['first_name'];
					$customer->last_name             = $social_user['last_name'];
					$customer->email                 = $social_user['email'];
					$customer->$social_id_field_name = $social_user['social_id'];
					if ( ! $customer->save( true ) ) {
						$response_html = $customer->get_error_messages();
						$status        = LATEPOINT_STATUS_ERROR;
					} else {
						do_action( 'latepoint_customer_created', $customer );
					}
				}

				if ( ( $status == LATEPOINT_STATUS_SUCCESS ) && $customer->id ) {
					$customer_id = $customer->id;
					// Update customer google user id if its not set yet
					if ( $customer->$social_id_field_name != $social_user['social_id'] ) {
						$old_customer_data               = $customer->get_data_vars();
						$customer->$social_id_field_name = $social_user['social_id'];
						$customer->save();
						$customer_was_updated = true;
					}
					OsAuthHelper::authorize_customer( $customer->id );
					$response_html = __( 'Welcome back', 'latepoint' );
				}
				if ( $customer_was_updated && $old_customer_data ) {
					do_action( 'latepoint_customer_updated', $customer, $old_customer_data );
				}
			} else {
				// ERROR WITH GOOGLE LOGIN
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = $social_user['error'];
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html, 'customer_id' => $customer_id ) );
			}

		}


		public function login_customer_using_google_token() {
			$social_user = [];
			$token       = sanitize_text_field( $this->params['token'] );
			$social_user = apply_filters( 'latepoint_get_social_user_by_token', $social_user, 'google', $token );
			if ( !empty($social_user) ) {
				$this->login_customer_using_social_data( 'google', $social_user );
			}
		}

		public function login_customer_using_facebook_token() {
			$social_user = [];
			$token       = sanitize_text_field( $this->params['token'] );
			$social_user = apply_filters( 'latepoint_get_social_user_by_token', $social_user, 'facebook', $token );
			if ( !empty($social_user) ) {
				$this->login_customer_using_social_data( 'facebook', $social_user );
			}
		}


	}
endif;