<?php 

class OsLicenseHelper {

  public static function get_license_key(){
    $license_info = self::get_license_info();
    return $license_info['license_key'];
  }

  public static function clear_license(){
    OsSettingsHelper::save_setting_by_name('is_active_license', 'no');
    OsSettingsHelper::save_setting_by_name('license_status_message', '');
    OsSettingsHelper::save_setting_by_name('license', '');
  }

  public static function get_license_info(){
    $license_info = OsSettingsHelper::get_settings_value('license');
    $license = array('full_name' => '', 'email' => '', 'license_key' => '');

    if($license_info){
      $license_arr = explode('*|||*', $license_info);
      $license['full_name'] = isset($license_arr[0]) ? $license_arr[0] : '';
      $license['email'] = isset($license_arr[1]) ? $license_arr[1] : '';
      $license['license_key'] = isset($license_arr[2]) ? $license_arr[2] : '';
    }

    $license['is_active'] = OsSettingsHelper::get_settings_value('is_active_license', 'no');
    $license['status_message'] = OsSettingsHelper::get_settings_value('license_status_message', false);

    return $license;
  }

  public static function is_license_active(){
  	return (OsSettingsHelper::get_settings_value('is_active_license', 'no') == 'yes');
  }

  public static function verify_license_key($license_data){

    $license_key = trim(strtolower($license_data['license_key']));
    $license_owner_name = $license_data['full_name'];
    $license_owner_email = $license_data['email'];

    if(empty($license_data['license_key'])) return ['status' => LATEPOINT_STATUS_ERROR, 'message' => __('Please enter your license key', 'latepoint')];

    $glued_license = implode('*|||*', array($license_owner_name, $license_owner_email, $license_key));

    OsSettingsHelper::save_setting_by_name('license', $glued_license);

    $is_valid_license = false;
    // connect
    $post = array(
      '_nonce'        => wp_create_nonce('activate_licence'),
      'license_key'   => $license_key, 
      'domain'        => OsUtilHelper::get_site_url(),
      'user_ip'       => OsUtilHelper::get_user_ip(),
      'data'          => $glued_license
    );

    $url = OsSettingsHelper::get_remote_url("/wp/activate-license");

   
    $request = wp_remote_post( $url,array('body' => $post, 'sslverify' => false));
    
    if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200){ 
      $response = json_decode($request['body'], true);
      if(empty($response['status'])){
        $message = __('Connection Error. Please try again in a few minutes or contact us at license@latepoint.com. Error Code: UDF732S83', 'latepoint');
      }else{
        $message = $response['message'];
        if( $response['status'] == 200){
          $is_valid_license = true;
        }
      }
    }else{
			if (is_wp_error($request)) OsDebugHelper::log('Update plugin error', 'update_plugin_error', ['error' => $request->get_error_messages()]);
      $message = __('Connection Error. Please try again in a few minutes or contact us at license@latepoint.com. Error Code: SUYF8362', 'latepoint');
    }

    if($is_valid_license){
      $status = LATEPOINT_STATUS_SUCCESS;
      OsSettingsHelper::save_setting_by_name('is_active_license', 'yes');
      OsSettingsHelper::save_setting_by_name('license_status_message', $message);
    }else{
      $status = LATEPOINT_STATUS_ERROR;
      OsSettingsHelper::save_setting_by_name('is_active_license', 'no');
      OsSettingsHelper::save_setting_by_name('license_status_message', $message);
    }

    return ['status' => $status, 'message' => $message];
  }

}