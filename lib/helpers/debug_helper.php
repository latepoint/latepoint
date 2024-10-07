<?php

class OsDebugHelper {
	public static function log_braintree_exception($e) {
		$body = $e->getJsonBody();
		$err = $body["error"];
		$return_array = [
			"status" => $e->getHttpStatus(),
			"type" => $err["type"],
			"code" => $err["code"],
			"param" => $err["param"],
			"message" => $err["message"],
		];
		$error_msg = wp_json_encode($return_array);
		error_log($error_msg);
	}

	public static function log_stripe_exception($e) {
		$body = $e->getJsonBody();
		$err = $body["error"];
		$return_array = [
			"status" => $e->getHttpStatus(),
			"type" => $err["type"],
			"code" => $err["code"],
			"param" => $err["param"],
			"message" => $err["message"],
		];
		$error_msg = wp_json_encode($return_array);
		error_log($error_msg);
	}

	public static function log_files($files){
		if (!OsSettingsHelper::is_env_dev()) return;

		if (is_array($files) || is_object($files)) {
			error_log('LatePoint Files: ' . print_r($files, true));
		} else {
			error_log('LatePoint Files: ' . $files);
		}
	}

	public static function log_route($route_name, $return_format){
		if (!OsSettingsHelper::is_env_dev()) return;

		error_log('LatePoint ROUTE: [' . $route_name. ']:'.$return_format);
	}

	public static function log_params($params){
		if (!OsSettingsHelper::is_env_dev()) return;

		if (is_array($params) || is_object($params)) {
			error_log('LatePoint Params: ' . print_r($params, true));
		} else {
			error_log('LatePoint Params: ' . $params);
		}
	}

	public static function log_query($query) {
		if (!OsSettingsHelper::is_env_dev() || defined('LATEPOINT_SKIP_SQL_LOG')) return;

		if (is_array($query) || is_object($query)) {
			error_log('LatePoint Query: ' . print_r($query, true));
		} else {
			error_log('LatePoint Query: ' . $query);
		}

	}

	public static function log($message, $error_code = 'generic_error', $extra_description = []) {
		if (is_array($message) || is_object($message)) $message = print_r($message, true);
		OsActivitiesHelper::create_activity(['code' => 'error', 'description' => wp_json_encode(['message' => $message, 'error_code' => $error_code, 'extra_description' => $extra_description])]);
	}
}