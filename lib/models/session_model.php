<?php

class OsSessionModel extends OsModel{
  public $id,
      $session_key,
      $session_value,
      $hash,
      $expiration;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_SESSIONS;
    $this->nice_names = array();

    if($id){
      $this->load_by_id($id);
    }
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'session_key', 
                            'session_value', 
                            'hash', 
                            'expiration', 
                          );
    return $allowed_params;
  }

  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'session_key', 
                            'session_value', 
                            'hash', 
                            'expiration',
                          );
    return $params_to_save;
  }


  protected function before_create(){
  }

  protected function set_defaults(){
  }

  protected function properties_to_validate(){
    $validations = array();
    return $validations;
  }
}