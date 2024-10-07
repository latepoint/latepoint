<?php 

class OsSessionsHelper {

	private static $logged_in_customer_id = false;

	public static function setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
		if ( ! headers_sent() ) {
			setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, $httponly );
		} elseif ( class_exists('Constants') && Constants::is_true( 'WP_DEBUG' ) ) {
			headers_sent( $file, $line );
			trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}
	}

	public static function get_customer_session_cookie(){
		if(isset($_COOKIE[LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE])){
			return sanitize_text_field( wp_unslash($_COOKIE[LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE]));
		}else{
			return false;
		}
	}

	public static function get_customer_token($customer_id){
		return 'latepoint';
	}

	public static function set_customer_session_cookie( $session, $expiration, $token ) {
		$to_hash           = $session->id . '|' . $session->hash . '|' . $expiration . '|' . $token;
		// If ext/hash is not present, compat.php's hash_hmac() does not support sha256.
		$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
		$cookie_hash       = hash_hmac( $algo, $to_hash, wp_hash( $to_hash ) );
		$cookie_value      = $session->id . '||' . $expiration . '||' . $cookie_hash;

		if ( ! isset( $_COOKIE[ LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE ] ) || $_COOKIE[ LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE ] !== $cookie_value ) {
			self::setcookie( LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE, $cookie_value);
		}
	}

	public static function get_customer_id_from_session(){
		if(self::$logged_in_customer_id) return self::$logged_in_customer_id;
		$cookie = self::get_customer_session_cookie();
		if(!$cookie) return false;
		list($session_id, $expiration, $cookie_hash) = explode('||', $cookie);
		if(!isset($session_id) || !is_numeric($session_id) || !isset($expiration) || !isset($cookie_hash)) return false;
		$session = new OsSessionModel($session_id);
		if(!$session) return false;

		$token = self::get_customer_token($session->session_key);

		$to_hash = $session->id . '|' . $session->hash . '|' . $expiration . '|' . $token;

		$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
		$control_hash = hash_hmac( $algo, $to_hash, wp_hash( $to_hash ) );
		// check if the cookie was altered by malicious user
		if($control_hash != $cookie_hash){
			OsAuthHelper::logout_customer();
			self::destroy_customer_session_cookie();
			return false;
		}else{
			self::$logged_in_customer_id = $session->session_key;
			return self::$logged_in_customer_id;
		}
	}

	public static function start_or_use_session_for_customer($customer_id){
		// find existing session for the customer
		$session_model = new OsSessionModel();
		$session = $session_model->where(['session_key' => $customer_id])->set_limit(1)->get_results_as_models();
		$token = self::get_customer_token($customer_id);
		if($session){
			// expired session, renew
			if($session->expiration < time()){
				$session->expiration = time() + 2 * DAY_IN_SECONDS;
				$session->save();
			}
		}else{
			$session = new OsSessionModel();
			$session->session_key = $customer_id;
			$session->expiration = time() + 2 * DAY_IN_SECONDS;
			$session->session_value = maybe_serialize([]);
			$session->hash = wp_generate_password(20, false, false);
			$session->save();
		}
		self::set_customer_session_cookie($session, $session->expiration, $token);
		self::$logged_in_customer_id = $customer_id;
	}

	public static function destroy_customer_session_cookie() {
		self::$logged_in_customer_id = false;
		if (isset($_COOKIE[LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE])) {
	    unset($_COOKIE[LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE]);
	    setcookie(LATEPOINT_CUSTOMER_LOGGED_IN_COOKIE, '', time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
		}
	}


}