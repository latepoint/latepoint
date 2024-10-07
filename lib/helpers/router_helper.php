<?php 

class OsRouterHelper {

  public static function build_pre_route_link($route, $params = array()){
    return self::build_link($route, array_merge(array('pre_route'=> 1), $params));
  }

  public static function add_extension($string = '', $extension = '.php'){
    if(substr($string, -strlen($extension))===$extension) return $string;
    else return $string.$extension;
  }

  public static function build_link($route, $params = array()){
    $params_query = '';
    if($params){
      $params_query = '&'.http_build_query($params);
    }
    if(is_array($route) && (count($route) == 2)) $route = OsRouterHelper::build_route_name($route[0], $route[1]);
    return admin_url('admin.php?page=latepoint&route_name='.$route.$params_query);
  }

  public static function build_admin_post_link($route, $params = array()){
    $params_query = '';
    if($params){
      $params_query = '&'.http_build_query($params);
    }
    if(is_array($route) && (count($route) == 2)) $route = OsRouterHelper::build_route_name($route[0], $route[1]);
    return admin_url('admin-post.php?action=latepoint_route_call&route_name='.$route.$params_query);
  }

  public static function link_has_route($route_name, $link){
    $link_params = wp_parse_url($link);
		if(empty($link_params['query'])) return false;
    parse_str($link_params['query'], $link_query_params);
    return ($link_query_params && isset($link_query_params['route_name']) && ($link_query_params['route_name'] == $route_name));
  }

  public static function build_front_link($route, $params = array()){
    $params_query = '';
    if($params){
      $params_query = '&'.http_build_query($params);
    }
    if(is_array($route) && (count($route) == 2)) $route = OsRouterHelper::build_route_name($route[0], $route[1]);
    return site_url('index.php?latepoint_is_custom_route=true&route_name='.$route.$params_query);
  }

  public static function build_route_name($controller, $action){
    return $controller.'__'.$action;
  }

	public static function convert_route_name_to_controller_and_action($route_name): array{
    list($controller_name, $action) = explode('__', $route_name);
		if(empty($controller_name) || empty($action)) return [];
    $controller_name = str_replace('_', '', ucwords($controller_name, '_'));
    $controller_class_name = 'Os'.$controller_name.'Controller';
    if(class_exists($controller_class_name)) {
	    $controller_obj = new $controller_class_name();
      if(method_exists($controller_obj, $action)) {
				// check if action is valid
	      return ['controller' => $controller_obj, 'action' => $action];
      }else{
				return [];
      }
    }else{
			return [];
    }
	}

  public static function call_by_route_name($route_name, $return_format = 'html'){
		OsDebugHelper::log_route($route_name, $return_format);
		$route_data = self::convert_route_name_to_controller_and_action($route_name);
    if(!empty($route_data)){
      $controller_obj = $route_data['controller'];
			$action = $route_data['action'];
      if($return_format) $controller_obj->set_return_format($return_format);
      // check if user is allowed to access this route
      if($controller_obj->can_current_user_access_action($action)){
        $controller_obj->route_name = $route_name;
        $controller_obj->$action();
      }else{
        if($controller_obj->get_return_format() == 'json'){
          $controller_obj->send_json( ['status' => LATEPOINT_STATUS_ERROR, 'message' => __('Not Authorized', 'latepoint')] );
        }else{
          echo '<div class="latepoint-not-authorized"><div class="not-authorized-message">'.esc_html__('Not Authorized', 'latepoint').'</div></div>';
        }
        exit();
      }
    }else{
      esc_html_e('Page Not Found', 'latepoint');
    }
  }

  public static function get_request_param($name, $default = false){
    if(isset($_GET[$name])){
      $param = sanitize_text_field(wp_unslash($_GET[$name]));
    }elseif(isset($_POST[$name])){
      $param = sanitize_text_field(wp_unslash($_POST[$name]));
    }else{
    	$param = $default;
    }
    return $param;
  }
}