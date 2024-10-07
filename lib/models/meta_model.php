<?php

class OsMetaModel extends OsModel{
  var $id,
      $meta_key,
      $meta_value,
      $object_id,
      $created_at,
      $updated_at;

  protected static $encrypted_settings = array();

  function __construct($object_id = false){
    $this->nice_names = array();
    $this->object_id = $object_id;
    parent::__construct();
  }

  public function delete_by_key($meta_key, $object_id){
    if(!$object_id) return false;
    $meta_to_delete = $this->where(array('meta_key' => $meta_key, 'object_id' => $object_id))->get_results_as_models();
    if($meta_to_delete){
      foreach($meta_to_delete as $meta_obj){
        $meta_obj->delete();
      }
    }
    return true;
  }

  public function save_by_key($meta_key, $meta_value, $object_id = false){
    if(!$object_id) $object_id = $this->object_id;
    if(!$object_id) return false;
    $existing_meta = $this->where(array('meta_key' => $meta_key, 'object_id' => $object_id))->set_limit(1)->get_results_as_models();
    if($existing_meta){
      $existing_meta->meta_value = self::prepare_value($meta_key, $meta_value);
      return $existing_meta->save();
    }else{
      $new_meta = $this;
      $new_meta->object_id = $object_id;
      $new_meta->meta_key = $meta_key;
      $new_meta->meta_value = self::prepare_value($meta_key, $meta_value);
      return $new_meta->save();
    }
  }

  private static function prepare_value($meta_key, $meta_value){
    if(in_array($meta_key, self::$encrypted_settings)){
      $meta_value = OsEncryptHelper::encrypt_value($meta_value);
    }
    return $meta_value;
  }

  public function get_by_key($meta_key, $object_id = false, $default = false){
    if(!$object_id) $object_id = $this->object_id;
    if(!$object_id) return $default;
    $record = $this->where(array('meta_key' => $meta_key, 'object_id' => $object_id))->set_limit(1)->get_results_as_models();
    if($record){
      if(in_array($meta_key, self::$encrypted_settings)){
        return OsEncryptHelper::decrypt_value($record->meta_value);
      }else{
        return $record->meta_value;
      }
    }else{
      return $default;
    }
  }

  public function get_by_object_id($object_id = false, $default = []){
    if(!$object_id) $object_id = $this->object_id;
    if(!$object_id) return $default;
    $records = $this->where(array('object_id' => $object_id))->get_results();
    if($records){
      $metas = [];
      foreach($records as $record){
        $value = in_array($record->meta_key, self::$encrypted_settings) ? OsEncryptHelper::decrypt_value($record->meta_value) : $record->meta_value;
        $metas[$record->meta_key] = $value;
      }
      return $metas;
    }else{
      return $default;
    }
  }

  public function get_object_id_by_value($meta_key, $meta_value){
    if(!$meta_value || !$meta_key) return false;
    $record = $this->select('object_id')->where(array('meta_key' => $meta_key, 'meta_value' => $meta_value))->set_limit(1)->get_results_as_models();
    if($record){
      return $record->object_id;
    }else{
      return false;
    }
  }


  protected function allowed_params($role = 'admin'){
    $allowed_params = array('object_id',
                            'meta_key',
                            'meta_value');
    return $allowed_params;
  }
  
  protected function params_to_save($role = 'admin'){
    $params_to_save = array('object_id',
                            'meta_key',
                            'meta_value');
    return $params_to_save;
  }

  protected function properties_to_validate(){
    $validations = array(
      'object_id' => array('presence'),
      'meta_key' => array('presence'),
      'meta_value' => array('presence'),
    );
    return $validations;
  }
}