<?php

class OsSmsHelper {



	/**
	 * @param $to
	 * @param $content
	 *
	 * @return array [
	 * 'status' => string,
	 * 'message' => string,
	 * 'to' => string,
	 * 'content' => string,
	 * 'processor_code' => string,
	 * 'processor_name' => string,
	 * 'processed_datetime' => string,
	 * 'extra_data' => array
	 * ]
	 */
	public static function send_sms($to, $content, $activity_data = []): array {
		$result = [
			'status' => LATEPOINT_STATUS_ERROR,
			'message' => __('No SMS processor is selected.', 'latepoint'),
			'to' => $to,
			'content' => $content,
			'processor_code' => '',
			'processor_name' => '',
			'processed_datetime' => '',
			'extra_data' => [
				'activity_data' => $activity_data
			],
			'errors' => []
		];

    if(OsSettingsHelper::is_sms_allowed() && OsNotificationsHelper::is_notification_type_enabled('sms')) {
	    /**
	     * Result of sending an SMS message to a recipient's phone number
	     *
	     * @param {array} $result The array of data describing the send operation
	     * @param {string} $to The recipient's phone number
	     * @param {string} $content The message to send to the recipient
	     *
	     * @since 4.7.0
	     * @hook latepoint_notifications_send_sms
	     * @returns {array} The array of descriptive data, possibly transformed by hooked SMS processor(s)
	     */
	    $result = apply_filters( 'latepoint_notifications_send_sms', $result, $to, $content );
    }else{
			$result['message'] = __('SMS notifications are disabled', 'latepoint');
			$result['errors'][] = __('SMS notifications are disabled', 'latepoint');
    }

		self::log_sms($result);

		return $result;
  }

	/**
	 * @param $enabled_only
	 *
	 * @return array [
	 *   'code' => [
	 *      'code' => string,
	 *      'label' => string,
	 *      'image_url' => string
	 *   ]
	 * ]
	 */
	public static function get_sms_processors( $enabled_only = false ) {
		$sms_processors = [];

		/**
		 * Get the list of SMS processors registered in the LatePoint ecosystem
		 *
		 * @since 1.2.3
		 * @hook latepoint_sms_processors
		 *
		 * @param {array} $sms_processors The list of SMS processors being filtered
		 * @param {bool} $enabled_only True when filtering only enabled SMS processors, false otherwise
		 * @returns {array} The filtered list of SMS processors
		 */
		return apply_filters('latepoint_sms_processors', $sms_processors, $enabled_only);
	}

	public static function is_sms_processor_enabled( string $sms_processor_code ): bool {
		return (OsNotificationsHelper::get_selected_processor_code_by_type('sms') == $sms_processor_code);
	}

	/**
	 * @param array $result
	 *
	 * @return OsActivityModel
	 */
	public static function log_sms( array $result ) {
		if ( empty( $result['processed_datetime'] ) ) {
			$result['processed_datetime'] = OsTimeHelper::now_datetime_in_db_format();
		}
		$data = [
			'code'        => 'sms_sent',
			'description' => wp_json_encode($result)
		];
		if(!empty($result['extra_data']['activity_data'])) $data = array_merge($data, $result['extra_data']['activity_data']);
		$activity = OsActivitiesHelper::create_activity( $data );
		return $activity;
	}
}