<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCustomerMailer' ) ) :


	class OsCustomerMailer extends OsMailer {


		function __construct() {
			parent::__construct();
			$this->views_folder = LATEPOINT_VIEWS_MAILERS_ABSPATH . 'customer/';
		}


		// PASSWORD RESET TOKEN

		function password_reset_request( $customer, $token ) {
			$this->vars['customer'] = $customer;
			$to                     = $customer->email;
			$subject                = $this->password_reset_request_subject();
			$message                = $this->password_reset_request_content();
			$subject                = OsReplacerHelper::replace_all_vars( $subject, array( 'customer' => $customer, 'other_vars' => [ 'token' => $token ] ) );
			$message                = OsReplacerHelper::replace_all_vars( $message, array( 'customer' => $customer, 'other_vars' => [ 'token' => $token ] ) );

			return wp_mail( $to, $subject, $message, $this->headers );
		}

		function password_reset_request_subject() {
			$default = __( 'Reset Your Password', 'latepoint' );

			return OsSettingsHelper::get_settings_value( 'email_customer_password_reset_request_subject', $default );
		}

		function password_reset_request_content() {
			$content = OsSettingsHelper::get_settings_value( 'email_customer_password_reset_request_content' );
			if ( ! $content ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				if ( ! WP_Filesystem() ) {
					OsDebugHelper::log( __( 'Failed to initialise WC_Filesystem API while trying to show notification templates.', 'latepoint' ) );

					return '';
				}
				global $wp_filesystem;

				return $wp_filesystem->get_contents( LATEPOINT_VIEWS_ABSPATH . 'mailers/customer/password_reset_request.html' );
			} else {
				return $content;
			}
		}
	}

endif;