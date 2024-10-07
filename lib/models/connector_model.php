<?php

class OsConnectorModel extends OsModel{
  public $id,
      $agent_id,
      $service_id,
      $location_id,
      $is_custom_price = false,
      $is_custom_hours = false,
      $is_custom_duration = false,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_AGENTS_SERVICES;
    $this->nice_names = array();

    if($id){
      $this->load_by_id($id);
    }
  }



  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'agent_id',
                            'service_id',
                            'location_id',
                            'is_custom_price',
                            'is_custom_hours',
                            'is_custom_duration');
    return $params_to_save;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'agent_id',
                            'service_id',
                            'location_id',
                            'is_custom_price',
                            'is_custom_hours',
                            'is_custom_duration');
    return $allowed_params;
  }


  protected function properties_to_validate(){
    $validations = array(
      'agent_id' => array('presence'),
      'service_id' => array('presence'),
      'location_id' => array('presence'),
    );
    return $validations;
  }
}