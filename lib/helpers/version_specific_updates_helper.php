<?php

class OsVersionSpecificUpdatesHelper {


	/**
	 *
	 * Used to target a specific version during an update
	 *
	 * @return bool
	 */
	public static function run_version_specific_updates() {
		$current_db_version = OsSettingsHelper::get_db_version();
		if (!$current_db_version) return false;
		$sqls = [];
		if (version_compare('1.0.2', $current_db_version) > 0) {
			// lower than 1.0.2
			$sqls = self::get_queries_for_nullable_columns();
			OsDatabaseHelper::run_queries($sqls);
		}
		if (version_compare('1.1.0', $current_db_version) > 0) {
			// lower than 1.1.0
			$sqls = self::set_end_date_for_bookings();
			OsDatabaseHelper::run_queries($sqls);
		}
		if (version_compare('1.3.0', $current_db_version) > 0) {
			// lower than 1.3.0
			$sqls = [];
			$sqls[] = "UPDATE " . LATEPOINT_TABLE_BOOKINGS . " SET total_attendees = 1 WHERE total_attendees IS NULL;";
			$sqls[] = "UPDATE " . LATEPOINT_TABLE_SERVICES . " SET visibility = '" . LATEPOINT_SERVICE_VISIBILITY_VISIBLE . "' WHERE visibility IS NULL OR visibility = '';";
			$sqls[] = "UPDATE " . LATEPOINT_TABLE_SERVICES . " SET capacity_min = 1 WHERE capacity_min IS NULL;";
			$sqls[] = "UPDATE " . LATEPOINT_TABLE_SERVICES . " SET capacity_max = 1 WHERE capacity_max IS NULL;";
			OsDatabaseHelper::run_queries($sqls);
		}
		if (version_compare('1.3.1', $current_db_version) > 0) {
			$sqls = [];
			$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_CUSTOMERS . " MODIFY COLUMN first_name varchar(255)";
			OsDatabaseHelper::run_queries($sqls);
		}
		if (version_compare('1.3.7', $current_db_version) > 0) {
			$sqls = [];
			$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_AGENTS . " MODIFY COLUMN wp_user_id int(11)";
			OsDatabaseHelper::run_queries($sqls);
		}
		if (version_compare('1.4.8', $current_db_version) > 0) {
			update_option('latepoint_db_seeded', true);
			OsSettingsHelper::save_setting_by_name('timeslot_blocking_statuses', LATEPOINT_BOOKING_STATUS_APPROVED);
			OsSettingsHelper::save_setting_by_name('calendar_hidden_statuses', LATEPOINT_BOOKING_STATUS_CANCELLED);
			OsSettingsHelper::save_setting_by_name('need_action_statuses', implode(',', [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING]));
			$tile_info = OsSettingsHelper::get_booking_template_for_calendar();
			$tile_info = OsUtilHelper::replace_single_curly_with_double($tile_info);
			OsSettingsHelper::save_setting_by_name('booking_template_for_calendar', $tile_info);

			// -------
			// Update {var} to {{var}}
			// -------

			// password reset message
			$content_to_replace = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('email_customer_password_reset_request_content', ''));
			OsSettingsHelper::save_setting_by_name('email_customer_password_reset_request_content', $content_to_replace);

			// new message (chat)
			$content_to_replace = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('email_notification_customer_has_new_message_content', ''));
			OsSettingsHelper::save_setting_by_name('email_notification_customer_has_new_message_content', $content_to_replace);

