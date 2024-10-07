<?php 

class OsNotificationsHelper {

	public static function get_selected_processor_code_by_type(string $type): string{
		return OsSettingsHelper::get_settings_value('notifications_'.$type.'_processor', '');
	}
	public static function get_available_notification_processors_for_type(string $type): array{
		$notification_types = self::get_available_notification_types();
		return $notification_types[$type]['processors'] ?? [];
	}

	public static function is_notification_type_enabled(string $type){
		return !empty(self::get_selected_processor_code_by_type($type));
	}

	public static function is_notification_processor_enabled(string $type, string $processor_code){
		return (self::get_selected_processor_code_by_type($type) == $processor_code);
	}

	public static function get_email_headers_from(){
		$from_name = OsSettingsHelper::get_settings_value('notification_email_setting_from_name', get_bloginfo( 'name' ));
		$from_email = OsSettingsHelper::get_settings_value('notification_email_setting_from_email', get_bloginfo( 'admin_email' ));
		return (!empty($from_email) && !empty($from_name)) ? ($from_name.' <'.$from_email.'>') : (get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>');
	}

	public static function get_available_notification_types(){
		$notification_types = [];
		$notification_types['email'] = [
			'code' => 'email',
			'label' => __('Email', 'latepoint'),
			'processors' => [
				[
					'label' => __('Default WordPress Mailer', 'latepoint'),
					'code' => 'wp_mail',
					'image_url' => ''
				]
			]
		];
		$notification_types['sms'] = [
			'code' => 'sms',
			'label' => __('SMS', 'latepoint'),
			'processors' => OsSmsHelper::get_sms_processors()
		];
		/**
		 *
		 * Returns notification types with processors
		 *
		 * @since 4.7.0
		 * @hook latepoint_available_notification_types
		 *
		 * @param {array} notification types with processors
		 * @returns {array} list of notification types with processors
		 */
		return apply_filters('latepoint_available_notification_types', $notification_types);
	}

	/**
	 *
	 * Sends notification based on a type
	 *
	 * @param string $type
	 * @param array $notification_settings
	 *
	 * @return array
	 */
	public static function send(string $type, array $notification_settings = []): array{
		$result = [
			'status' => LATEPOINT_STATUS_ERROR,
			'message' => __('Nothing to run', 'latepoint')
		];
		if(!OsNotificationsHelper::is_notification_type_enabled($type)){
			$type_label = self::get_available_notification_types()[$type]['label'] ?? $type;
			// translators: %s is the type of notification
			$result['message'] = sprintf(__('%s notifications are disabled', 'latepoint'), $type_label);
			return $result;
		}
		switch ($type){
			case 'email':
				$to = $notification_settings['to'] ?? '';
				$subject = $notification_settings['subject'] ?? '';
				$content = $notification_settings['content'] ?? '';
				$mailer = new OsMailer();
				$result = OsEmailHelper::send_email($to, $subject, $content, $mailer->get_headers(), ($notification_settings['activity_data'] ?? []));
				break;
			case 'sms':
				$to = $notification_settings['to'] ?? '';
				$content = $notification_settings['content'] ?? '';
				$result = OsSmsHelper::send_sms($to, $content, ($notification_settings['activity_data'] ?? []));
				break;
		}

		 /**
	     * Send a notification based on type
		 *
	     * @since 4.7.0
	     * @hook latepoint_notifications_send
	     *
	     * @param {array} $result The array of data describing the send operation
	     * @param {string} $type Type of notificaiton to send
	     * @param {array} $notification_settings Settings for notification
	     *
	     * @returns {array} The array of descriptive data, possibly transformed by hooks
	     */
		 return apply_filters('latepoint_notifications_send', $result, $type, $notification_settings);
	}


	public static function load_templates_for_action_type($action_type){
		$templates = [];

		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( ! WP_Filesystem() ) {
			OsDebugHelper::log( __( 'Failed to initialise WC_Filesystem API while trying to show notification templates.', 'latepoint' ) );
			return $templates;
		}
		global $wp_filesystem;

		switch($action_type){
			case 'send_email':
				$templates[] = [
					'id' => 'booking__created__to_agent',
					'to_user_type' => 'agent',
					'name' => "New Appointment",
					'to_email' => '{{agent_full_name}} <{{agent_email}}>',
					'subject' => "New Appointment Received",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/booking_created.html'))
				];
				$templates[] = [
					'id' => 'booking__created__to_customer',
					'to_user_type' => 'customer',
					'name' => "New Appointment",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Appointment Confirmation",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/booking_created.html'))
				];
				$templates[] = [
					'id' => 'booking__updated__to_customer',
					'to_user_type' => 'customer',
					'name' => "Appointment Updated",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Appointment Updated",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/booking_updated.html'))
				];
				$templates[] = [
					'id' => 'booking__updated__to_agent',
					'to_user_type' => 'agent',
					'name' => "Appointment Updated",
					'to_email' => '{{agent_full_name}} <{{agent_email}}>',
					'subject' => "Appointment Updated",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/booking_updated.html'))
				];
				$templates[] = [
					'id' => 'customer__created__to_customer',
					'to_user_type' => 'customer',
					'name' => "Customer Created",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Your New Account",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/customer_created.html'))
				];
				$templates[] = [
					'id' => 'booking__start__to_customer',
					'to_user_type' => 'customer',
					'name' => "Appointment Reminder",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Appointment Reminder",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/booking_start.html'))
				];
				$templates[] = [
					'id' => 'booking__start__to_agent',
					'to_user_type' => 'agent',
					'name' => "Appointment Reminder",
					'to_email' => '{{agent_full_name}} <{{agent_email}}>',
					'subject' => "Appointment Reminder",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/booking_start.html'))
				];
				$templates[] = [
					'id' => 'booking__end__to_customer',
					'to_user_type' => 'customer',
					'name' => "After Appointment Feedback",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Appointment Feedback",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/booking_end.html'))
				];
				$templates[] = [
					'id' => 'booking__end__to_agent',
					'to_user_type' => 'agent',
					'name' => "After Appointment Feedback",
					'to_email' => '{{agent_full_name}} <{{agent_email}}>',
					'subject' => "Appointment Feedback",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/booking_end.html'))
				];
				$templates[] = [
					'id' => 'order__created__to_agent',
					'to_user_type' => 'agent',
					'name' => "New Order",
					'to_email' => '{{order_agents_emails}}',
					'subject' => "New Order Received",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/order_created.html'))
				];
				$templates[] = [
					'id' => 'order__created__to_customer',
					'to_user_type' => 'customer',
					'name' => "New Order",
					'to_email' => '{{customer_full_name}} <{{customer_email}}>',
					'subject' => "Order Confirmation",
					'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/customer/order_created.html'))
				];
				if(OsSettingsHelper::is_env_dev()){

					$templates[] = [
						'id' => 'booking__test__to_agent',
						'to_user_type' => 'agent',
						'name' => "Test Booking variables",
						'to_email' => '{{agent_full_name}} <{{agent_email}}>',
						'subject' => "Test Booking variables",
						'content' => OsEmailHelper::get_email_layout($wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/agent/booking_test.html'))
					];
				}
				break;
			case 'send_sms':
				$templates[] = [
					'id' => 'booking__created__to_agent',
					'to_user_type' => 'agent',
					'name' => "New Appointment",
					'to_phone' => '{{agent_phone}}',
					'content' => 'Hi {{agent_first_name}}, your {{service_name}} appointment with {{customer_full_name}} is coming on {{start_date}} at {{start_time}}'
				];
				$templates[] = [
					'id' => 'booking__updated__to_agent',
					'to_user_type' => 'agent',
					'name' => "Appointment Updated",
					'to_phone' => '{{agent_phone}}',
					'content' => 'Hi {{agent_first_name}}, appointment with ID {{booking_id}} was updated. Date/Time: {{start_date}} at {{start_time}}'
				];
				$templates[] = [
					'id' => 'booking__created__to_customer',
					'to_user_type' => 'customer',
					'name' => "New Appointment",
					'to_phone' => '{{customer_phone}}',
					'content' => 'Hi {{customer_first_name}}, your {{service_name}} appointment is coming on {{start_date}} at {{start_time}}'
				];
				$templates[] = [
					'id' => 'booking__updated__to_customer',
					'to_user_type' => 'customer',
					'name' => "Appointment Updated",
					'to_phone' => '{{customer_phone}}',
					'content' => 'Hi {{customer_first_name}}, your {{service_name}} appointment was updated. Date/Time: {{start_date}} at {{start_time}}'
				];
				$templates[] = [
					'id' => 'customer__created__to_customer',
					'to_user_type' => 'customer',
					'name' => "Customer Created",
					'to_phone' => '{{customer_phone}}',
					'content' => 'Thank you for creating an account. Visit {{customer_dashboard_url}} to manage your appointments.'
				];
				break;
		}
		return $templates;
	}


  // Password Reset Request
  public static function customer_password_reset_request_subject(){
    $customer_mailer = new OsCustomerMailer();
    return $customer_mailer->password_reset_request_subject();
  }

  public static function customer_password_reset_request_content(){
    $customer_mailer = new OsCustomerMailer();
    return $customer_mailer->password_reset_request_content();
  }


  
  
}