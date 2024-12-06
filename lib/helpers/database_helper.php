<?php

class OsDatabaseHelper {

	public static function run_setup() {
		self::install_database();
	}

	public static function check_db_version() {
		$current_db_version = OsSettingsHelper::get_db_version();
		if (!$current_db_version || version_compare(LATEPOINT_DB_VERSION, $current_db_version)) {
			self::install_database();
		}
	}

	// [name => 'addon_name', 'db_version' => '1.0.0', 'version' => '1.0.0']
	public static function get_installed_addons_list() {
		$installed_addons = [];
		$installed_addons = apply_filters('latepoint_installed_addons', $installed_addons);
		return $installed_addons;
	}


	// Check if addons databases are up to date
	public static function check_db_version_for_addons() {
		$is_new_addon_db_version_available = false;
		$installed_addons = self::get_installed_addons_list();
		if (empty($installed_addons)) return;
		foreach ($installed_addons as $installed_addon) {
			$current_addon_db_version = get_option($installed_addon['name'] . '_addon_db_version');
			if (!$current_addon_db_version || version_compare($current_addon_db_version, $installed_addon['db_version'])) {
				self::save_addon_info($installed_addon['name'], $installed_addon['db_version']);
				$is_new_addon_db_version_available = true;
			}
		}
		if ($is_new_addon_db_version_available) self::install_database_for_addons();
	}


	  public static function save_addon_info($name, $version){
	    update_option( $name . '_addon_db_version', $version );
	  }

	  public static function delete_addon_info($name, $version){
	    delete_option( $name . '_addon_db_version' );
	  }


	// Install queries for addons
	public static function install_database_for_addons() {
		$sqls = self::get_table_queries_for_addons();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		foreach ($sqls as $sql) {
			error_log(print_r(dbDelta($sql), true));
		}
	}


	public static function install_database() {
		$sqls = self::get_initial_table_queries();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		foreach ($sqls as $sql) {
			error_log(print_r(dbDelta($sql), true));
		}
		OsVersionSpecificUpdatesHelper::run_version_specific_updates();
		self::seed_initial_data();
		update_option('latepoint_db_version', LATEPOINT_DB_VERSION);
	}

