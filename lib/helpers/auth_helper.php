<?php 

class OsAuthHelper {

	public static ?\LatePoint\Misc\User $current_user = null;
	public static $logged_in_customer_id = false;

	public static function set_current_user(){
    if(\OsWpUserHelper::is_user_logged_in()){
			// if wp user is logged in - load from it
			self::$current_user = \LatePoint\Misc\User::load_from_wp_user(\OsWpUserHelper::get_current_user());
    }else{
			self::$current_user = new \LatePoint\Misc\User();
    }
		$customer = self::get_logged_in_customer();
		if($customer) self::$current_user->customer = $customer;
	}

	public static function get_current_user(): \LatePoint\Misc\User{
		if(!self::$current_user) self::set_current_user();
		return self::$current_user;
	}

  public static function get_highest_current_user_id(){
    $user_id = false;
    switch(self::get_highest_current_user_type()){
      case LATEPOINT_USER_TYPE_ADMIN:
      case LATEPOINT_USER_TYPE_CUSTOM:
        $user_id = self::get_logged_in_wp_user_id();
      break;
      case LATEPOINT_USER_TYPE_AGENT:
        $user_id = self::get_logged_in_agent_id();
      break;
      case LATEPOINT_USER_TYPE_CUSTOMER:
        $user_id = self::get_logged_in_customer_id();
      break;
    }
    return $user_id;
  }

  public static function get_admin_or_agent_avatar_url(){
    $avatar_url = LATEPOINT_DEFAULT_AVATAR_URL;
    if(self::is_agent_logged_in()){
      $agent = self::get_logged_in_agent();
      $avatar_url = $agent->get_avatar_url();
    }elseif(self::get_logged_in_wp_user_id()){
      $wp_user = self::get_logged_in_wp_user();
      $avatar_url = get_avatar_url($wp_user->user_email);
    }
    return $avatar_url;
  }

  public static function get_highest_current_user_type(){
    // check if WP admin is logged in
    $user_type = false;
		if(self::get_current_user()->backend_user_type){
			// backend user, admin, agent or custom role
			return self::get_current_user()->backend_user_type;
		}elseif(self::get_current_user()->customer){
			// customer
			return LATEPOINT_USER_TYPE_CUSTOMER;
		}
    return $user_type;
  }



  public static function login_wp_user($user){
    clean_user_cache($user->ID);
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    update_user_caches($user);
  }


  public static function login_customer($email, $password){
    if(empty($email) || empty($password)) return false;
	$email = sanitize_email($email);
    if(self::wp_users_as_customers()){
      $wp_user = wp_signon(['user_login' => $email, 'user_password' => $password]);
      if(!is_wp_error($wp_user)){
        // successfully logged into wp user
        // check if latepoint customer exists in db for this wp user
        wp_set_current_user($wp_user->ID);
        $customer = OsCustomerHelper::get_customer_for_wp_user($wp_user);
        if($customer->id){
          return $customer;
        }else{
          OsDebugHelper::log('Can not login because can not create LatePoint Customer from WP User', 'customer_login_error', $customer->get_error_messages());
          return false;
        }
        return $customer;
      }else{
        return false;
      }
    }else{
      $customer = new OsCustomerModel();
      $customer = $customer->where(array('email' => $email))->set_limit(1)->get_results_as_models();
      if($customer && OsAuthHelper::verify_password($password, $customer->password)){
        OsAuthHelper::authorize_customer($customer->id);
        return $customer;
      }else{
        return false;
      }
    }
  }

  public static function wp_users_as_customers(){
    return OsSettingsHelper::is_on('wp_users_as_customers', false);
  }
  

  // CUSTOMERS 
  // ---------------

  public static function logout_customer(){
    if(self::wp_users_as_customers()){
      wp_logout();
    }else{
      OsSessionsHelper::destroy_customer_session_cookie();
    }
  }

