<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsMarketingSystemsHelper {


	public static function is_external_marketing_system_enabled(string $external_marketing_system_code): bool {
		return OsSettingsHelper::is_on('enable_' . $external_marketing_system_code);
	}

	public static function get_list_of_external_marketing_systems($enabled_only = false) {
		$external_marketing_systems = [];
		/**
		 * Returns an array of external marketing systems
		 *
		 * @since 4.7.0
		 * @hook latepoint_list_of_external_marketing_systems
		 *
		 * @param {array} array of marketing systems
		 * @param {bool} filter to return only marketing systems that are enabled
		 *
		 * @returns {array} The array of external marketing systems
		 *
		 */
		return apply_filters('latepoint_list_of_external_marketing_systems', $external_marketing_systems, $enabled_only);
	}
}
