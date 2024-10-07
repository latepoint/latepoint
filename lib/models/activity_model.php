<?php

class OsActivityModel extends OsModel{
  public $id,
      $agent_id,
      $order_id,
      $order_item_id,
      $booking_id,
      $service_id,
      $customer_id,
      $code,
      $description,
      $initiated_by,
      $initiated_by_id,
      $updated_at,
      $created_at;
      
      
      
      
      
  protected $codes;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_ACTIVITIES;
    $this->nice_names = array();

    $this->codes = $this->get_codes();

    if($id){
      $this->load_by_id($id);
    }
  }

	protected function get_codes(){
		return OsActivitiesHelper::get_codes();
	}

  public function get_link_to_object($label = false){
		$label = ($label) ? $label : esc_html__('View', 'latepoint');
    $href = '#';
		$attrs = '';
    switch($this->code){
      case 'customer_created':
      case 'customer_updated':
        $attrs = OsCustomerHelper::quick_customer_btn_html($this->customer_id);
      break;
      case 'agent_updated':
      case 'agent_created':
        $href = OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $this->agent_id) );
      break;
      case 'service_updated':
      case 'service_created':
        $href = OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'edit_form'), array('id' => $this->service_id) );
      break;
	    default:
				$attrs = 'data-os-params="' . esc_attr(http_build_query(['id' => $this->id])) . '" 
							    data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'activities', 'view' )) . '" 
							    data-os-lightbox-classes="width-800"
							    data-os-after-call="latepoint_init_json_view"
							    data-os-output-target="side-panel"';
			break;
    }
		$link = '<a class="view-activity-link" href="'.esc_url($href).'" '.$attrs.'>'.$label.'</a>';
		$link = apply_filters('latepoint_activity_link_to_object', $link, $this, $label);
		return $link;
  }



  protected function get_user_link_with_avatar(){
    $link = '#';
    $name = 'n/a';
		$attrs = '';
    $avatar_url = LATEPOINT_DEFAULT_AVATAR_URL;
    switch($this->initiated_by){
      case 'wp_user':
      case 'admin':
      case 'custom':
        $link = get_edit_user_link($this->initiated_by_id);
        $userdata = get_userdata($this->initiated_by_id);
        $name = $userdata->display_name;
        $avatar_url = get_avatar_url($this->initiated_by_id, array('size' => 200));
      break;
      case 'agent':
        $agent = new OsAgentModel($this->initiated_by_id);
        $link = OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $this->initiated_by_id) );
        $name = $agent->full_name;
        $avatar_url = $agent->get_avatar_url();
      break;
      case 'customer':
        $customer = new OsCustomerModel($this->initiated_by_id);
        $attrs = $attrs = OsCustomerHelper::quick_customer_btn_html($this->initiated_by_id);
        $name = $customer->full_name;
        $avatar_url = $customer->get_avatar_url();
      break;
	    default:
				$link = '#';
				$name = 'n/a';
				$avatar_url = LATEPOINT_IMAGES_URL . 'default-avatar.jpg';
				break;
    }
		$avatar_url = esc_url($avatar_url);
		$name = esc_html($name);
		$link = esc_url($link);
    return "<a class='user-link-with-avatar' target='_blank' href='{$link}' {$attrs}><span class='ula-avatar' style='background-image: url({$avatar_url})'></span><span class='ula-name'>{$name}</span><span class='latepoint-icon latepoint-icon-external-link'></span></a>";
  }

  public function get_description() {
	  if ($this->code == 'sms_sent') {
		  $this->description = json_decode($this->description, true);
	  }

	  return $this->description;
  }


  protected function get_nice_created_at(){
    $time = strtotime($this->created_at);
    return gmdate("m/d/y g:i:s A", $time);
  }


  protected function get_name(){
    if($this->code && isset($this->codes[$this->code])){
      return $this->codes[$this->code];
    }else{
      return $this->code;
    }
  }

  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'agent_id',
      'order_id',
      'order_item_id',
                            'booking_id',
                            'service_id',
                            'customer_id',
                            'code',
                            'description',
                            'initiated_by',
                            'initiated_by_id');
    return $params_to_save;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'agent_id',
                            'booking_id',
      'order_id',
      'order_item_id',
                            'service_id',
                            'customer_id',
                            'code',
                            'description',
                            'initiated_by',
                            'initiated_by_id');
    return $allowed_params;
  }


  protected function properties_to_validate(){
    $validations = array(
      'code' => array('presence')
    );
    return $validations;
  }
}