	public static function seed_initial_data() {
		// if DB version is set (means that it's probably an update) skip seeding
		if (OsSettingsHelper::get_db_version()) return false;
		// if database was already seeded before - skip it
		if (OsSettingsHelper::get_settings_value('is_database_seeded', false)) return false;

		// set default booking status rules
		OsSettingsHelper::save_setting_by_name('default_booking_status', LATEPOINT_BOOKING_STATUS_APPROVED);
		OsSettingsHelper::save_setting_by_name('timeslot_blocking_statuses', LATEPOINT_BOOKING_STATUS_APPROVED);
		OsSettingsHelper::save_setting_by_name('calendar_hidden_statuses', LATEPOINT_BOOKING_STATUS_CANCELLED);
		OsSettingsHelper::save_setting_by_name('need_action_statuses', implode(',', [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING]));
		// create default processes
		$process = new OsProcessModel();
		$process->event_type = 'booking_created';
		$process->name = 'New Booking Notification';
		$actions = [];

		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( ! WP_Filesystem() ) {
			OsDebugHelper::log( __( 'Failed to initialise WC_Filesystem API while trying to setup notifications for initial data seed.', 'latepoint' ) );
		}else{
			global $wp_filesystem;
			foreach (['agent', 'customer'] as $user_type) {
				$action = [];
				$action['type'] = 'send_email';
				$action['settings']['to_email'] = '{{' . $user_type . '_full_name}} <{{' . $user_type . '_email}}>';
				$action['settings']['subject'] = ($user_type == 'agent') ? "New Appointment Received" : "Appointment Confirmation";
				$action['settings']['content'] = OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH . 'mailers/' . $user_type . '/booking_created.html'));
				$actions[\LatePoint\Misc\ProcessAction::generate_id()] = $action;
			}
		}

		$process_actions = OsProcessesHelper::iterate_trigger_conditions([], $actions);
		$process_actions[0]['time_offset'] = [];
		$process->actions_json = wp_json_encode($process_actions);
		if (!OsProcessesHelper::check_if_process_exists($process)) $process->save();

		/**
		 * Hook your initial data seed actions here
		 *
		 * @since 4.7.0
		 * @hook latepoint_seed_initial_data
		 *
		 */
		do_action('latepoint_seed_initial_data');
		OsSettingsHelper::save_setting_by_name('is_database_seeded', true);

	}

	public static function run_query(string $sql) {
		global $wpdb;
		OsDebugHelper::log_query($sql);
		return $wpdb->query($sql);
	}

	public static function run_queries($sqls) {
		global $wpdb;
		if ($sqls && is_array($sqls)) {
			foreach ($sqls as $sql) {
				$wpdb->query($sql);
				OsDebugHelper::log_query($sql);
			}
		}
	}


	// Get queries registered by addons
	public static function get_table_queries_for_addons() {
		$sqls = [];
		$sqls = apply_filters('latepoint_addons_sqls', $sqls);
		return $sqls;
	}


	public static function get_initial_table_queries() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sqls = [];


		// ---------------
		// STEPS
		// ---------------


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_STEPS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      title text,
      before_content text,
      after_content text,
      side_title text,
      side_description text,
      use_custom_image boolean,
      custom_image_id int(11),
      code varchar(100),
      parent_step_id smallint(6),
      position smallint(6),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";

		// ---------------
		// CART
		// ---------------

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CARTS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      uuid varchar(36),
      order_intent_id int(11),
      order_id int(11),
      coupon_code varchar(100),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      KEY uuid_index (uuid),
      KEY order_id_index (order_id),
      KEY order_intent_id_index (order_intent_id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CART_ITEMS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      cart_id int(11) NOT NULL,
      variant varchar(55),
      item_data text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      KEY cart_id_index (cart_id)
    ) $charset_collate;";


		// ---------------
		// ORDERS
		// ---------------

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ORDERS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      subtotal decimal(20,4),
      total decimal(20,4),
      status varchar(30) DEFAULT '" . LATEPOINT_ORDER_STATUS_OPEN . "' NOT NULL,
      fulfillment_status varchar(30) DEFAULT '" . LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED . "' NOT NULL,
      payment_status varchar(30) DEFAULT '" . LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID . "' NOT NULL,
      source_id varchar(100),
      source_url text,
      ip_address varchar(55),
      customer_id int(11) NOT NULL,
      customer_comment text,
      confirmation_code varchar(10),
      price_breakdown text,
      coupon_code varchar(100),
      coupon_discount decimal(20,4),
      tax_total decimal(20,4),
      initial_payment_data text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      KEY customer_id_index (customer_id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ORDER_ITEMS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      order_id int(11) NOT NULL,
      variant varchar(55),
      item_data text,
      subtotal decimal(20,4),
      total decimal(20,4),
      coupon_code varchar(100),
      coupon_discount decimal(20,4),
      tax_total decimal(20,4),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      KEY order_id_index (order_id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ORDER_INTENTS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      intent_key varchar(55) NOT NULL,
      customer_id int(11) NOT NULL,
      cart_items_data text,
      restrictions_data text,
      presets_data text,
      payment_data text,
      other_data text,
      order_id int(11),
      booking_form_page_url text,
      total decimal(20,4),
      subtotal decimal(20,4),
      coupon_code varchar(100),
      coupon_discount decimal(20,4),
      tax_total decimal(20,4),
      charge_amount decimal(20,4),
      specs_charge_amount varchar(55),
      price_breakdown text,
      status varchar(30) DEFAULT '" . LATEPOINT_ORDER_INTENT_STATUS_NEW . "' NOT NULL,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      UNIQUE KEY intent_key_index (intent_key)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ORDER_INVOICES . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      order_id int(11) NOT NULL,
      invoice_number varchar(10),
      data text,
      status varchar(30) DEFAULT '" . LATEPOINT_INVOICE_STATUS_NOT_PAID . "' NOT NULL,
      charge_amount decimal(20,4),
      due_at datetime,
      payment_portion varchar(55),
      access_key varchar(36),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      KEY order_id_index (order_id)
      UNIQUE KEY invoice_number_index (invoice_number)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_PAYMENT_REQUESTS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      order_id int(11) NOT NULL,
      invoice_id int(11) NOT NULL,
      charge_amount decimal(20,4),
      due_at datetime,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
      KEY invoice_id_index (invoice_id)
      KEY order_id_index (order_id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_PROCESS_JOBS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      process_id int(11) NOT NULL,
      object_id int(11) NOT NULL,
      object_model_type varchar(55),
      settings text,
      to_run_after_utc datetime,
      status varchar(30) DEFAULT '" . LATEPOINT_JOB_STATUS_SCHEDULED . "',
      run_result text,
      process_info text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_SESSIONS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      session_key varchar(55) NOT NULL,
      session_value longtext NOT NULL,
      expiration BIGINT UNSIGNED NOT NULL,
      hash varchar(50) NOT NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY session_key (session_key)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_BOOKINGS . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      booking_code varchar(10),
      start_date date,
      end_date date,
      start_time mediumint(9),
      end_time mediumint(9),
      start_datetime_utc datetime,
      end_datetime_utc datetime,
      buffer_before mediumint(9) NOT NULL,
      buffer_after mediumint(9) NOT NULL,
      duration mediumint(9),
      status varchar(30) DEFAULT '" . LATEPOINT_BOOKING_STATUS_PENDING . "' NOT NULL,
      customer_id mediumint(9) NOT NULL,
      service_id mediumint(9) NOT NULL,
      agent_id mediumint(9) NOT NULL,
      location_id mediumint(9),
      order_item_id mediumint(9),
      total_attendees mediumint(4),
      created_at datetime,
      updated_at datetime,
      KEY start_date_index (start_date),
      KEY end_date_index (end_date),
      KEY status_index (status),
      KEY customer_id_index (customer_id),
      KEY service_id_index (service_id),
      KEY agent_id_index (agent_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CART_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ORDER_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_BOOKING_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_PROCESSES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(110) NOT NULL,
      event_type varchar(110) NOT NULL,
      actions_json text,
      status varchar(30) DEFAULT '" . LATEPOINT_STATUS_ACTIVE . "',
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_SERVICE_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CUSTOMER_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_AGENT_META . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_SETTINGS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(110) NOT NULL,
      value longtext,
      created_at datetime,
      updated_at datetime,
      KEY name_index (name),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_LOCATIONS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      full_address text,
      status varchar(20) NOT NULL,
      category_id int(11),
      order_number int(11),
      selection_image_id int(11),
      created_at datetime,
      updated_at datetime,
      KEY status_index (status),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_LOCATION_CATEGORIES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL,
      short_description text,
      parent_id mediumint(9),
      selection_image_id int(11),
      order_number int(11),
      created_at datetime,
      updated_at datetime,
      KEY order_number_index (order_number),
      KEY parent_id_index (parent_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_BUNDLES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      short_description text,
      charge_amount decimal(20,4),
      deposit_amount decimal(20,4),
      status varchar(20) NOT NULL,
      visibility varchar(20) NOT NULL,
      order_number int(11),
      created_at datetime,
      updated_at datetime,
      KEY order_number_index (order_number),
      KEY status_index (status),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_JOIN_BUNDLES_SERVICES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      bundle_id mediumint(9),
      service_id mediumint(9),
      total_attendees mediumint(4),
      duration int(11),
      quantity mediumint(4),
      created_at datetime,
      updated_at datetime,
      KEY bundle_id_index (bundle_id),
      KEY service_id_index (service_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_SERVICES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      short_description text,
      is_price_variable boolean,
      price_min decimal(20,4),
      price_max decimal(20,4),
      charge_amount decimal(20,4),
      deposit_amount decimal(20,4),
      is_deposit_required boolean,
      duration_name varchar(255),
      override_default_booking_status varchar(255),
      duration int(11) NOT NULL,
      buffer_before int(11),
      buffer_after int(11),
      category_id int(11),
      order_number int(11),
      selection_image_id int(11),
      description_image_id int(11),
      bg_color varchar(20),
      timeblock_interval int(11),
      capacity_min int(4),
      capacity_max int(4),
      status varchar(20) NOT NULL,
      visibility varchar(20) NOT NULL,
      created_at datetime,
      updated_at datetime,
      KEY category_id_index (category_id),
      KEY order_number_index (order_number),
      KEY status_index (status),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_AGENTS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      avatar_image_id int(11),
      bio_image_id int(11),
      first_name varchar(255) NOT NULL,
      last_name varchar(255),
      display_name varchar(255),
      title varchar(255),
      bio text,
      features text,
      email varchar(110) NOT NULL,
      phone varchar(255),
      password varchar(255),
      custom_hours boolean,
      wp_user_id int(11),
      status varchar(20) NOT NULL,
      extra_emails text,
      extra_phones text,
      created_at datetime,
      updated_at datetime,
      KEY email_index (email),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_STEP_SETTINGS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      label varchar(50) NOT NULL,
      value text,
      step varchar(50),
      created_at datetime,
      updated_at datetime,
      KEY step_index (step),
      KEY label_index (label),
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CUSTOMERS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      first_name varchar(255),
      last_name varchar(255),
      email varchar(110) NOT NULL,
      phone varchar(255),
      avatar_image_id int(11),
      status varchar(50) NOT NULL,
      password varchar(255),
      activation_key varchar(255),
      account_nonse varchar(255),
      google_user_id varchar(255),
      facebook_user_id varchar(255),
      wordpress_user_id int(11),
      is_guest boolean,
      notes text,
      admin_notes text,
      created_at datetime,
      updated_at datetime,
      KEY email_index (email),
      KEY status_index (status),
      KEY wordpress_user_id_index (wordpress_user_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_SERVICE_CATEGORIES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL,
      short_description text,
      parent_id mediumint(9),
      selection_image_id int(11),
      order_number int(11),
      created_at datetime,
      updated_at datetime,
      KEY order_number_index (order_number),
      KEY parent_id_index (parent_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_CUSTOM_PRICES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11) NOT NULL,
      is_price_variable boolean,
      price_min decimal(20,4),
      price_max decimal(20,4),
      charge_amount decimal(20,4),
      is_deposit_required boolean,
      deposit_amount decimal(20,4),
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_WORK_PERIODS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11) NOT NULL,
      start_time smallint(6) NOT NULL,
      end_time smallint(6) NOT NULL,
      week_day tinyint(3) NOT NULL,
      custom_date date,
      chain_id varchar(20),
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      KEY week_day_index (week_day),
      KEY custom_date_index (custom_date),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_AGENTS_SERVICES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11),
      is_custom_hours BOOLEAN,
      is_custom_price BOOLEAN,
      is_custom_duration BOOLEAN,
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_ACTIVITIES . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11),
      booking_id int(11),
      service_id int(11),
      customer_id int(11),
      location_id int(11),
      order_id int(11),
      order_item_id int(11),
      code varchar(255) NOT NULL,
      description text,
      initiated_by varchar(100),
      initiated_by_id int(11),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";


		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_TRANSACTION_INTENTS . " (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      intent_key varchar(55) NOT NULL,
      order_id int(11) NOT NULL,
      customer_id int(11),
      invoice_id int(11),
      transaction_id int(11),
      payment_data text,
      charge_amount decimal(20,4),
      specs_charge_amount varchar(55),
      status varchar(30) DEFAULT '" . LATEPOINT_TRANSACTION_INTENT_STATUS_NEW . "' NOT NULL,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      UNIQUE KEY intent_key_index (intent_key)
    ) $charset_collate;";

		$sqls[] = "CREATE TABLE " . LATEPOINT_TABLE_TRANSACTIONS . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      token text,
      invoice_id int(11),
      order_id int(11),
      customer_id int(11),
      processor varchar(100),
      payment_method varchar(55),
      payment_portion varchar(55),
      kind varchar(40),
      status varchar(100) NOT NULL,
      amount decimal(20,4),
      notes text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";

		return $sqls;
	}


}