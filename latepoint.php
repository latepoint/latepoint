<?php
/**
 * Plugin Name: LatePoint
 * Description: Appointment Scheduling Software for WordPress
 * Version: 5.0.15
 * Author: LatePoint
 * Author URI: http://latepoint.com
 * Text Domain: latepoint
 * Domain Path: /languages
 * License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'LatePoint' ) ) :

	/**
	 * Main LatePoint Class.
	 *
	 */

	final class LatePoint {

		/**
		 * LatePoint version.
		 *
		 */
		public $version = '5.0.15';
		public $db_version = '2.1.5';


		/**
		 * LatePoint Constructor.
		 */
		public function __construct() {

			$this->define_constants();
			$this->includes();
			$this->init_hooks();
			OsDatabaseHelper::check_db_version();
			OsDatabaseHelper::check_db_version_for_addons();


			$GLOBALS['latepoint_settings'] = new OsSettingsHelper();

		}


		/**
		 * Define constant if not already set.
		 *
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}


		/**
		 * Get the plugin url. *has trailing slash
		 * @return string
		 */
		public static function plugin_url() {
			return plugin_dir_url( __FILE__ );
		}

		public static function public_javascripts() {
			return plugin_dir_url( __FILE__ ) . 'public/javascripts/';
		}

		public static function public_vendor_javascripts() {
			return plugin_dir_url( __FILE__ ) . 'public/javascripts/vendor/';
		}

		public static function public_stylesheets() {
			return plugin_dir_url( __FILE__ ) . 'public/stylesheets/';
		}

		public static function blocks_build_url() {
			return plugin_dir_url( __FILE__ ) . 'blocks/build/';
		}

		public static function node_modules_url() {
			return plugin_dir_url( __FILE__ ) . 'node_modules/';
		}

		public static function vendor_assets_url() {
			return plugin_dir_url( __FILE__ ) . 'vendor/';
		}

		public static function images_url() {
			return plugin_dir_url( __FILE__ ) . 'public/images/';
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public static function plugin_path() {
			return plugin_dir_path( __FILE__ );
		}


		/**
		 * Define LatePoint Constants.
		 */
		public function define_constants() {
			$upload_dir = wp_upload_dir();

			// ENVIRONMENTS TYPES
			if ( ! defined( 'LATEPOINT_ENV_LIVE' ) ) {
				define( 'LATEPOINT_ENV_LIVE', 'live' );
			}
			if ( ! defined( 'LATEPOINT_ENV_DEMO' ) ) {
				define( 'LATEPOINT_ENV_DEMO', 'demo' );
			}
			if ( ! defined( 'LATEPOINT_ENV_DEV' ) ) {
				define( 'LATEPOINT_ENV_DEV', 'dev' );
			}

			// PAYMENT ENVIRONMENTS TYPES
			if ( ! defined( 'LATEPOINT_PAYMENTS_ENV_LIVE' ) ) {
				define( 'LATEPOINT_PAYMENTS_ENV_LIVE', 'live' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENTS_ENV_DEMO' ) ) {
				define( 'LATEPOINT_PAYMENTS_ENV_DEMO', 'demo' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENTS_ENV_DEV' ) ) {
				define( 'LATEPOINT_PAYMENTS_ENV_DEV', 'dev' );
			}


			if ( ! defined( 'LATEPOINT_PAYMENTS_DEV_SUFFIX' ) ) {
				define( 'LATEPOINT_PAYMENTS_DEV_SUFFIX', '_dev' );
			}

			if ( ! defined( 'LATEPOINT_ENV' ) ) {
				define( 'LATEPOINT_ENV', LATEPOINT_ENV_LIVE );
			}
			if ( ! defined( 'LATEPOINT_ALLOW_LOCAL_SERVER' ) ) {
				define( 'LATEPOINT_ALLOW_LOCAL_SERVER', true );
			}

			if ( ! defined( 'LATEPOINT_ALLOW_SMS' ) ) {
				define( 'LATEPOINT_ALLOW_SMS', true );
			}
			if ( ! defined( 'LATEPOINT_ALLOW_EMAILS' ) ) {
				define( 'LATEPOINT_ALLOW_EMAILS', true );
			}

			if ( ! defined( 'LATEPOINT_PLUGIN_FILE' ) ) {
				define( 'LATEPOINT_PLUGIN_FILE', __FILE__ );
			}
			if ( ! defined( 'LATEPOINT_STYLESHEETS_URL' ) ) {
				define( 'LATEPOINT_STYLESHEETS_URL', $this->public_stylesheets() );
			}
			if ( ! defined( 'LATEPOINT_BLOCKS_BUILD_URL' ) ) {
				define( 'LATEPOINT_BLOCKS_BUILD_URL', $this->blocks_build_url() );
			}
			if ( ! defined( 'LATEPOINT_ABSPATH' ) ) {
				define( 'LATEPOINT_ABSPATH', dirname( __FILE__ ) . '/' );
			}
			if ( ! defined( 'LATEPOINT_LIB_ABSPATH' ) ) {
				define( 'LATEPOINT_LIB_ABSPATH', LATEPOINT_ABSPATH . 'lib/' );
			}
			if ( ! defined( 'LATEPOINT_CONFIG_ABSPATH' ) ) {
				define( 'LATEPOINT_CONFIG_ABSPATH', LATEPOINT_LIB_ABSPATH . 'config/' );
			}
			if ( ! defined( 'LATEPOINT_BLOCKS_ABSPATH' ) ) {
				define( 'LATEPOINT_BLOCKS_ABSPATH', LATEPOINT_ABSPATH . 'blocks/build/' );
			}
			if ( ! defined( 'LATEPOINT_BOWER_ABSPATH' ) ) {
				define( 'LATEPOINT_BOWER_ABSPATH', LATEPOINT_ABSPATH . 'vendor/bower_components/' );
			}
			if ( ! defined( 'LATEPOINT_VIEWS_ABSPATH' ) ) {
				define( 'LATEPOINT_VIEWS_ABSPATH', LATEPOINT_LIB_ABSPATH . 'views/' );
			}
			if ( ! defined( 'LATEPOINT_VIEWS_ABSPATH_SHARED' ) ) {
				define( 'LATEPOINT_VIEWS_ABSPATH_SHARED', LATEPOINT_LIB_ABSPATH . 'views/shared/' );
			}
			if ( ! defined( 'LATEPOINT_VIEWS_MAILERS_ABSPATH' ) ) {
				define( 'LATEPOINT_VIEWS_MAILERS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'mailers/' );
			}
			if ( ! defined( 'LATEPOINT_VIEWS_LAYOUTS_ABSPATH' ) ) {
				define( 'LATEPOINT_VIEWS_LAYOUTS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'layouts/' );
			}
			if ( ! defined( 'LATEPOINT_VIEWS_PARTIALS_ABSPATH' ) ) {
				define( 'LATEPOINT_VIEWS_PARTIALS_ABSPATH', LATEPOINT_VIEWS_ABSPATH . 'partials/' );
			}
			if ( ! defined( 'LATEPOINT_PLUGIN_BASENAME' ) ) {
				define( 'LATEPOINT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			}

			if ( ! defined( 'LATEPOINT_PLUGIN_URL' ) ) {
				define( 'LATEPOINT_PLUGIN_URL', $this->plugin_url() );
			}
			if ( ! defined( 'LATEPOINT_LIB_URL' ) ) {
				define( 'LATEPOINT_LIB_URL', LATEPOINT_PLUGIN_URL . 'lib/' );
			}
			if ( ! defined( 'LATEPOINT_PUBLIC_URL' ) ) {
				define( 'LATEPOINT_PUBLIC_URL', LATEPOINT_PLUGIN_URL . 'public/' );
			}
			if ( ! defined( 'LATEPOINT_IMAGES_URL' ) ) {
				define( 'LATEPOINT_IMAGES_URL', LATEPOINT_PUBLIC_URL . 'images/' );
			}
			if ( ! defined( 'LATEPOINT_DEFAULT_AVATAR_URL' ) ) {
				define( 'LATEPOINT_DEFAULT_AVATAR_URL', LATEPOINT_IMAGES_URL . 'default-avatar.jpg' );
			}
			if ( ! defined( 'LATEPOINT_MARKETPLACE' ) ) {
				define( 'LATEPOINT_MARKETPLACE', 'codecanyon' );
			}
			if ( ! defined( 'LATEPOINT_REMOTE_HASH' ) ) {
				define( 'LATEPOINT_REMOTE_HASH', 'aHR0cHM6Ly9sYXRlcG9pbnQuY29t' );
			}

			// role to assigne WP user to so they are connected to a LatePoint backend user type (agent or manager)
			if ( ! defined( 'LATEPOINT_WP_ADMIN_ROLE' ) ) {
				define( 'LATEPOINT_WP_ADMIN_ROLE', 'administrator' );
			}
			if ( ! defined( 'LATEPOINT_WP_AGENT_ROLE' ) ) {
				define( 'LATEPOINT_WP_AGENT_ROLE', 'latepoint_agent' );
			}
			if ( ! defined( 'LATEPOINT_WP_MANAGER_ROLE' ) ) {
				define( 'LATEPOINT_WP_MANAGER_ROLE', 'latepoint_manager' );
			}

			if ( ! defined( 'LATEPOINT_USER_TYPE_ADMIN' ) ) {
				define( 'LATEPOINT_USER_TYPE_ADMIN', 'admin' );
			}
			if ( ! defined( 'LATEPOINT_USER_TYPE_AGENT' ) ) {
				define( 'LATEPOINT_USER_TYPE_AGENT', 'agent' );
			}
			if ( ! defined( 'LATEPOINT_USER_TYPE_CUSTOM' ) ) {
				define( 'LATEPOINT_USER_TYPE_CUSTOM', 'custom' );
			}
			if ( ! defined( 'LATEPOINT_USER_TYPE_CUSTOMER' ) ) {
				define( 'LATEPOINT_USER_TYPE_CUSTOMER', 'customer' );
			}

			if ( ! defined( 'LATEPOINT_PARAMS_SCOPE_PUBLIC' ) ) {
				define( 'LATEPOINT_PARAMS_SCOPE_PUBLIC', 'public' );
			}
			if ( ! defined( 'LATEPOINT_PARAMS_SCOPE_ADMIN' ) ) {
				define( 'LATEPOINT_PARAMS_SCOPE_ADMIN', 'admin' );
			}
			if ( ! defined( 'LATEPOINT_PARAMS_SCOPE_CUSTOMER' ) ) {
				define( 'LATEPOINT_PARAMS_SCOPE_CUSTOMER', 'customer' );
			}


			if ( ! defined( 'LATEPOINT_VERSION' ) ) {
				define( 'LATEPOINT_VERSION', $this->version );
			}
			if ( ! defined( 'LATEPOINT_ENCRYPTION_KEY' ) ) {
				define( 'LATEPOINT_ENCRYPTION_KEY', 'oiaf(*Ufdsoh2ie7QEy,R@6(I9H/VoX^r4}SHC_7W-<$S!,/kd)OSw?.Y9lcd105cu$' );
			}

			if ( ! defined( 'LATEPOINT_AGENT_POST_TYPE' ) ) {
				define( 'LATEPOINT_AGENT_POST_TYPE', 'latepoint_agent' );
			}

			if ( ! defined( 'LATEPOINT_UPGRADE_URL' ) ) {
				define( 'LATEPOINT_UPGRADE_URL', 'https://latepoint.com/pricing' );
			}
			if ( ! defined( 'LATEPOINT_SERVICE_POST_TYPE' ) ) {
				define( 'LATEPOINT_SERVICE_POST_TYPE', 'latepoint_service' );
			}
			if ( ! defined( 'LATEPOINT_CUSTOMER_POST_TYPE' ) ) {
				define( 'LATEPOINT_CUSTOMER_POST_TYPE', 'latepoint_customer' );
			}

			if ( ! defined( 'LATEPOINT_DB_VERSION' ) ) {
				define( 'LATEPOINT_DB_VERSION', $this->db_version );
			}

			global $wpdb;
			if ( ! defined( 'LATEPOINT_TABLE_BUNDLES' ) ) {
				define( 'LATEPOINT_TABLE_BUNDLES', $wpdb->prefix . 'latepoint_bundles' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_BUNDLES_SERVICES' ) ) {
				define( 'LATEPOINT_TABLE_JOIN_BUNDLES_SERVICES', $wpdb->prefix . 'latepoint_bundles_services' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_BOOKINGS' ) ) {
				define( 'LATEPOINT_TABLE_BOOKINGS', $wpdb->prefix . 'latepoint_bookings' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_SESSIONS' ) ) {
				define( 'LATEPOINT_TABLE_SESSIONS', $wpdb->prefix . 'latepoint_sessions' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_SERVICES' ) ) {
				define( 'LATEPOINT_TABLE_SERVICES', $wpdb->prefix . 'latepoint_services' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_SETTINGS' ) ) {
				define( 'LATEPOINT_TABLE_SETTINGS', $wpdb->prefix . 'latepoint_settings' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_SERVICE_CATEGORIES' ) ) {
				define( 'LATEPOINT_TABLE_SERVICE_CATEGORIES', $wpdb->prefix . 'latepoint_service_categories' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_WORK_PERIODS' ) ) {
				define( 'LATEPOINT_TABLE_WORK_PERIODS', $wpdb->prefix . 'latepoint_work_periods' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_CUSTOM_PRICES' ) ) {
				define( 'LATEPOINT_TABLE_CUSTOM_PRICES', $wpdb->prefix . 'latepoint_custom_prices' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_AGENTS_SERVICES' ) ) {
				define( 'LATEPOINT_TABLE_AGENTS_SERVICES', $wpdb->prefix . 'latepoint_agents_services' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ACTIVITIES' ) ) {
				define( 'LATEPOINT_TABLE_ACTIVITIES', $wpdb->prefix . 'latepoint_activities' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_TRANSACTIONS' ) ) {
				define( 'LATEPOINT_TABLE_TRANSACTIONS', $wpdb->prefix . 'latepoint_transactions' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_TRANSACTION_INTENTS' ) ) {
				define( 'LATEPOINT_TABLE_TRANSACTION_INTENTS', $wpdb->prefix . 'latepoint_transaction_intents' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_AGENTS' ) ) {
				define( 'LATEPOINT_TABLE_AGENTS', $wpdb->prefix . 'latepoint_agents' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_CUSTOMERS' ) ) {
				define( 'LATEPOINT_TABLE_CUSTOMERS', $wpdb->prefix . 'latepoint_customers' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_CUSTOMER_META' ) ) {
				define( 'LATEPOINT_TABLE_CUSTOMER_META', $wpdb->prefix . 'latepoint_customer_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_SERVICE_META' ) ) {
				define( 'LATEPOINT_TABLE_SERVICE_META', $wpdb->prefix . 'latepoint_service_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_BOOKING_META' ) ) {
				define( 'LATEPOINT_TABLE_BOOKING_META', $wpdb->prefix . 'latepoint_booking_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_AGENT_META' ) ) {
				define( 'LATEPOINT_TABLE_AGENT_META', $wpdb->prefix . 'latepoint_agent_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_STEPS' ) ) {
				define( 'LATEPOINT_TABLE_STEPS', $wpdb->prefix . 'latepoint_steps' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_STEP_SETTINGS' ) ) {
				define( 'LATEPOINT_TABLE_STEP_SETTINGS', $wpdb->prefix . 'latepoint_step_settings' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_LOCATIONS' ) ) {
				define( 'LATEPOINT_TABLE_LOCATIONS', $wpdb->prefix . 'latepoint_locations' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_LOCATION_CATEGORIES' ) ) {
				define( 'LATEPOINT_TABLE_LOCATION_CATEGORIES', $wpdb->prefix . 'latepoint_location_categories' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_PROCESSES' ) ) {
				define( 'LATEPOINT_TABLE_PROCESSES', $wpdb->prefix . 'latepoint_processes' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_PROCESS_JOBS' ) ) {
				define( 'LATEPOINT_TABLE_PROCESS_JOBS', $wpdb->prefix . 'latepoint_process_jobs' );
			}

			if ( ! defined( 'LATEPOINT_TABLE_CARTS' ) ) {
				define( 'LATEPOINT_TABLE_CARTS', $wpdb->prefix . 'latepoint_carts' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_CART_META' ) ) {
				define( 'LATEPOINT_TABLE_CART_META', $wpdb->prefix . 'latepoint_cart_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_CART_ITEMS' ) ) {
				define( 'LATEPOINT_TABLE_CART_ITEMS', $wpdb->prefix . 'latepoint_cart_items' );
			}

			if ( ! defined( 'LATEPOINT_TABLE_ORDERS' ) ) {
				define( 'LATEPOINT_TABLE_ORDERS', $wpdb->prefix . 'latepoint_orders' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_META' ) ) {
				define( 'LATEPOINT_TABLE_ORDER_META', $wpdb->prefix . 'latepoint_order_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_ITEMS' ) ) {
				define( 'LATEPOINT_TABLE_ORDER_ITEMS', $wpdb->prefix . 'latepoint_order_items' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_INTENTS' ) ) {
				define( 'LATEPOINT_TABLE_ORDER_INTENTS', $wpdb->prefix . 'latepoint_order_intents' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_INTENT_META' ) ) {
				define( 'LATEPOINT_TABLE_ORDER_INTENT_META', $wpdb->prefix . 'latepoint_order_intent_meta' );
			}
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_INVOICES' ) ) {
				define( 'LATEPOINT_TABLE_ORDER_INVOICES', $wpdb->prefix . 'latepoint_order_invoices' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_INTENT_STATUS_NEW' ) ) {
				define( 'LATEPOINT_ORDER_INTENT_STATUS_NEW', 'new' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_INTENT_STATUS_CONVERTED' ) ) {
				define( 'LATEPOINT_ORDER_INTENT_STATUS_CONVERTED', 'converted' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_INTENT_STATUS_PROCESSING' ) ) {
				define( 'LATEPOINT_ORDER_INTENT_STATUS_PROCESSING', 'processing' );
			}

			if ( ! defined( 'LATEPOINT_TRANSACTION_INTENT_STATUS_NEW' ) ) {
				define( 'LATEPOINT_TRANSACTION_INTENT_STATUS_NEW', 'new' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_INTENT_STATUS_CONVERTED' ) ) {
				define( 'LATEPOINT_TRANSACTION_INTENT_STATUS_CONVERTED', 'converted' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_INTENT_STATUS_PROCESSING' ) ) {
				define( 'LATEPOINT_TRANSACTION_INTENT_STATUS_PROCESSING', 'processing' );
			}

			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_APPROVED' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_APPROVED', 'approved' );
			}
			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_PENDING' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_PENDING', 'pending' );
			}
			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING', 'payment_pending' );
			}
			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_CANCELLED' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_CANCELLED', 'cancelled' );
			}
			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_NO_SHOW' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_NO_SHOW', 'no_show' );
			}
			if ( ! defined( 'LATEPOINT_BOOKING_STATUS_COMPLETED' ) ) {
				define( 'LATEPOINT_BOOKING_STATUS_COMPLETED', 'completed' );
			}

			if ( ! defined( 'LATEPOINT_JOB_STATUS_COMPLETED' ) ) {
				define( 'LATEPOINT_JOB_STATUS_COMPLETED', 'completed' );
			}
			if ( ! defined( 'LATEPOINT_JOB_STATUS_SCHEDULED' ) ) {
				define( 'LATEPOINT_JOB_STATUS_SCHEDULED', 'scheduled' );
			}
			if ( ! defined( 'LATEPOINT_JOB_STATUS_CANCELLED' ) ) {
				define( 'LATEPOINT_JOB_STATUS_CANCELLED', 'cancelled' );
			}
			if ( ! defined( 'LATEPOINT_JOB_STATUS_ERROR' ) ) {
				define( 'LATEPOINT_JOB_STATUS_ERROR', 'error' );
			}

			// payment statuses
			if ( ! defined( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_PROCESSING' ) ) {
				define( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_PROCESSING', 'processing' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_NOT_PAID' ) ) {
				define( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_NOT_PAID', 'not_paid' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_PAID' ) ) {
				define( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_PAID', 'fully_paid' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_REFUNDED' ) ) {
				define( 'LATEPOINT_TRANSACTION_PAYMENT_STATUS_REFUNDED', 'refunded' );
			}

			// order statuses
			if ( ! defined( 'LATEPOINT_ORDER_STATUS_OPEN' ) ) {
				define( 'LATEPOINT_ORDER_STATUS_OPEN', 'open' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_STATUS_CANCELLED' ) ) {
				define( 'LATEPOINT_ORDER_STATUS_CANCELLED', 'cancelled' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_STATUS_COMPLETED' ) ) {
				define( 'LATEPOINT_ORDER_STATUS_COMPLETED', 'completed' );
			}

			// order fulfillment statuses
			if ( ! defined( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED' ) ) {
				define( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED', 'not_fulfilled' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_FULFILLED' ) ) {
				define( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_FULFILLED', 'fulfilled' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_PARTIALLY_FULFILLED' ) ) {
				define( 'LATEPOINT_ORDER_FULFILLMENT_STATUS_PARTIALLY_FULFILLED', 'partially_fulfilled' );
			}

			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID', 'not_paid' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_PAID' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_PAID', 'partially_paid' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID', 'fully_paid' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_PROCESSING' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_PROCESSING', 'processing' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_REFUNDED' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_REFUNDED', 'refunded' );
			}
			if ( ! defined( 'LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_REFUNDED' ) ) {
				define( 'LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_REFUNDED', 'partially_refunded' );
			}

			if ( ! defined( 'LATEPOINT_DEFAULT_TIME_SYSTEM' ) ) {
				define( 'LATEPOINT_DEFAULT_TIME_SYSTEM', '12' );
			}
			if ( ! defined( 'LATEPOINT_DEFAULT_DATE_FORMAT' ) ) {
				define( 'LATEPOINT_DEFAULT_DATE_FORMAT', 'm/d/Y' );
			}
			if ( ! defined( 'LATEPOINT_DATETIME_DB_FORMAT' ) ) {
				define( 'LATEPOINT_DATETIME_DB_FORMAT', 'Y-m-d H:i:s' );
			}

			if ( ! defined( 'LATEPOINT_STATUS_ERROR' ) ) {
				define( 'LATEPOINT_STATUS_ERROR', 'error' );
			}
			if ( ! defined( 'LATEPOINT_STATUS_SUCCESS' ) ) {
				define( 'LATEPOINT_STATUS_SUCCESS', 'success' );
			}

			if ( ! defined( 'LATEPOINT_SERVICE_STATUS_ACTIVE' ) ) {
				define( 'LATEPOINT_SERVICE_STATUS_ACTIVE', 'active' );
			}
			if ( ! defined( 'LATEPOINT_SERVICE_STATUS_DISABLED' ) ) {
				define( 'LATEPOINT_SERVICE_STATUS_DISABLED', 'disabled' );
			}

			if ( ! defined( 'LATEPOINT_SERVICE_VISIBILITY_VISIBLE' ) ) {
				define( 'LATEPOINT_SERVICE_VISIBILITY_VISIBLE', 'visible' );
			}
			if ( ! defined( 'LATEPOINT_SERVICE_VISIBILITY_HIDDEN' ) ) {
				define( 'LATEPOINT_SERVICE_VISIBILITY_HIDDEN', 'hidden' );
			}


			if ( ! defined( 'LATEPOINT_LOCATION_STATUS_ACTIVE' ) ) {
				define( 'LATEPOINT_LOCATION_STATUS_ACTIVE', 'active' );
			}
			if ( ! defined( 'LATEPOINT_LOCATION_STATUS_DISABLED' ) ) {
				define( 'LATEPOINT_LOCATION_STATUS_DISABLED', 'disabled' );
			}

			if ( ! defined( 'LATEPOINT_AGENT_STATUS_ACTIVE' ) ) {
				define( 'LATEPOINT_AGENT_STATUS_ACTIVE', 'active' );
			}
			if ( ! defined( 'LATEPOINT_AGENT_STATUS_DISABLED' ) ) {
				define( 'LATEPOINT_AGENT_STATUS_DISABLED', 'disabled' );
			}

			if ( ! defined( 'LATEPOINT_BUNDLE_STATUS_ACTIVE' ) ) {
				define( 'LATEPOINT_BUNDLE_STATUS_ACTIVE', 'active' );
			}
			if ( ! defined( 'LATEPOINT_BUNDLE_STATUS_DISABLED' ) ) {
				define( 'LATEPOINT_BUNDLE_STATUS_DISABLED', 'disabled' );
			}

			if ( ! defined( 'LATEPOINT_BUNDLE_VISIBILITY_VISIBLE' ) ) {
				define( 'LATEPOINT_BUNDLE_VISIBILITY_VISIBLE', 'visible' );
			}
			if ( ! defined( 'LATEPOINT_BUNDLE_VISIBILITY_HIDDEN' ) ) {
				define( 'LATEPOINT_BUNDLE_VISIBILITY_HIDDEN', 'hidden' );
			}

			if ( ! defined( 'LATEPOINT_ITEM_VARIANT_BOOKING' ) ) {
				define( 'LATEPOINT_ITEM_VARIANT_BOOKING', 'booking' );
			}
			if ( ! defined( 'LATEPOINT_ITEM_VARIANT_BUNDLE' ) ) {
				define( 'LATEPOINT_ITEM_VARIANT_BUNDLE', 'bundle' );
			}

			if ( ! defined( 'LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL' ) ) {
				define( 'LATEPOINT_DEFAULT_TIMEBLOCK_INTERVAL', 30 );
			}
			if ( ! defined( 'LATEPOINT_DEFAULT_PHONE_CODE' ) ) {
				define( 'LATEPOINT_DEFAULT_PHONE_CODE', '+1' );
			}
			if ( ! defined( 'LATEPOINT_DEFAULT_PHONE_FORMAT' ) ) {
				define( 'LATEPOINT_DEFAULT_PHONE_FORMAT', '(999) 999-9999' );
			}

			if ( ! defined( 'LATEPOINT_TRANSACTION_STATUS_SUCCEEDED' ) ) {
				define( 'LATEPOINT_TRANSACTION_STATUS_SUCCEEDED', 'succeeded' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_STATUS_PROCESSING' ) ) {
				define( 'LATEPOINT_TRANSACTION_STATUS_PROCESSING', 'processing' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_STATUS_FAILED' ) ) {
				define( 'LATEPOINT_TRANSACTION_STATUS_FAILED', 'failed' );
			}

			// invoices
			if ( ! defined( 'LATEPOINT_INVOICE_STATUS_NOT_PAID' ) ) {
				define( 'LATEPOINT_INVOICE_STATUS_NOT_PAID', 'not_paid' );
			}
			if ( ! defined( 'LATEPOINT_INVOICE_STATUS_PAID' ) ) {
				define( 'LATEPOINT_INVOICE_STATUS_PAID', 'paid' );
			}
			if ( ! defined( 'LATEPOINT_INVOICE_STATUS_PARTIALLY_PAID' ) ) {
				define( 'LATEPOINT_INVOICE_STATUS_PARTIALLY_PAID', 'partially_paid' );
			}
			if ( ! defined( 'LATEPOINT_INVOICE_STATUS_DRAFT' ) ) {
				define( 'LATEPOINT_INVOICE_STATUS_DRAFT', 'draft' );
			}
			if ( ! defined( 'LATEPOINT_INVOICE_STATUS_VOIDED' ) ) {
				define( 'LATEPOINT_INVOICE_STATUS_VOIDED', 'voided' );
			}

			// PAYMENTS

			if ( ! defined( 'LATEPOINT_PAYMENT_PROCESSOR_STRIPE' ) ) {
				define( 'LATEPOINT_PAYMENT_PROCESSOR_STRIPE', 'stripe' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_PROCESSOR_BRAINTREE' ) ) {
				define( 'LATEPOINT_PAYMENT_PROCESSOR_BRAINTREE', 'braintree' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_PROCESSOR_PAYPAL' ) ) {
				define( 'LATEPOINT_PAYMENT_PROCESSOR_PAYPAL', 'paypal' );
			}

			if ( ! defined( 'LATEPOINT_TRANSACTION_KIND_CAPTURE' ) ) {
				define( 'LATEPOINT_TRANSACTION_KIND_CAPTURE', 'capture' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_KIND_AUTHORIZATION' ) ) {
				define( 'LATEPOINT_TRANSACTION_KIND_AUTHORIZATION', 'authorization' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_KIND_REFUND' ) ) {
				define( 'LATEPOINT_TRANSACTION_KIND_REFUND', 'refund' );
			}
			if ( ! defined( 'LATEPOINT_TRANSACTION_KIND_VOID' ) ) {
				define( 'LATEPOINT_TRANSACTION_KIND_VOID', 'void' );
			}

			if ( ! defined( 'LATEPOINT_PAYMENT_METHOD_LOCAL' ) ) {
				define( 'LATEPOINT_PAYMENT_METHOD_LOCAL', 'local' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_METHOD_PAYPAL' ) ) {
				define( 'LATEPOINT_PAYMENT_METHOD_PAYPAL', 'paypal' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_METHOD_CARD' ) ) {
				define( 'LATEPOINT_PAYMENT_METHOD_CARD', 'card' );
			}

			if ( ! defined( 'LATEPOINT_PAYMENT_TIME_LATER' ) ) {
				define( 'LATEPOINT_PAYMENT_TIME_LATER', 'later' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_TIME_NOW' ) ) {
				define( 'LATEPOINT_PAYMENT_TIME_NOW', 'now' );
			}

			if ( ! defined( 'LATEPOINT_VALUE_ON' ) ) {
				define( 'LATEPOINT_VALUE_ON', 'on' );
			}
			if ( ! defined( 'LATEPOINT_VALUE_OFF' ) ) {
				define( 'LATEPOINT_VALUE_OFF', 'off' );
			}

			if ( ! defined( 'LATEPOINT_STATUS_ACTIVE' ) ) {
				define( 'LATEPOINT_STATUS_ACTIVE', 'active' );
			}
			if ( ! defined( 'LATEPOINT_STATUS_DISABLED' ) ) {
				define( 'LATEPOINT_STATUS_DISABLED', 'disabled' );
			}

			if ( ! defined( 'LATEPOINT_PAYMENT_PORTION_FULL' ) ) {
				define( 'LATEPOINT_PAYMENT_PORTION_FULL', 'full' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_PORTION_REMAINING' ) ) {
				define( 'LATEPOINT_PAYMENT_PORTION_REMAINING', 'remaining' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_PORTION_DEPOSIT' ) ) {
				define( 'LATEPOINT_PAYMENT_PORTION_DEPOSIT', 'deposit' );
			}
			if ( ! defined( 'LATEPOINT_PAYMENT_PORTION_CUSTOM' ) ) {
				define( 'LATEPOINT_PAYMENT_PORTION_CUSTOM', 'custom' );
			}

			if ( ! defined( 'LATEPOINT_ANY_AGENT' ) ) {
				define( 'LATEPOINT_ANY_AGENT', 'any' );
			}
			if ( ! defined( 'LATEPOINT_ANY_LOCATION' ) ) {
				define( 'LATEPOINT_ANY_LOCATION', 'any' );
			}

			if ( ! defined( 'LATEPOINT_ANY_AGENT_ORDER_RANDOM' ) ) {
				define( 'LATEPOINT_ANY_AGENT_ORDER_RANDOM', 'random' );
			}
			if ( ! defined( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH' ) ) {
				define( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH', 'price_high' );
			}
			if ( ! defined( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW' ) ) {
				define( 'LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW', 'price_low' );
			}
			if ( ! defined( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH' ) ) {
				define( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH', 'busy_high' );
			}
			if ( ! defined( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW' ) ) {
				define( 'LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW', 'busy_low' );
			}

			if ( ! defined( 'LATEPOINT_ALL' ) ) {
				define( 'LATEPOINT_ALL', 'all' );
			}

			// Stripe Connect
			if ( ! defined( 'LATEPOINT_STRIPE_CONNECT_URL' ) ) {
//				define( 'LATEPOINT_STRIPE_CONNECT_URL', 'https://app.latepoint.com' );
				define( 'LATEPOINT_STRIPE_CONNECT_URL', 'https://latepoint-laravel.test' );
			}
		}


		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			// CONTROLLERS
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/pro_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/default_agent_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/activities_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/search_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/customers_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/services_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/carts_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/transactions_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/orders_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/auth_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/processes_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/process_jobs_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/settings_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/form_fields_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/bookings_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/dashboard_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/wizard_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/notifications_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/steps_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/calendars_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/booking_form_settings_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/integrations_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/customer_cabinet_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/manage_booking_by_key_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/manage_order_by_key_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/events_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/stripe_connect_controller.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/controllers/invoices_controller.php' );



			// MODELS
			include_once( LATEPOINT_ABSPATH . 'lib/models/model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/bundle_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/cart_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/cart_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/cart_item_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/order_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/order_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/order_item_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/activity_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/work_period_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/agent_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/service_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/connector_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/service_category_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/customer_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/settings_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/booking_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/step_settings_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/transaction_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/transaction_intent_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/booking_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/customer_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/agent_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/service_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/location_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/location_category_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/session_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/order_intent_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/order_intent_meta_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/process_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/process_job_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/join_bundles_services_model.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/models/invoice_model.php' );


			// HELPERS
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/wp_datetime.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/router_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/sessions_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/auth_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/encrypt_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/license_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/form_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/migrations_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/util_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/debug_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/wp_user_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/menu_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/image_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/events_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/icalendar_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/version_specific_updates_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/calendar_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/meeting_systems_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/marketing_systems_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/timeline_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/booking_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/order_intent_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/activities_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/settings_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/customer_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/processes_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/agent_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/service_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/database_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/money_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/time_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/notifications_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/email_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/sms_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/styles_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/work_periods_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/bundles_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/carts_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/orders_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/replacer_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/payments_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/resource_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/meta_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/shortcodes_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/connector_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/location_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/csv_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/steps_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/params_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/process_jobs_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/blocks_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/roles_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/price_breakdown_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/stripe_connect_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/pages_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/elementor_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/bricks_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/transaction_intent_helper.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/helpers/invoices_helper.php' );

			// MISC
			include_once( LATEPOINT_ABSPATH . 'lib/misc/router.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/time_period.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/blocked_period.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/booked_period.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/stripe_connect_customer.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/booking_request.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/booking_resource.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/work_period.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/filter.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/booking_slot.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/process_event.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/process_action.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/role.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/user.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/misc/step.php' );

			// MAILERS
			include_once( LATEPOINT_ABSPATH . 'lib/mailers/mailer.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/mailers/agent_mailer.php' );
			include_once( LATEPOINT_ABSPATH . 'lib/mailers/customer_mailer.php' );


			do_action( 'latepoint_includes' );
		}


		/**
		 * Hook into actions and filters.
		 */
		public function init_hooks() {
			$siteurl = get_site_option( 'siteurl' );
			if ( $siteurl ) {
				$siteurl_hash = md5( $siteurl );
			} else {
				$siteurl_hash = '';
			}

			if ( ! defined( 'LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE' ) ) {
				define( 'LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE', 'latepoint_customer_logged_in_' . $siteurl_hash );
			}
			if ( ! defined( 'LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE' ) ) {
				define( 'LATEPOINT_ADMIN_MENU_LAYOUT_STYLE_COOKIE', 'latepoint_admin_menu_layout_style_' . $siteurl_hash );
			}
			if ( ! defined( 'LATEPOINT_SELECTED_TIMEZONE_COOKIE' ) ) {
				define( 'LATEPOINT_SELECTED_TIMEZONE_COOKIE', 'latepoint_selected_timezone_' . $siteurl_hash );
			}

			if ( ! defined( 'LATEPOINT_CART_COOKIE' ) ) {
				define( 'LATEPOINT_CART_COOKIE', 'latepoint_cart_' . $siteurl_hash );
			}


			// Activation hook
			register_activation_hook( __FILE__, array( $this, 'create_required_tables' ) );
			register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
			register_deactivation_hook( __FILE__, [ $this, 'on_deactivate' ] );


			add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'init', array( $this, 'init_widgets' ), 11 );

			add_action( 'admin_menu', array( $this, 'init_menus' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_front_scripts_and_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts_and_styles' ) );
			add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ), 40 );
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			add_filter( 'cron_schedules', [ $this, 'add_custom_cron_schedules' ] );

			// used for testing of localhost to prevent wordpress issues with getting files from local server
			if ( LATEPOINT_ALLOW_LOCAL_SERVER ) {
				add_filter( 'http_request_args', [ $this, 'disable_localhost_url_check_for_development' ] );
			}

			// Add Link to latepoint to admin bar
			add_action( 'admin_bar_menu', array( $this, 'add_latepoint_link_to_admin_bar' ), 999 );


			// fix for output buffering error in WP
			// remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

			add_action( 'wp_loaded', array( $this, 'pre_route_call' ) );


			// Create router action
			// ajax
			add_action( 'wp_ajax_latepoint_route_call', array( $this, 'route_call' ) );
			add_action( 'wp_ajax_nopriv_latepoint_route_call', array( $this, 'route_call' ) );
			// admin custom post/get
			add_action( 'admin_post_latepoint_route_call', array( $this, 'route_call' ) );
			add_action( 'admin_post_nopriv_latepoint_route_call', array( $this, 'route_call' ) );

			// crons
			add_action( 'latepoint_clear_old_activity_logs', [ $this, 'clear_old_activity_logs' ] );


			add_action( 'latepoint_on_addon_activate', [ $this, 'init_addon' ], 10, 2 );
			add_action( 'latepoint_on_addon_deactivate', [ $this, 'deactivate_addon' ], 10, 2 );


			add_action( 'latepoint_email_processor_settings', [ $this, 'email_processor_settings' ], 10, 2 );

			// order invoices
			add_action( 'latepoint_order_created', 'OsInvoicesHelper::create_invoice_for_new_order' );

			// Auth
			add_filter( 'login_redirect', [ $this, 'redirect_manager_and_agent_to_latepoint' ], 10, 3 );


			// But WordPress has a whitelist of variables it allows, so we must put it on that list
			add_action( 'query_vars', array( $this, 'front_route_query_vars' ) );

			// If this is done, we can access it later
			// This example checks very early in the process:
			// if the variable is set, we include our page and stop execution after it
			add_action( 'parse_request', array( $this, 'front_route_parse_request' ) );


			add_action( 'admin_init', array( $this, 'redirect_after_activation' ) );

			add_filter( 'display_post_states', 'OsPagesHelper::add_display_post_states', 10, 2);

			// allow agents to access admin when woocommerce plugin is installed
			add_filter( 'woocommerce_prevent_admin_access', [
				$this,
				'woocommerce_allow_agent_to_access_admin'
			], 20, 1 );

			// plugin related hooks
			add_action( 'latepoint_model_save', [ $this, 'save_connected_wordpress_user' ] );

			// Stripe Connect

			add_filter( 'latepoint_payment_processors', 'OsStripeConnectHelper::register_payment_processor' );
			add_action( 'latepoint_payment_processor_settings', 'OsStripeConnectHelper::add_settings_fields', 10 );
			add_action( 'latepoint_step_payment__pay_content', 'OsStripeConnectHelper::output_payment_step_contents', 10 );
			add_action( 'latepoint_order_payment__pay_content', 'OsStripeConnectHelper::output_order_payment_pay_contents', 10 );

			add_filter( 'latepoint_convert_charge_amount_to_requirements', 'OsStripeConnectHelper::convert_charge_amount_to_requirements', 10, 2 );
			add_filter( 'latepoint_process_payment_for_order_intent', 'OsStripeConnectHelper::process_payment', 10, 2 );
			add_filter( 'latepoint_process_payment_for_transaction_intent', 'OsStripeConnectHelper::process_payment_for_transaction_intent', 10, 2 );

			add_filter( 'latepoint_get_all_payment_times', 'OsStripeConnectHelper::add_all_payment_methods_to_payment_times' );
			add_filter( 'latepoint_get_enabled_payment_times', 'OsStripeConnectHelper::add_enabled_payment_methods_to_payment_times' );

			add_action( 'latepoint_customer_edit_form_after', 'OsStripeConnectHelper::output_stripe_link_on_customer_quick_form' );

			add_action('save_post', 'OsBlockHelper::save_blocks_styles');
			// misc
			add_action( 'latepoint_after_step_content', 'OsStepsHelper::output_preset_fields' );

			OsActivitiesHelper::init_hooks();
			OsProcessJobsHelper::init_hooks();

			do_action('latepoint_init_hooks');
		}


		function add_custom_cron_schedules( $schedules ) {
			if ( ! isset( $schedules['latepoint_5_minutes'] ) ) {
				$schedules['latepoint_5_minutes'] = array(
					'interval' => 5 * 60,
					'display'  => __( 'Once every 5 minutes', 'latepoint' )
				);
			}

			return $schedules;
		}

		function woocommerce_allow_agent_to_access_admin( $prevent_access ) {
			if ( OsAuthHelper::is_agent_logged_in() ) {
				$prevent_access = false;
			}

			return $prevent_access;
		}

		function email_processor_settings( $processor_code ) {
			if ( $processor_code == 'wp_mail' ) {
				echo '<div class="sub-section-row">
					      <div class="sub-section-label">
					        <h3>' . esc_html__( 'Email Settings', 'latepoint' ) . '</h3>
					      </div>
					      <div class="sub-section-content">
						      <div class="os-row">
										<div class="os-col-4">' . OsFormHelper::text_field( 'settings[notification_email_setting_from_name]', __( 'From Name', 'latepoint' ), OsSettingsHelper::get_settings_value( 'notification_email_setting_from_name', get_bloginfo( 'name' ) ), [ 'theme' => 'simple' ] ) . '</div>
										<div class="os-col-8">' . OsFormHelper::text_field( 'settings[notification_email_setting_from_email]', __( 'From Email Address', 'latepoint' ), OsSettingsHelper::get_settings_value( 'notification_email_setting_from_email', get_bloginfo( 'admin_email' ) ), [ 'theme' => 'simple' ] ) . '</div>
									</div>
								</div>
							</div>';
			}
		}

		public function save_connected_wordpress_user( $customer ) {
			if ( $customer->is_new_record() ) {
				return;
			}
			if ( $customer instanceof OsCustomerModel ) {
				if ( $customer->wordpress_user_id ) {
					// has connected wp user
					$wp_user = get_user_by( 'id', $customer->wordpress_user_id );
					if ( $wp_user && ! is_super_admin( $wp_user->ID ) ) {
						// update linked wordpress user
						if ( $customer->first_name && $customer->first_name != $wp_user->first_name ) {
							$wp_user->first_name = $customer->first_name;
						}
						if ( $customer->last_name && $customer->last_name != $wp_user->last_name ) {
							$wp_user->last_name = $customer->last_name;
						}
						if ( $customer->email && $customer->email != $wp_user->user_email ) {
							$wp_user->user_email = $customer->email;
						}
						$result = wp_update_user( $wp_user );
						if ( is_wp_error( $result ) ) {
							error_log( 'Error saving wp user' );
						} else {
							// update user cookies because their data has changed
						}
					}
				} else {
					if ( OsAuthHelper::wp_users_as_customers() ) {
						OsCustomerHelper::create_wp_user_for_customer( $customer );
					}
				}
			}
		}

		// used for testing of localhost to prevent wordpress issues with getting files from local server
		public function disable_localhost_url_check_for_development( $parsed_args ) {
			$parsed_args['reject_unsafe_urls'] = false;

			return $parsed_args;
		}

		/*
		 * Check if current user is Agent or Manager, automatically redirect to latepoint dashboard instead of default WordPress admin
		 */
		public function redirect_manager_and_agent_to_latepoint( $redirect_to, $request, $user ) {
			if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
				if ( $user->has_cap( 'edit_bookings' ) || $user->has_cap( 'manage_latepoint' ) ) {
					return OsRouterHelper::build_link( [ 'dashboard', 'index' ] );
				}
			}

			return $redirect_to;
		}


		public function clear_old_activity_logs() {
			global $wpdb;
			$activity = new OsActivityModel();
			$cutoff   = OsTimeHelper::get_modified_now_object( '-1 week' )->format( 'Y-m-d H:i:s' );
			$wpdb->query( $wpdb->prepare( "DELETE FROM %i WHERE `created_at` < %s", [esc_sql($activity->table_name), $cutoff] ) );
		}

		public function init_addon( $addon_name, $addon_version ) {
			OsDatabaseHelper::check_db_version_for_addons();
		}

		public function deactivate_addon( $addon_name, $addon_version ) {
			OsDatabaseHelper::delete_addon_info( $addon_name, $addon_version );
		}


		public function on_deactivate() {
			wp_clear_scheduled_hook( 'latepoint_check_if_addons_update_available' );
			wp_clear_scheduled_hook( 'latepoint_clear_old_activity_logs' );
		}

		function on_activate() {
			OsRolesHelper::register_roles_in_wp();

			if ( ! wp_next_scheduled( 'latepoint_check_if_addons_update_available' ) ) {
				wp_schedule_event( time(), 'daily', 'latepoint_check_if_addons_update_available' );
			}

			if ( ! wp_next_scheduled( 'latepoint_clear_old_activity_logs' ) ) {
				wp_schedule_event( time(), 'daily', 'latepoint_clear_old_activity_logs' );
			}

			// if wizard has not been visited yet - redirect to it
			if ( ! get_option( 'latepoint_wizard_visited', false ) ) {
				add_option( 'latepoint_redirect_to_wizard', true );
			}

			// create default location
			OsLocationHelper::get_default_location();

			# create default pages if not existing (customer cabinet)
			OsPagesHelper::create_predefined_pages();

			do_action( 'latepoint_on_activate', 'latepoint', $this->version );
		}

		function redirect_after_activation() {
			if ( get_option( 'latepoint_redirect_to_wizard', false ) ) {
				delete_option( 'latepoint_redirect_to_wizard' );
				if ( ! isset( $_GET['activate-multi'] ) ) {
					wp_redirect( OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'wizard', 'setup' ) ) );
				}
			} elseif ( get_option( 'latepoint_show_version_5_modal', false ) ) {
				delete_option( 'latepoint_show_version_5_modal' );
				if ( ! isset( $_GET['activate-multi'] ) ) {
					wp_redirect( OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'settings', 'version_5_intro' ) ) );
				}
			}
		}

		public function front_route_parse_request( $wp ) {
			if ( isset( $wp->query_vars['latepoint_is_custom_route'] ) ) {
				if ( isset( $wp->query_vars['route_name'] ) ) {
					$this->route_call();
				}
			}
		}

		public function front_route_query_vars( $query_vars ) {
			$query_vars[] = 'latepoint_booking_id';
			$query_vars[] = 'latepoint_is_custom_route';
			$query_vars[] = 'route_name';

			return $query_vars;
		}

		public function route_call() {
			$route_name = OsRouterHelper::get_request_param( 'route_name', OsRouterHelper::build_route_name( 'dashboard', 'index' ) );
			OsRouterHelper::call_by_route_name( $route_name, OsRouterHelper::get_request_param( 'return_format', 'html' ) );
		}

		public function pre_route_call() {
			if ( OsRouterHelper::get_request_param( 'pre_route' ) ) {
				$this->route_call();
			}
		}



		/**
		 * Init LatePoint when WordPress Initialises.
		 */
		public function init() {
			OsAuthHelper::set_current_user();
			OsStepsHelper::init_step_actions();
			OsSettingsHelper::run_autoload();
			$this->register_post_types();
			$this->register_shortcodes();
			// Set up localisation.
			$this->load_plugin_textdomain();
			do_action( 'latepoint_init' );
			add_filter( 'http_request_host_is_external', '__return_true' );

			OsBlockHelper::register_blocks();
		}

		public function init_widgets(  ) {
			OsElementorHelper::init();
			OsBricksHelper::init();
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'latepoint', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}


		/**
		 * Register a custom menu page.
		 */
		function init_menus() {
			// link for admins
			add_menu_page(
				__( 'LatePoint', 'latepoint' ),
				__( 'LatePoint', 'latepoint' ),
				OsAuthHelper::get_current_user()->wp_capability,
				'latepoint',
				[ $this, 'route_call' ],
				'none'
			);


		}


		function add_latepoint_link_to_admin_bar( $wp_admin_bar ) {
			if ( OsAuthHelper::get_current_user()->has_backend_access() ) {
				// build link depending on who is logged in
				$args = [
					'id'    => 'latepoint_top_link',
					'title' => '<span class="latepoint-icon latepoint-icon-lp-logo" style="margin-right: 7px;"></span><span style="">' . __( 'LatePoint', 'latepoint' ) . '</span>',
					'href'  => OsRouterHelper::build_link( [ 'dashboard', 'index' ] ),
					'meta'  => array( 'class' => '' )
				];
				$wp_admin_bar->add_node( $args );
			}
		}


		/**
		 * Register shortcodes
		 */
		public function register_shortcodes() {
			add_shortcode( 'latepoint_book_button', array( 'OsShortcodesHelper', 'shortcode_latepoint_book_button' ) );
			add_shortcode( 'latepoint_book_form', array( 'OsShortcodesHelper', 'shortcode_latepoint_book_form' ) );
			add_shortcode( 'latepoint_customer_dashboard', array(
				'OsShortcodesHelper',
				'shortcode_latepoint_customer_dashboard'
			) );
			add_shortcode( 'latepoint_customer_login', array(
				'OsShortcodesHelper',
				'shortcode_latepoint_customer_login'
			) );
			add_shortcode( 'latepoint_resources', array( 'OsShortcodesHelper', 'shortcode_latepoint_resources' ) );
			add_shortcode( 'latepoint_calendar', array( 'OsShortcodesHelper', 'shortcode_latepoint_calendar' ) );
		}

		/*

		 SHORTCODES

		*/


		public function setup_environment() {
			if ( ! current_theme_supports( 'post-thumbnails' ) ) {
				add_theme_support( 'post-thumbnails' );
			}
			add_post_type_support( LATEPOINT_AGENT_POST_TYPE, 'thumbnail' );
			add_post_type_support( LATEPOINT_SERVICE_POST_TYPE, 'thumbnail' );
			add_post_type_support( LATEPOINT_CUSTOMER_POST_TYPE, 'thumbnail' );
		}


		public function create_required_tables() {
			OsDatabaseHelper::run_setup();
		}


		/**
		 * Register core post types.
		 */
		public function register_post_types() {
		}



		/**
		 * Register scripts and styles - FRONT
		 */
		public function load_front_scripts_and_styles() {
			$localized_vars = [
				'route_action'                        => 'latepoint_route_call',
				'response_status'                     => [ 'success' => 'success', 'error' => 'error' ],
				'ajaxurl'                             => admin_url( 'admin-ajax.php' ),
				'time_pick_style'                     => OsStepsHelper::get_time_pick_style(),
				'string_today'                        => __( 'Today', 'latepoint' ),
				'reload_booking_form_summary_route'   => OsRouterHelper::build_route_name( 'steps', 'reload_booking_form_summary_panel' ),
				'time_system'                         => OsTimeHelper::get_time_system(),
				'msg_not_available'                   => __( 'Not Available', 'latepoint' ),
				'booking_button_route'                => OsRouterHelper::build_route_name( 'steps', 'start' ),
				'remove_cart_item_route'                => OsRouterHelper::build_route_name( 'carts', 'remove_item_from_cart' ),
				'show_booking_end_time'               => ( OsSettingsHelper::is_on( 'show_booking_end_time' ) ) ? 'yes' : 'no',
				'customer_dashboard_url'              => OsSettingsHelper::get_customer_dashboard_url( true ),
				'demo_mode'                           => OsSettingsHelper::is_env_demo(),
				'cancel_booking_prompt'               => __( 'Are you sure you want to cancel this appointment?', 'latepoint' ),
				'single_space_message'                => __( 'Space Available', 'latepoint' ),
				'many_spaces_message'                 => __( 'Spaces Available', 'latepoint' ),
				'body_font_family'                    => '"latepoint", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif ',
				'headings_font_family'                => '"latepoint", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif ',
				'currency_symbol_before'              => OsSettingsHelper::get_settings_value( 'currency_symbol_before', '' ),
				'currency_symbol_after'               => OsSettingsHelper::get_settings_value( 'currency_symbol_after', '' ),
				'thousand_separator'                  => OsSettingsHelper::get_settings_value( 'thousand_separator', ',' ),
				'decimal_separator'                   => OsSettingsHelper::get_settings_value( 'decimal_separator', '.' ),
				'number_of_decimals'                  => OsSettingsHelper::get_settings_value( 'number_of_decimals', '2' ),
				'included_phone_countries'            => wp_json_encode( OsSettingsHelper::get_included_phone_countries() ),
				'default_phone_country'               => OsSettingsHelper::get_default_phone_country(),
				'is_timezone_selected'                => OsTimeHelper::is_timezone_saved_in_session(),
				'start_from_order_intent_route'       => OsRouterHelper::build_route_name( 'steps', 'start_from_order_intent' ),
				'start_from_order_intent_key'         => OsRouterHelper::get_request_param( 'latepoint_order_intent_key' ) ? OsRouterHelper::get_request_param( 'latepoint_order_intent_key' ) : '',
				'is_enabled_show_dial_code_with_flag' => OsSettingsHelper::is_enabled_show_dial_code_with_flag(),
				'mask_phone_number_fields'            => OsSettingsHelper::is_on( 'mask_phone_number_fields', LATEPOINT_VALUE_ON ),
				'msg_validation_presence'             => __( 'can not be blank', 'latepoint' ),
				'msg_validation_presence_checkbox'             => __( 'has to be checked', 'latepoint' ),
				'msg_validation_invalid'              => __( 'is invalid', 'latepoint' ),
				'msg_minutes_suffix'                  => __( ' minutes', 'latepoint' ),
				'is_stripe_connect_enabled'           => OsPaymentsHelper::is_payment_processor_enabled( OsStripeConnectHelper::$processor_code ),
				'check_order_intent_bookable_route'   => OsRouterHelper::build_route_name( 'steps', 'check_order_intent_bookable' ),
				'payment_environment'   => OsSettingsHelper::get_payments_environment(),
				'style_border_radius' => OsSettingsHelper::get_booking_form_border_radius(),
				'datepicker_timeslot_selected_label' => __('Selected', 'latepoint'),
				'order_payment_form_route' => OsRouterHelper::build_route_name('orders', 'payment_form')
			];

			if ( OsPaymentsHelper::is_payment_processor_enabled( OsStripeConnectHelper::$processor_code ) ) {
				$localized_vars['stripe_connect_key']          = OsStripeConnectHelper::get_connect_publishable_key();
				$localized_vars['stripe_connected_account_id'] = OsStripeConnectHelper::get_connect_account_id();
			}
			$localized_vars['stripe_connect_route_create_payment_intent'] = OsRouterHelper::build_route_name( 'stripe_connect', 'create_payment_intent' );
			$localized_vars['stripe_connect_route_create_payment_intent_for_transaction_intent'] = OsRouterHelper::build_route_name( 'stripe_connect', 'create_payment_intent_for_transaction' );

			// Stylesheets
			wp_enqueue_style( 'latepoint-main-front', $this->public_stylesheets() . 'front.css', false, $this->version );

			// add styles from options if gutenberg blocks exists
			OsBlockHelper::add_block_styles_to_page();

			// Javscripts

			// Addon scripts and styles
			do_action( 'latepoint_wp_enqueue_scripts' );

			if ( OsPaymentsHelper::is_payment_processor_enabled( OsStripeConnectHelper::$processor_code ) ) {
				wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', false, null );
			}

			wp_register_script( 'latepoint-vendor-front', $this->public_javascripts() . 'vendor-front.js', [ 'jquery' ], $this->version );
			wp_register_script( 'latepoint-main-front', $this->public_javascripts() . 'front.js', [
				'jquery',
				'latepoint-vendor-front',
				'wp-i18n'
			], $this->version );


			$localized_vars = apply_filters( 'latepoint_localized_vars_front', $localized_vars );

			wp_localize_script( 'latepoint-main-front', 'latepoint_helper', $localized_vars );
			wp_enqueue_script( 'latepoint-main-front' );


			$latepoint_css_variables = OsStylesHelper::generate_css_variables();
			wp_add_inline_style( 'latepoint-main-front', $latepoint_css_variables );
		}

		public function add_admin_body_class( $classes ) {
			if ( ( is_admin() ) && isset( $_GET['page'] ) && $_GET['page'] == 'latepoint' ) {
				$classes = $classes . ' latepoint-admin latepoint';
			}

			return $classes;
		}

		public function add_body_class( $classes ) {
			$classes[] = 'latepoint';

			return $classes;
		}


		/**
		 * Register admin scripts and styles - ADMIN
		 */
		public function load_admin_scripts_and_styles() {
			// Stylesheets
			wp_enqueue_style( 'latepoint-blocks-editor', LatePoint::plugin_url() . 'blocks/assets/css/editor-styles.css', array( 'wp-edit-blocks' ), $this->version );
			wp_enqueue_style( 'latepoint-main-admin', $this->public_stylesheets() . 'admin.css', false, $this->version );

			// Javscripts
			wp_enqueue_media();


			wp_enqueue_script( 'latepoint-vendor-admin', $this->public_javascripts() . 'vendor-admin.js', [ 'jquery' ], $this->version );
			wp_enqueue_script( 'latepoint-main-admin', $this->public_javascripts() . 'admin.js', [
				'jquery',
				'latepoint-vendor-admin',
				'wp-i18n'
			], $this->version );


			do_action( 'latepoint_admin_enqueue_scripts' );

			$localized_vars = [
				'route_action'                        => 'latepoint_route_call',
				'response_status'                     => [ 'success' => 'success', 'error' => 'error' ],
				'ajaxurl'                             => admin_url( 'admin-ajax.php' ),
				'value_all'                           => LATEPOINT_ALL,
				'value_on'                            => LATEPOINT_VALUE_ON,
				'value_off'                           => LATEPOINT_VALUE_OFF,
				'body_font_family'                    => '"latepoint", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif ',
				'headings_font_family'                => '"latepoint", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif ',
				'wp_locale'                           => get_locale(),
				'string_today'                        => __( 'Today', 'latepoint' ),
				'click_to_copy_done'                  => __( 'Copied', 'latepoint' ),
				'click_to_copy_prompt'                => __( 'Just click to copy', 'latepoint' ),
				'approve_confirm'                     => __( 'Are you sure you want to approve this booking?', 'latepoint' ),
				'reject_confirm'                      => __( 'Are you sure you want to reject this booking?', 'latepoint' ),
				'time_system'                         => OsTimeHelper::get_time_system(),
				'msg_not_available'                   => __( 'Not Available', 'latepoint' ),
				'msg_addon_activated'                 => __( 'Active', 'latepoint' ),
				'datepicker_timeslot_selected_label'  => __( 'Selected', 'latepoint' ),
				'string_minutes'                      => __( 'minutes', 'latepoint' ),
				'single_space_message'                => __( 'Space Available', 'latepoint' ),
				'many_spaces_message'                 => __( 'Spaces Available', 'latepoint' ),
				'currency_symbol_before'              => OsSettingsHelper::get_settings_value( 'currency_symbol_before', '' ),
				'currency_symbol_after'               => OsSettingsHelper::get_settings_value( 'currency_symbol_after', '' ),
				'thousand_separator'                  => OsSettingsHelper::get_settings_value( 'thousand_separator', ',' ),
				'decimal_separator'                   => OsSettingsHelper::get_settings_value( 'decimal_separator', '.' ),
				'number_of_decimals'                  => OsSettingsHelper::get_settings_value( 'number_of_decimals', '2' ),
				'included_phone_countries'            => wp_json_encode( OsSettingsHelper::get_included_phone_countries() ),
				'default_phone_country'               => OsSettingsHelper::get_default_phone_country(),
				'date_format'                         => OsSettingsHelper::get_date_format(),
				'date_format_for_js'                  => OsSettingsHelper::get_date_format_for_js(),
				'is_enabled_show_dial_code_with_flag' => OsSettingsHelper::is_enabled_show_dial_code_with_flag(),
				'mask_phone_number_fields'            => OsSettingsHelper::is_on( 'mask_phone_number_fields', LATEPOINT_VALUE_ON ),
				'msg_validation_presence'             => __( 'can not be blank', 'latepoint' ),
				'msg_validation_presence_checkbox'             => __( 'has to be checked', 'latepoint' ),
				'msg_validation_invalid'              => __( 'is invalid', 'latepoint' ),
				'msg_minutes_suffix'                  => __( ' minutes', 'latepoint' ),
				'order_item_variant_booking'          => LATEPOINT_ITEM_VARIANT_BOOKING,
				'order_item_variant_bundle'           => LATEPOINT_ITEM_VARIANT_BUNDLE
			];

			// Add block related variables
			$localized_vars = array_merge($localized_vars, OsBlockHelper::localized_vars_for_blocks());

			/**
			 * Array of localized variables to be available in latepoint_helper object in admin
			 *
			 * @since 5.0.0
			 * @hook latepoint_localized_vars_admin
			 *
			 * @param {array} $localized_vars The default array being filtered
			 * @returns {array} The filtered array of localized variables
			 */
			$localized_vars = apply_filters( 'latepoint_localized_vars_admin', $localized_vars );

			wp_localize_script( 'latepoint-main-admin', 'latepoint_helper', $localized_vars );


			$latepoint_css_variables = OsStylesHelper::generate_css_variables();
			wp_add_inline_style( 'latepoint-main-admin', $latepoint_css_variables );

		}

	}
endif;


$LATEPOINT = new LatePoint();