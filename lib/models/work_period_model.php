<?php

class OsWorkPeriodModel extends OsModel{
  public $id,
      $service_id,
      $agent_id,
      $location_id,
      $start_time,
      $end_time,
      $week_day,
      $custom_date = null,
      $chain_id,
	  $services_agents_table_name,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_WORK_PERIODS;
    $this->services_agents_table_name = LATEPOINT_TABLE_AGENTS_SERVICES;
    $this->nice_names = array(
                              'start_time' => __('Start Time', 'latepoint'),
                              'end_time' => __('End Time', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

  protected function get_is_active(){
    return ($this->start_time != $this->end_time);
  }

  protected function get_nice_start_time(){
    return OsTimeHelper::minutes_to_hours_and_minutes($this->start_time);
  }

  protected function get_nice_end_time(){
    return OsTimeHelper::minutes_to_hours_and_minutes($this->end_time);
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'service_id', 
                            'agent_id', 
                            'location_id', 
                            'start_time', 
                            'end_time',
                            'chain_id',
                            'custom_date',
                            'week_day');
    return $allowed_params;
  }
  
  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'service_id', 
                            'agent_id', 
                            'location_id', 
                            'start_time', 
                            'end_time',
                            'chain_id',
                            'custom_date',
                            'week_day');
    return $params_to_save;
  }



  protected function properties_to_validate(){
    $validations = array(
      'week_day' => array('presence'),
    );
    return $validations;
  }
}