			// js tracking code
			$content_to_replace = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('confirmation_step_tracking_code', ''));
			OsSettingsHelper::save_setting_by_name('confirmation_step_tracking_code', $content_to_replace);

			// Google calendar
			$content_to_replace = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('google_calendar_event_summary_template', ''));
			if (!empty($content_to_replace)) OsSettingsHelper::save_setting_by_name('google_calendar_event_summary_template', $content_to_replace);
			$content_to_replace = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('google_calendar_event_description_template', ''));
			if (!empty($content_to_replace)) OsSettingsHelper::save_setting_by_name('google_calendar_event_description_template', $content_to_replace);


			// -------
			// migrate old notification system to processes
			// -------

			$process_actions = [];
			// STATUS CHANGE NOTIFICATION

			if (OsSettingsHelper::is_on('notifications_email')) {
				// email

				foreach (['agent', 'customer'] as $user_type) {
					$action = [];
					if (OsSettingsHelper::is_on('notification_' . $user_type . '_booking_status_changed')) {
						$action['type'] = 'send_email';
						$action['settings']['to_email'] = '{{' . $user_type . '_full_name}} <{{' . $user_type . '_email}}>';
						$action['settings']['subject'] = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('notification_' . $user_type . '_booking_status_changed_notification_subject', ''));
						$action['settings']['content'] = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value('notification_' . $user_type . '_booking_status_changed_notification_content', ''));
						$process_actions[\LatePoint\Misc\ProcessAction::generate_id()] = $action;
					}
				}
				if ($process_actions) {
					// put all under single process with multiple actions
					$process = new OsProcessModel();
					$process->event_type = 'booking_updated';
					$process->name = 'Booking status change notification';

					$trigger_conditions[] = ['object' => 'old_booking', 'property' => 'old_booking__status', 'operator' => 'changed', 'value' => ''];
					$process_actions = OsProcessesHelper::iterate_trigger_conditions($trigger_conditions, $process_actions);
					$process_actions[0]['time_offset'] = [];
					$process->actions_json = wp_json_encode($process_actions);
					if (!OsProcessesHelper::check_if_process_exists($process)) $process->save();
				}

			}


			$process_actions = [];
			// NEW BOOKING NOTIFICATION
			if (OsSettingsHelper::is_on('notifications_email')) {
				OsSettingsHelper::save_setting_by_name('notifications_email_processor', 'wp_mail');
				// email
				foreach (['agent', 'customer'] as $user_type) {
					$action = [];
					if (OsSettingsHelper::is_on('notification_' . $user_type . '_confirmation')) {
						$action['type'] = 'send_email';
						$action['settings']['to_email'] = '{{' . $user_type . '_email}}';
						$action['settings']['subject'] = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value((($user_type == 'agent') ? 'notification_agent_new_booking_notification_subject' : 'notification_customer_booking_confirmation_subject'), ''));
						$action['settings']['content'] = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value((($user_type == 'agent') ? 'notification_agent_new_booking_notification_content' : 'notification_customer_booking_confirmation_content'), ''));
						$process_actions[\LatePoint\Misc\ProcessAction::generate_id()] = $action;
					}
				}
			}
			if (OsSettingsHelper::is_on('notifications_sms')) {
				OsSettingsHelper::save_setting_by_name('notifications_sms_processor', 'twilio');
				// sms
				foreach (['agent', 'customer'] as $user_type) {
					$action = [];
					if (OsSettingsHelper::is_on('notification_sms_' . $user_type . '_confirmation')) {
						$action['type'] = 'send_sms';
						$action['settings']['to_phone'] = '{{' . $user_type . '_phone}}';
						$action['settings']['content'] = OsUtilHelper::replace_single_curly_with_double(OsSettingsHelper::get_settings_value((($user_type == 'agent') ? 'notification_sms_agent_new_booking_notification_message' : 'notification_sms_customer_booking_confirmation_message'), ''));
						$process_actions[\LatePoint\Misc\ProcessAction::generate_id()] = $action;
					}
				}
			}


			// webhooks for new booking

			// migrate webhooks for new booking into processes
			$webhooks = json_decode(OsSettingsHelper::get_settings_value('webhooks', ''), true);
			if ($webhooks) {
				foreach ($webhooks as $webhook) {
					// only process new booking
					if ($webhook['status'] != 'active' || $webhook['trigger'] != 'new_booking') continue;
					$action = [];
					$action['type'] = 'trigger_webhook';
					$action['settings']['url'] = $webhook['url'];
					$process_actions[$webhook['id']] = $action;
				}
			}

			// CREATE NEW BOOKING PROCESSES IF THERE ARE ANY ACTIONS
			if ($process_actions) {
				// put all under single process with multiple actions
				$process = new OsProcessModel();
				$process->event_type = 'booking_created';
				$process->name = 'Booking created notification';

				$process_actions = OsProcessesHelper::iterate_trigger_conditions([], $process_actions);
				$process_actions[0]['time_offset'] = [];
				$process->actions_json = wp_json_encode($process_actions);
				if (!OsProcessesHelper::check_if_process_exists($process)) $process->save();
			}

			// migrate other webhooks (not new booking) into processes
			if ($webhooks) {
				$process_actions_for_triggers = ['updated_booking' => [], 'new_customer' => [], 'new_transaction' => []];
				foreach ($webhooks as $webhook) {
					if ($webhook['status'] != 'active' || !in_array($webhook['trigger'], ['updated_booking', 'new_customer', 'new_transaction'])) continue;
					$process_actions_for_triggers[$webhook['trigger']][$webhook['id']] = ['type' => 'trigger_webhook', 'settings' => ['url' => $webhook['url']]];
				}
				foreach ($process_actions_for_triggers as $webhook_trigger => $actions) {
					if ($actions) {
						$process = new OsProcessModel();
						switch ($webhook_trigger) {
							case 'updated_booking':
								$process->name = 'Booking updated notification';
								$process->event_type = 'booking_updated';
								break;
							case 'new_customer':
								$process->name = 'New customer notification';
								$process->event_type = 'customer_created';
								break;
							case 'new_transaction':
								$process->name = 'New transaction notification';
								$process->event_type = 'transaction_created';
								break;
						}
						$process_actions = OsProcessesHelper::iterate_trigger_conditions([], $actions);
						$process_actions[0]['time_offset'] = [];
						$process->actions_json = wp_json_encode($process_actions);
						if (!OsProcessesHelper::check_if_process_exists($process)) $process->save();
					}
				}
			}

			// migrate reminders into processes
			// old example: {"rem_0zMZzZVY":{"name":"Reminder to customer","medium":"email","receiver":"customer","value":"7","unit":"day","when":"before","subject":"Reminder","content":"<p>Testing<\/p>","id":"rem_0zMZzZVY"}}
			// multiple: {"rem_POtZuDDd":{"name":"Sms Reminder before","medium":"sms","receiver":"customer","value":"7","unit":"day","when":"before","subject":"","content":"<p>Testing<\/p>","id":"rem_POtZuDDd"},"rem_q4kA6JwC":{"name":"Sms Reminder after","medium":"sms","receiver":"agent","value":"7","unit":"day","when":"after","subject":"test","content":"Testing","id":"rem_q4kA6JwC"},"rem_hR6YOF3w":{"name":"Email Reminder after","medium":"email","receiver":"agent","value":"7","unit":"day","when":"after","subject":"test","content":"Testing","id":"rem_hR6YOF3w"}}
			$reminders = json_decode(OsSettingsHelper::get_settings_value('reminders', ''), true);
			if ($reminders) {
				$processes = [];
				$actions = [];
				foreach ($reminders as $reminder) {

					// create action
					$action = [];
					$action_id = \LatePoint\Misc\ProcessAction::generate_id();
					$action['settings']['content'] = OsUtilHelper::replace_single_curly_with_double($reminder['content'] ?? '');
					switch ($reminder['medium']) {
						case 'sms':
							$action['type'] = 'send_sms';
							$action['settings']['to_phone'] = ($reminder['receiver'] == 'customer') ? '{{customer_phone}}' : '{{agent_phone}}';
							break;
						case 'email':
							$action['type'] = 'send_email';
							$action['settings']['to_email'] = ($reminder['receiver'] == 'customer') ? '{{customer_email}}' : '{{agent_email}}';
							$action['settings']['subject'] = OsUtilHelper::replace_single_curly_with_double($reminder['subject']);
							break;
					}

					// generate time offset
					$time_offset = ['value' => $reminder['value'], 'unit' => $reminder['unit'], 'before_after' => $reminder['when']];

					// attach to process
					if ($processes) {
						$existing = false;
						// try to find process that matches parameters
						for ($i = 0; $i < count($processes); $i++) {
							if ($processes[$i]['time_offset'] == $time_offset) {
								$processes[$i]['actions'][$action_id] = $action;
								$existing = true;
								break;
							}
						}
						// didn't find process with same time offset, create new
						if (!$existing) {
							$process = ['name' => $reminder['name'], 'event_type' => 'booking_start', 'time_offset' => $time_offset, 'actions' => []];
							$process['actions'][$action_id] = $action;
							$processes[] = $process;
						}
					} else {
						$process = ['name' => $reminder['name'], 'event_type' => 'booking_start', 'time_offset' => $time_offset, 'actions' => []];
						$process['actions'][$action_id] = $action;
						$processes[] = $process;
					}


				}
				if ($processes) {
					foreach ($processes as $process_data) {
						$process = new OsProcessModel();
						$process->event_type = $process_data['event_type'];
						$process->name = $process_data['name'];

						$process_actions = OsProcessesHelper::iterate_trigger_conditions([], $process_data['actions']);
						$process_actions[0]['time_offset'] = $process_data['time_offset'];
						$process->actions_json = wp_json_encode($process_actions);
						if (!OsProcessesHelper::check_if_process_exists($process)) $process->save();
					}
				}
			}

			// Update customer phone numbers to new E.164 format based on the country that was selected in settings
			$customers = new OsCustomerModel();
			$customers = $customers->get_results_as_models();
			foreach ($customers as $customer) {
				if (empty($customer->phone)) continue;
				$formatted_phone = OsUtilHelper::sanitize_phone_number($customer->phone, OsSettingsHelper::get_settings_value('country_phone_code', ''));
				if (!empty($formatted_phone)) $customer->update_attributes(['phone' => $formatted_phone]);
			}
			// update agent phone numbers
			$agents = new OsAgentModel();
			$agents = $agents->get_results_as_models();
			foreach ($agents as $agent) {
				if (empty($agent->phone)) continue;
				$formatted_phone = OsUtilHelper::sanitize_phone_number($agent->phone, OsSettingsHelper::get_settings_value('country_phone_code', ''));
				if (!empty($formatted_phone)) $agent->update_attributes(['phone' => $formatted_phone]);
			}
		}

		if (version_compare('1.4.91', $current_db_version) > 0) {
			$sqls = [];
			$has_column = OsDatabaseHelper::run_query("SHOW COLUMNS FROM " . LATEPOINT_TABLE_BOOKINGS . " LIKE 'start_datetime_gmt'");
			if ($has_column) $sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_BOOKINGS . " DROP COLUMN start_datetime_gmt";

			$has_column = OsDatabaseHelper::run_query("SHOW COLUMNS FROM " . LATEPOINT_TABLE_BOOKINGS . " LIKE 'end_datetime_gmt'");
			if ($has_column) $sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_BOOKINGS . " DROP COLUMN end_datetime_gmt";

			if (!empty($sqls)) OsDatabaseHelper::run_queries($sqls);
		}


		if (version_compare('2.0.0', $current_db_version) > 0) {
			// we used to have a typo in a column name, check if it still exists and assign its values to a correctly named column
			$has_typo = OsDatabaseHelper::run_query("SHOW COLUMNS FROM " . LATEPOINT_TABLE_BOOKINGS . " LIKE 'total_attendies'");
			if ($has_typo) {
				$sqls = [];
				$sqls[] = "UPDATE " . LATEPOINT_TABLE_BOOKINGS . " SET total_attendees = total_attendies WHERE total_attendees IS NULL";
				OsDatabaseHelper::run_queries($sqls);
			}

			// deactivate old addons that are replaced by a PRO addon
			$plugins_to_deactivate = [
				'latepoint-custom-fields/latepoint-custom-fields.php',
				'latepoint-locations/latepoint-locations.php',
				'latepoint-webhooks/latepoint-webhooks.php',
				'latepoint-qr-code/latepoint-qr-code.php',
				'latepoint-reminders/latepoint-reminders.php',
				'latepoint-role-manager/latepoint-role-manager.php',
				'latepoint-timezone-selector/latepoint-timezone-selector.php',
				'latepoint-group-bookings/latepoint-group-bookings.php',
				'latepoint-taxes/latepoint-taxes.php',
				'latepoint-service-durations/latepoint-service-durations.php',
				'latepoint-service-extras/latepoint-service-extras.php',
				'latepoint-messages/latepoint-messages.php',
				'latepoint-coupons/latepoint-coupons.php',
			];
			$deactivated_plugins = [];

			foreach ($plugins_to_deactivate as $plugin) {
				if (is_plugin_active($plugin)) {
					$deactivated_plugins[] = OsUtilHelper::extract_plugin_name_from_path($plugin);
					deactivate_plugins($plugin);
				}
			}

			$report = OsMigrationsHelper::migrate_from_version_4();

			if($deactivated_plugins) OsSettingsHelper::save_setting_by_name('migration_version_5_deactivated_plugins', implode(', ', $deactivated_plugins));

			// if wizard has not been visited yet - redirect to it
			add_option('latepoint_show_version_5_modal', true);
		}

		/**
		 * Hook your updates to database that need to be run for specific version of database
		 *
		 * @since 1.0.0
		 * @hook latepoint_run_version_specific_updates
		 *
		 * @param {string} version of database before the update
		 */
		do_action('latepoint_run_version_specific_updates', $current_db_version);
		return true;
	}



	public static function set_end_date_for_bookings() {
		$sqls = [];

		$sqls[] = "UPDATE " . LATEPOINT_TABLE_BOOKINGS . " SET end_date = start_date WHERE end_date IS NULL;";
		return $sqls;
	}

	public static function get_queries_for_nullable_columns() {
		$sqls = [];

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_BOOKINGS . "
					      MODIFY COLUMN ip_address varchar(55),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";


		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_CUSTOMER_META . "
					      MODIFY COLUMN meta_value text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_SETTINGS . "
					      MODIFY COLUMN value text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_SERVICES . "
					      MODIFY COLUMN short_description text,
					      MODIFY COLUMN is_price_variable boolean,
					      MODIFY COLUMN price_min decimal(20,4),
					      MODIFY COLUMN price_max decimal(20,4),
					      MODIFY COLUMN charge_amount decimal(20,4),
					      MODIFY COLUMN is_deposit_required boolean,
					      MODIFY COLUMN buffer_before int(11),
					      MODIFY COLUMN buffer_after int(11),
					      MODIFY COLUMN category_id int(11),
					      MODIFY COLUMN order_number int(11),
					      MODIFY COLUMN selection_image_id int(11),
					      MODIFY COLUMN description_image_id int(11),
					      MODIFY COLUMN bg_color varchar(20),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_AGENTS . "
					      MODIFY COLUMN avatar_image_id int(11),
					      MODIFY COLUMN last_name varchar(255),
					      MODIFY COLUMN phone varchar(255),
					      MODIFY COLUMN password varchar(255),
					      MODIFY COLUMN custom_hours boolean,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_STEP_SETTINGS . "
					      MODIFY COLUMN value text,
					      MODIFY COLUMN step varchar(50),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_CUSTOMERS . " 
						    MODIFY COLUMN last_name varchar(255),
						    MODIFY COLUMN phone varchar(255),
						    MODIFY COLUMN avatar_image_id int(11),
						    MODIFY COLUMN password varchar(255),
						    MODIFY COLUMN activation_key varchar(255),
						    MODIFY COLUMN account_nonse varchar(255),
						    MODIFY COLUMN google_user_id varchar(255),
						    MODIFY COLUMN facebook_user_id varchar(255),
						    MODIFY COLUMN is_guest boolean,
						    MODIFY COLUMN notes text,
						    MODIFY COLUMN created_at datetime,
						    MODIFY COLUMN updated_at datetime;";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_SERVICE_CATEGORIES . " 
					      MODIFY COLUMN short_description text,
					      MODIFY COLUMN parent_id mediumint(9),
					      MODIFY COLUMN selection_image_id int(11),
					      MODIFY COLUMN order_number int(11),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_CUSTOM_PRICES . " 
					      MODIFY COLUMN is_price_variable boolean,
					      MODIFY COLUMN price_min decimal(20,4),
					      MODIFY COLUMN price_max decimal(20,4),
					      MODIFY COLUMN charge_amount decimal(20,4),
					      MODIFY COLUMN is_deposit_required boolean,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_WORK_PERIODS . " 
					      MODIFY COLUMN custom_date date,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_AGENTS_SERVICES . " 
					      MODIFY COLUMN is_custom_hours BOOLEAN,
					      MODIFY COLUMN is_custom_price BOOLEAN,
					      MODIFY COLUMN is_custom_duration BOOLEAN,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_ACTIVITIES . " 
					      MODIFY COLUMN agent_id int(11),
					      MODIFY COLUMN booking_id int(11),
					      MODIFY COLUMN service_id int(11),
					      MODIFY COLUMN customer_id int(11),
					      MODIFY COLUMN description text,
					      MODIFY COLUMN initiated_by varchar(100),
					      MODIFY COLUMN initiated_by_id int(11),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

		$sqls[] = "ALTER TABLE " . LATEPOINT_TABLE_TRANSACTIONS . " 
					      MODIFY COLUMN notes text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";
		return $sqls;
	}
}