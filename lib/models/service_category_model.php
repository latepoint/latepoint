<?php

class OsServiceCategoryModel extends OsModel{
  public $id,
      $name,
      $parent_id,
      $selection_image_id,
      $order_number,
      $short_description,
      $updated_at,
      $created_at;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_SERVICE_CATEGORIES;
    $this->nice_names = array(
                              'name' => __('Service Category Name', 'latepoint'),
                              'short_description' => __('Service Category Short Description', 'latepoint'),
                              'selection_image_id' => __('Service Category Selection Image', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

  public function count_services(){
    $services = new OsServiceModel();
    $total_services = $services->where(['category_id' => $this->id])->should_be_active()->count();
    $child_categories = new OsServiceCategoryModel();
    $child_categories = $child_categories->where(['parent_id' => $this->id])->get_results_as_models();
    if($child_categories){
      foreach($child_categories as $child_category){
        $total_services = $total_services + $child_category->count_services();
      }
    }
    return $total_services;
  }

  public function delete($id = false){
    if(!$id && isset($this->id)){
      $id = $this->id;
    }
    if($id && $this->db->delete( $this->table_name, array('id' => $id), array( '%d' ))){
      $this->db->update(LATEPOINT_TABLE_SERVICES, array('category_id' => 0), array( 'category_id' => $id ), array( '%d' ) );
      $this->db->update($this->table_name, array('parent_id' => NULL), array( 'parent_id' => $id ), array( '%d' ) );
      return true;
    }else{
      return false;
    }
  }

  public function get_selection_image_url(){
    $default_service_image_url = LATEPOINT_IMAGES_URL . 'service-image.png';
    return OsImageHelper::get_image_url_by_id($this->selection_image_id, 'thumbnail', $default_service_image_url);
  }

  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence', 'uniqueness'),
    );
    return $validations;
  }

  public function index_for_select(){
    $categories = $this->db->get_results("SELECT id, name FROM ".$this->table_name);
    $categories_for_select = array();

    $categories_for_select[] = array('value' => 0, 'label' => __('Uncategorized', 'latepoint'));
    foreach($categories as $category){
      $categories_for_select[] = array('value' => $category->id, 'label' => $category->name);
    }

    return $categories_for_select;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id', 
                            'name', 
                            'short_description', 
                            'selection_image_id',
                            'parent_id',
                            'order_number');
    return $allowed_params;
  }


  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id', 
                            'name', 
                            'short_description', 
                            'selection_image_id',
                            'parent_id',
                            'order_number');
    return $params_to_save;
  }

  public function get_active_services($show_hidden = false){
    if(!isset($this->active_services)){
      $services = new OsServiceModel();
      $services->should_be_active()->where(array('category_id'=> $this->id))->order_by('order_number asc');
      if(!$show_hidden) $services->should_not_be_hidden();
      $this->active_services = $services->get_results_as_models();
    }
    return $this->active_services;
  }

  protected function get_services(){
    if(!isset($this->services)){
      $services = new OsServiceModel();
      $this->services = $services->where(array('category_id'=> $this->id))->order_by('order_number asc')->get_results_as_models();
    }
    return $this->services;
  }

}