  public static function authorize_customer($customer_id){
    $customer = new OsCustomerModel();
    $customer = $customer->where(['id' => $customer_id])->set_limit(1)->get_results_as_models();
		if(empty($customer)){
      OsDebugHelper::log('Tried to authorize customer with invalid ID', 'customer_authorization', ['customer_id' => $customer_id]);
			return false;
		}
    if(self::wp_users_as_customers()){

			if($customer->wordpress_user_id){
				$wp_user = get_user_by( 'id', $customer->wordpress_user_id );
				// check if WP User exists, if not - create new one and get ID, otherwise get ID from customer record, since its valid
				$wordpress_user_id = ($wp_user) ? $customer->wordpress_user_id : OsCustomerHelper::create_wp_user_for_customer($customer);
			}else{
				$wordpress_user_id = OsCustomerHelper::create_wp_user_for_customer($customer);
			}

			if($wordpress_user_id){
        $wp_user = get_user_by( 'id', $wordpress_user_id );
				if( $wp_user ) {
          self::login_wp_user($wp_user);
        }else{
	        OsDebugHelper::log('WordPress user ID for customer is not found or can not be created.', 'customer_create_error', ['customer_id' => $customer_id, 'wordpress_user_id' => $wordpress_user_id]);
				}
			}else{
        OsDebugHelper::log('WordPress user ID for customer is not found or can not be created.', 'customer_create_error', ['customer_id' => $customer_id]);
      }
    }else{
      OsSessionsHelper::start_or_use_session_for_customer($customer_id);
    }
  }

  public static function get_logged_in_customer_id(){
    if(self::wp_users_as_customers()){
      // using wp users as customers
      if(OsWpUserHelper::is_user_logged_in()){
        $wp_user = wp_get_current_user();
        // search connected latepoint customer
        $customer = OsCustomerHelper::get_customer_for_wp_user($wp_user);
        if($customer->id){
          return $customer->id;
        }else{
          OsDebugHelper::log('Can not create LatePoint Customer from WP User', 'customer_create_error', $customer->get_error_messages());
          return false;
        }
      }else{
        return false;
      }
    }else{
			if(self::$logged_in_customer_id) return self::$logged_in_customer_id;
			$customer_id = OsSessionsHelper::get_customer_id_from_session();
			// make sure customer with this ID exists in database
			$customer = new OsCustomerModel($customer_id);
			if(!$customer->is_new_record()){
				self::$logged_in_customer_id = $customer_id;
				return self::$logged_in_customer_id;
			}else{
				// customer not found, destroy this invalid customer ID in session cookie
				OsSessionsHelper::destroy_customer_session_cookie();
				return false;
			}
    }
  }

  public static function is_customer_logged_in(){
    return self::get_logged_in_customer_id();
  }

  public static function get_logged_in_customer(){
    $customer = false;
    if(self::is_customer_logged_in()){
      $customer = new OsCustomerModel(self::get_logged_in_customer_id());
			if($customer->is_new_record()) $customer = false;
    }
    return $customer;
  }


  // AGENTS
  // -------------

  public static function get_logged_in_agent_id(){
    $agent_id = false;
    if(self::is_agent_logged_in()){
			if(self::get_current_user()->agent && self::get_current_user()->agent->id) $agent_id = self::get_current_user()->agent->id;
    }
    return $agent_id;
  }

  public static function is_agent_logged_in(){
    return (self::get_current_user()->backend_user_type == LATEPOINT_USER_TYPE_AGENT);
  }

  public static function get_logged_in_agent(){
    $agent = false;
    if(self::is_agent_logged_in()){
      $agent = new OsAgentModel();
      $agent = $agent->where(['wp_user_id' => self::get_logged_in_wp_user_id()])->set_limit(1)->get_results_as_models();
    }
    return $agent;
  }


  public static function is_custom_backend_user_logged_in(){
    return (self::get_current_user()->backend_user_type == LATEPOINT_USER_TYPE_CUSTOM);
  }

  public static function is_admin_logged_in(){
    return (self::get_current_user()->backend_user_type == LATEPOINT_USER_TYPE_ADMIN);
  }

  public static function get_logged_in_admin_user(){
    $admin_user = false;
    if(self::is_admin_logged_in()){
      $admin_user = self::get_logged_in_wp_user();
    }
    return $admin_user;
  }

  public static function get_logged_in_admin_user_id(){
    $admin_id = false;
    if(self::is_admin_logged_in()){
      $admin_id = self::get_logged_in_wp_user_id();
    }
    return $admin_id;
  }

  public static function get_logged_in_custom_user_id(){
    $admin_id = false;
    if(self::is_custom_backend_user_logged_in()){
      $admin_id = self::get_logged_in_wp_user_id();
    }
    return $admin_id;
  }








  // WP USER
  public static function get_logged_in_wp_user_id(){
    return OsWpUserHelper::get_current_user_id();
  }

  public static function get_logged_in_wp_user(){
    return OsWpUserHelper::get_current_user();
  }
  

  // UTILS

  public static function hash_password($password){
    return password_hash($password, PASSWORD_DEFAULT);
  }

  public static function verify_password($password, $hash){
    return password_verify($password, $hash);
  }

}