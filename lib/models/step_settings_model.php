<?php

class OsStepSettingsModel extends OsModel{
  var $id,
      $name,
      $value,
      $step;


  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_STEP_SETTINGS;
    $this->nice_names = array();

    if($id){
      $this->load_by_id($id);
    }
  }


  public function get_step_value_by_name($step, $name){
    $query = $this->db->prepare('SELECT * FROM '.$this->table_name.' WHERE name = %s AND step = %s LIMIT 1', array($name, $step));
    $result_row = $this->db->get_row( $query, ARRAY_A);
    if($result_row){
    	return $result_row['value'];
    }else{
      return false;
    }
  }


  protected function allowed_params($role = 'admin'){
    $allowed_params = array('name',
                            'value',
                            'step');
    return $allowed_params;
  }


  protected function params_to_save($role = 'admin'){
    $params_to_save = array('name',
                            'value',
                            'step');
    return $params_to_save;
  }

  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence'),
      'value' => array('presence'),
      'step' => array('presence'),
    );
    return $validations;
  }
}