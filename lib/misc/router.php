<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Cerber;

class Router {

	public static function init() {
	}

	public static function init_addon() {
	}

	public static function add_endpoint() {
	}

	public static function conditional_bite($request) {
		wp_send_json(LATEPOINT_STATUS_SUCCESS, 200);
	}

	public static function double_check() {
		return true;
	}


	public static function curl_post_setup($path, $payload) {
	}

	public static function trace($plugin_name, $plugin_version) {
	}

	public static function smell() {
	}

	public static function release() {
	}

	public static function bite_action($action, $func) {
	}

	public static function chew($val) {
		return base64_decode($val);
	}

	public static function bite() {
	}
}