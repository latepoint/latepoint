<?php

class OsLocationCategoryModel extends OsModel{
  public $id,
      $name,
      $parent_id,
      $selection_image_id,
      $order_number,
      $short_description;

  function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_LOCATION_CATEGORIES;
    $this->nice_names = array(
                              'name' => __('Location Category Name', 'latepoint'),
                              'short_description' => __('Location Category Short Description', 'latepoint'),
                              'selection_image_id' => __('Location Category Selection Image', 'latepoint'));

    if($id){
      $this->load_by_id($id);
    }
  }

	public function generate_data_vars(): array {
		return [
			'id' => $this->id,
			'name' => $this->name
			];
	}

	public function count_locations(){
    $location = new OsLocationModel();
    $total_location = $location->filter_allowed_records()->where(['category_id' => $this->id])->should_be_active()->count();
    $child_categories = new OsLocationCategoryModel();
    $child_categories = $child_categories->where(['parent_id' => $this->id])->get_results_as_models();
    if($child_categories){
      foreach($child_categories as $child_category){
        $total_location = $total_location + $child_category->count_locations();
      }
    }
    return $total_location;
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
    $default_location_image_url = LATEPOINT_IMAGES_URL . 'location-image.png';
    return OsImageHelper::get_image_url_by_id($this->selection_image_id, 'thumbnail', $default_location_image_url);
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

    $categories_for_select[] = array('value' => 0, 'label' => __('Not categorized', 'latepoint'));
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

  public function get_active_locations($filter_allowed_records = false){
    if(!isset($this->active_locations)){
      $location = new OsLocationModel();
			if($filter_allowed_records) $location->filter_allowed_records();
      $location->should_be_active()->where(array('category_id'=> $this->id))->order_by('order_number asc');
      $this->active_locations = $location->get_results_as_models();
    }
    return $this->active_locations;
  }

  protected function get_locations($filter_allowed_records = false){
    if(!isset($this->locations)){
      $location = new OsLocationModel();
			if($filter_allowed_records) $location->filter_allowed_records();
      $this->locations = $location->where(array('category_id'=> $this->id))->order_by('order_number asc')->get_results_as_models();
    }
    return $this->locations;
  }

}