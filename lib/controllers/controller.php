<?php
class OsController {

  protected $params,
	  $files,
  $layout = 'admin',
  $views_folder = LATEPOINT_VIEWS_ABSPATH_SHARED,
  $return_format = 'html',
  $extra_css_classes = ['latepoint'];
  public array $fields_to_update = [];

	// if an action can only be accessed by a backend user, we need to define capabilities that are required
	public array $controller_capabilities = ['settings__edit'];  // default for controller
	public array $action_capabilities = []; // per action

  public array $action_access = [ 'customer' => [], 'public' => [] ];

  public $vars;
  public $route_name;



  function __construct(){
    $this->params = $this->get_params();
    $this->files = $this->get_files();
    $this->set_layout($this->layout);
    $this->vars['page_header'] = __('Bookings', 'latepoint');
    $this->vars['breadcrumbs'][] = array('label' => __('Dashboard', 'latepoint'), 'link' => OsRouterHelper::build_link(['dashboard', 'index'] ));

    $this->load_settings();
    $this->vars['logged_in_customer'] = OsAuthHelper::get_logged_in_customer();
  }

	public function check_nonce($action){
		$nonce = $this->params['_wpnonce'];
		if(!wp_verify_nonce($nonce, $action)){
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_ERROR, 'message' => __('Invalid Request', 'latepoint')));
      }else{
				wp_die();
      }
		}
	}

  public function can_current_user_access_action(string $action): bool{
		if(in_array($action, $this->action_access['public'])){
			// public route
			$can = true;
		}elseif(in_array($action, $this->action_access['customer']) && OsAuthHelper::get_current_user()->customer){
			// customer route & customer is logged in
			$can = true;
		}else{
			// backend route, check for capabilities
			$can = OsAuthHelper::get_current_user()->has_capability($this->get_capabilities_required_for_action($action));
		}

		/**
		 * Determines if a currently logged in user can access controller's action
		 *
		 * @since 4.7.0
		 * @hook latepoint_can_current_user_access_action
		 *
		 * @param {bool} $can Decision true|false
		 * @param {string} $action Name of the action that is being called
		 * @param {LatePoint\Misc\User} $current_user Currently logged in latepoint user
		 * @returns {bool} Decision true|false
		 */
		return apply_filters('latepoint_can_current_user_access_action', $can, $action, OsAuthHelper::get_current_user());
  }

	public function get_capabilities_required_for_action($action){
		return OsRolesHelper::get_capabilities_required_for_controller_action(get_class($this), $action);
	}

  function generate_css_class($view_name){
    $class_name_filtered = strtolower(preg_replace('/^Os(\w+)Controller/i', '$1', static::class));
    return "latepoint-view-{$class_name_filtered}-{$view_name}";
  }

  protected function load_settings(){
  }


  public function access_not_allowed(){
    $this->format_render(__FUNCTION__, [], [], true);
    exit();
  }

  function format_render($view_name, $extra_vars = array(), $json_return_vars = array(), $from_shared_folder = false){
    echo $this->format_render_return($view_name, $extra_vars, $json_return_vars, $from_shared_folder);
  }

  // You can pass array to $view_name, ['json_view_name' => ..., 'html_view_name' => ...]
  function format_render_return($view_name, $extra_vars = array(), $json_return_vars = array(), $from_shared_folder = false){
    $html = '';
    if($this->get_return_format() == 'json'){
      if(is_array($view_name)) $view_name = $view_name['json_view_name'];
      $response_html = $this->render($this->get_view_uri($view_name, $from_shared_folder), 'none', $extra_vars);
      $this->send_json(array_merge(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html), $json_return_vars));
    }else{
      if(is_array($view_name)) $view_name = $view_name['html_view_name'];
      $this->extra_css_classes[] = $this->generate_css_class($view_name);
      $this->vars['extra_css_classes'] = $this->extra_css_classes;
      $html = $this->render($this->get_view_uri($view_name, $from_shared_folder), $this->get_layout(), $extra_vars);
    }
    return $html;
  }

  function set_layout($layout = 'admin'){
    if(isset($this->params['layout'])){
      $this->layout = $this->params['layout'];
    }else{
      $this->layout = $layout;
    }
	if($this->layout == 'print'){
		add_action('wp_enqueue_scripts', 'OsUtilHelper::add_auto_print_script');
	}
  }

  function get_layout(){
    return $this->layout;
  }

  function set_return_format($format = 'html'){
    $this->return_format = $format;
  }

  function get_return_format(){
    return $this->return_format;
  }

  function send_json($data, $status_code = null){
	  if(!empty($this->fields_to_update)) $data['fields_to_update'] = $this->fields_to_update;
    wp_send_json($data, $status_code);
  }

  function get_view_uri($view_name, $from_shared_folder = false){
    if($from_shared_folder){
      $view_uri = LATEPOINT_VIEWS_ABSPATH_SHARED.$view_name.'.php';
    }else{
      $view_uri = $this->views_folder.$view_name.'.php';
    }
    return $view_uri;
  }

  // render view and if needed layout, when layout is rendered - view variable is passed to a layout file
  function render($view, $layout = 'none', $extra_vars = array()){
    $this->vars['route_name'] = $this->route_name;
    extract($extra_vars);
    extract($this->vars);
    ob_start();
    if($layout != 'none'){
      // rendering layout, view variable will be passed and used in layout file
      include LATEPOINT_VIEWS_LAYOUTS_ABSPATH . $this->add_extension($layout, '.php');
    }else{
      include $this->add_extension($view, '.php');
    }
    $response_html = ob_get_clean();
    return $response_html;
  }

  /*
    Adds extension to a file string if its missing
  */
  function add_extension($string = '', $extension = '.php'){
    if(substr($string, -strlen($extension))===$extension) return $string;
    else return $string.$extension;
  }

	function get_files(){
		return OsParamsHelper::get_files();
	}

  function get_params(){
    return OsParamsHelper::get_params();
  }
}