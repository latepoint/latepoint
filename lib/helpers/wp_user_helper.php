<?php 

class OsWpUserHelper {

  public static function is_user_logged_in(){
	  if ( function_exists( 'is_user_logged_in' ) ) {
	    return is_user_logged_in();
	  }else{
		  return false;
	  }
  }

  public static function get_current_user(){
    $user = false;
    if(self::is_user_logged_in()) $user = wp_get_current_user();
    return $user;
  }

  public static function get_current_user_id(){
    return get_current_user_id();
  }

  public static function get_wp_users_for_select($args = array()){
    $default_args = array( 'fields' => array( 'display_name', 'ID' ), 'orderby' => 'nicename');
    $args = array_merge($default_args, $args);
    $wp_users = get_users($args);
    $wp_users_for_select = array();

    foreach($wp_users as $wp_user){
      $wp_users_for_select[] = array('value' => $wp_user->ID, 'label' => $wp_user->display_name);
    }

    return $wp_users_for_select;
  }
}