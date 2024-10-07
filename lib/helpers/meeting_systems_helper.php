<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsMeetingSystemsHelper {


	public static function is_external_meeting_system_enabled(string $external_meeting_system_code): bool {
		return OsSettingsHelper::is_on('enable_' . $external_meeting_system_code);
	}

	public static function get_list_of_external_meeting_systems($enabled_only = false) {
		$external_meeting_systems = [];
		/**
		 * Returns an array of external meeting systems
		 *
		 * @since 4.7.0
		 * @hook latepoint_list_of_external_meeting_systems
		 *
		 * @param {array} array of meeting systems
		 * @param {bool} filter to return only meeting systems that are enabled
		 *
		 * @returns {array} The array of external meeting systems
		 *
		 */
		return apply_filters('latepoint_list_of_external_meeting_systems', $external_meeting_systems, $enabled_only);
	}
}
