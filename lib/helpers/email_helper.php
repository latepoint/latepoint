<?php

class OsEmailHelper{

	public static function get_default_headers(){
		$headers = [];
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: '.get_bloginfo('name').' <'.get_bloginfo('admin_email').'>';

		/**
     * Default headers for sending email
		 *
     * @since 4.7.0
     * @hook latepoint_default_email_headers
     *
     * @param {array} $headers The array of headers for email
     *
     * @returns {array} The array of headers for email
     */
		return apply_filters('latepoint_default_email_headers', $headers);
	}

	public static function get_email_layout($insert_content = false): string{

		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( ! WP_Filesystem() ) {
			OsDebugHelper::log( __( 'Failed to initialise WC_Filesystem API while trying to show notification templates.', 'latepoint' ) );
			return '';
		}
		global $wp_filesystem;

		$html = OsSettingsHelper::get_settings_value('email_layout_template', $wp_filesystem->get_contents(LATEPOINT_VIEWS_ABSPATH.'mailers/layouts/default.html'));
		if($insert_content){
			$html = str_replace('{{content}}', $insert_content, $html);
		}
		return $html;
	}

	/**
	 * @param array $result
	 *
	 * @return OsActivityModel
	 */
	public static function log_email( array $result ) {
		if ( empty( $result['processed_datetime'] ) ) {
			$result['processed_datetime'] = OsTimeHelper::now_datetime_in_db_format();
		}
		$data = [
			'code'        => 'email_sent',
			'description' => wp_json_encode($result)
		];
		if(!empty($result['extra_data']['activity_data'])) $data = array_merge($data, $result['extra_data']['activity_data']);
		$activity = OsActivitiesHelper::create_activity( $data );
		return $activity;
	}


	/**
	 *
	 * Sends email using WordPress wp_mail function
	 *
	 * @param string $to Email address(es) of the receiver
	 * @param string $subject Subject of the email
	 * @param string $content Contents of the email
	 * @param array $headers
	 * @return array
	 */
  public static function send_email(string $to, string $subject, string $content, array $headers = [], $activity_data = []): array{
		$processor_code = 'wp_mail';
		$result = [
			'status' => LATEPOINT_STATUS_ERROR,
			'message' => __('No email processor is selected.', 'latepoint'),
			'to' => $to,
			'content' => $content,
			'processor_code' => $processor_code,
			'processor_name' => 'WordPress Mailer',
			'processed_datetime' => '',
			'extra_data' => [
				'activity_data' => $activity_data
			],
			'errors' => [],
		];

		if(empty($to)) $errors[] = __('Email address of the recipient can not be blank', 'latepoint');
		if(empty($subject)) $errors[] = __('Subject of the email can not be blank', 'latepoint');
		if(empty($content)) $errors[] = __('Content of the email can not be blank', 'latepoint');

		if(!empty($errors)){
			$result['status'] = LATEPOINT_STATUS_ERROR;
			$result['message'] = implode(', ', $errors);
			$result['errors'] = $errors;
			return $result;
		}

    if(OsSettingsHelper::is_email_allowed() && OsNotificationsHelper::is_notification_type_enabled('email')) {

			if(OsNotificationsHelper::get_selected_processor_code_by_type('email') == $processor_code){
				if(wp_mail($to, $subject, $content, $headers)){
					$result['status'] = LATEPOINT_STATUS_SUCCESS;
					$result['message'] = __('Email was sent successfully', 'latepoint');
					$result['processed_datetime'] = OsTimeHelper::now_datetime_in_db_format();
					$result['extra_data']['subject'] = $subject;
				}else{
					$result['status'] = LATEPOINT_STATUS_ERROR;
					$result['errors'] = __('Error sending email', 'latepoint');
				}
			}

			/**
	     * Result of sending an email
			 *
	     * @since 4.7.0
	     * @hook latepoint_notifications_send_email
	     *
	     * @param {array} $result The array of data describing the result of operation
	     * @param {string} $to
	     * @param {string} $subject
	     * @param {string} $content
	     * @param {array} $headers
	     *
	     * @returns {array} The array of data describing the result of operation
	     */
	    $result = apply_filters('latepoint_notifications_send_email', $result, $to, $subject, $content, $headers);

    }else{
			$result['message'] = __('Email notifications are disabled. Enable email processor in Settings - Notifications.', 'latepoint');
			$result['errors'][] = __('Email notifications are disabled. Enable email processor in Settings - Notifications.', 'latepoint');
    }
		OsEmailHelper::log_email($result);
		return $result;
  }
}