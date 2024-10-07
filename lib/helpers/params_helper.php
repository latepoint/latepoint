<?php

class OsParamsHelper {

	private static $params = [];
	private static $files = [];

	public static function load_params() {
		$params      = array();
		$post_params = array();
		$get_params  = array();
		if ( ! empty( $_POST['params'] ) ) {
			if ( is_string( $_POST['params'] ) ) {
				parse_str( $_POST['params'], $post_params );
			}
			if ( is_array( $_POST['params'] ) ) {
				$post_params = array_merge( $_POST['params'], $post_params );
			}
		}
		$get_params   = $_GET;
		$params       = array_merge( $post_params, $get_params );
		$params       = stripslashes_deep( $params );
		self::$params = $params;
	}

	public static function load_files() {
		if ( ! empty( $_FILES ) ) {
			self::$files = $_FILES;
		} else {
			self::$files = [];
		}
	}


	public static function get_params() {
		self::ensure_params_loaded();
		OsDebugHelper::log_params( self::$params );

		return self::$params;
	}

	public static function get_param( $param_name ) {
		self::ensure_params_loaded();
		return self::$params[ $param_name ] ?? null;
	}

	public static function ensure_params_loaded() {
		if ( empty( self::$params ) ) {
			self::load_params();
		}
	}

	public static function get_files() {
		self::ensure_files_loaded();
		OsDebugHelper::log_files( self::$files );

		return self::$files;
	}

	public static function get_file( $file_name ) {
		self::ensure_files_loaded();

		return self::$files[ $file_name ] ?? null;
	}

	public static function ensure_files_loaded() {
		if ( empty( self::$files ) ) {
			self::load_files();
		}
	}

	public static function sanitize_param( $value, $rule ) {
		switch ( $rule ) {
			case 'money':
				$value = OsMoneyHelper::convert_amount_from_money_input_to_db_format( $value );
				break;
			case 'percent':
				$value = OsMoneyHelper::convert_value_from_percent_input_to_db_format( $value );
				break;
			case 'date':
				break;
		}

		return $value;
	}

	public static function permit_params( array $params, array $allowed_keys ): array {
		$allowed_keys_arr = array_flip( $allowed_keys );

		return array_intersect_key( $params, $allowed_keys_arr );
	}
}