<?php

class OsLocationModel extends OsModel{
  public $id,
      $name,
      $full_address,
      $selection_image_id,
      $status,
      $category_id,
      $order_number,
	  $services_agents_table_name,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_LOCATIONS;
    $this->services_agents_table_name = LATEPOINT_TABLE_AGENTS_SERVICES;
    $this->nice_names = array(
                              'name' => __('Location Name', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

	public function generate_data_vars(): array {
		$location_category = empty($this->category_id) ? false : new OsLocationCategoryModel($this->category_id);
		return [
			'id' => $this->id,
			'name' => $this->name,
			'full_address' => $this->full_address,
			'category' => $location_category ? $location_category->get_data_vars() : [],
		];
	}

	public function filter_allowed_records(): OsModel{
		if(!OsRolesHelper::are_all_records_allowed('location')){
			$this->filter_where_conditions(['id' => OsRolesHelper::get_allowed_records('location')]);
		}
		return $this;
	}

	protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'name', 
                            'full_address', 
                            'status', 
                            'category_id', 
                            'order_number', 
                            'selection_image_id', 
                          );
    return $allowed_params;
  }

  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'name',
                            'status',
                            'category_id',
                            'order_number',
                            'full_address', 
                            'selection_image_id');
    return $params_to_save;
  }


  protected function before_create(){
  }

  protected function set_defaults(){
    if(empty($this->category_id)) $this->category_id = 0;
    if(empty($this->status)) $this->status = LATEPOINT_LOCATION_STATUS_ACTIVE;
  }
  
  public function save_custom_schedule($work_periods){
    foreach($work_periods as &$work_period){
      $work_period['location_id'] = $this->id;
    }
    unset($work_period);
    OsWorkPeriodsHelper::save_work_periods($work_periods);
  }

	public function get_google_maps_link($embed = false){
		$extra = $embed ? '&output=embed' : '';
		return 'https://google.com/maps?q='.urlencode($this->full_address).$extra;
	}

	public function get_google_maps_iframe($height = '240'){
		return '<iframe width="100%" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$this->get_google_maps_link(true).'"></iframe>';
	}

  public function delete_custom_schedule(){
    $work_periods_model = new OsWorkPeriodModel();
    $work_periods = $work_periods_model->where(array('location_id' => $this->id, 'agent_id' => 0, 'service_id' => 0, 'custom_date' => 'IS NULL'))->get_results_as_models();
    if(is_array($work_periods)){
      foreach($work_periods as $work_period){
        $work_period->delete();
      }
    }
  }

  public function count_number_of_connected_services($agent_id = false){
    if($this->is_new_record()) return 0;
    $args = ['location_id' => $this->id];
    if($agent_id) $args['agent_id'] = $agent_id;
    return OsConnectorHelper::count_connections($args, 'service_id');
  }

  public function get_connected_agents(){
    $connector = new OsConnectorModel();
    $agent_ids = $connector->select('agent_id')->where(['location_id' => $this->id])->group_by('agent_id')->get_results();
    $agents = [];
    if($agent_ids){
      foreach($agent_ids as $connector_row){
        $agents[] = new OsAgentModel($connector_row->agent_id);
      }
    }
    return $agents;
  }

  public function save_agents_and_services($agents){
    if(!$agents) return true;
    $connections_to_save = [];
    $connections_to_remove = [];
    foreach($agents as $agent_key => $services){
      $agent_id = str_replace('agent_', '', $agent_key);
      foreach($services as $service_key => $service){
        $service_id = str_replace('service_', '', $service_key);
        $connection = ['location_id' => $this->id, 'agent_id' => $agent_id, 'service_id' => $service_id];
        if($service['connected'] == 'yes'){
          $connections_to_save[] = $connection;
        }else{
          $connections_to_remove[] = $connection;
        }
      }
    }
    if(!empty($connections_to_save)){
      foreach($connections_to_save as $connection_to_save){
        OsConnectorHelper::save_connection($connection_to_save);
      }
    }
    if(!empty($connections_to_remove)){
      foreach($connections_to_remove as $connection_to_remove){
        OsConnectorHelper::remove_connection($connection_to_remove);
      }
    }
    return true;
  }

  public function delete($id = false){
    if(!$id && isset($this->id)){
      $id = $this->id;
    }
    if($id && $this->db->delete( $this->table_name, array('id' => $id), array( '%d' ))){
      $this->db->delete(LATEPOINT_TABLE_AGENTS_SERVICES, array('location_id' => $id), array( '%d' ) );
      $this->db->delete(LATEPOINT_TABLE_BOOKINGS, array('location_id' => $id), array( '%d' ) );
      $this->db->delete(LATEPOINT_TABLE_WORK_PERIODS, array('location_id' => $id), array( '%d' ) );
      return true;
    }else{
      return false;
    }
  }

  public function should_be_active(){
    return $this->where(['status' => LATEPOINT_LOCATION_STATUS_ACTIVE]);
  }

  public function is_active(){
    return ($this->status == LATEPOINT_LOCATION_STATUS_ACTIVE);
  }


  public function get_selection_image_url(){
    $default_location_image_url = LATEPOINT_IMAGES_URL . 'location-image.png';
    return OsImageHelper::get_image_url_by_id($this->selection_image_id, 'thumbnail', $default_location_image_url);
  }

  public function has_agent($agent_id){
    return OsConnectorHelper::has_connection(['location_id' => $this->id, 'agent_id' => $agent_id]);
  }

  public function has_agent_and_service($agent_id, $service_id){
    if($this->is_new_record()) return false;
    return OsConnectorHelper::has_connection(['location_id' => $this->id, 'agent_id' => $agent_id, 'service_id' => $service_id]);
  }

  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence', 'uniqueness'),
    );
    return $validations;
  }
}