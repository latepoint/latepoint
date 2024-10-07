<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsBundleModel extends OsModel {
	var $services;

	var $id,
		$name,
		$short_description,
		$charge_amount,
		$deposit_amount,
		$status,
		$visibility,
		$order_number,
		$updated_at,
		$created_at;

	function __construct($id = false) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_BUNDLES;
    $this->join_table_name_bundles_services = LATEPOINT_TABLE_JOIN_BUNDLES_SERVICES;

		if ($id) {
			$this->load_by_id($id);
		}
	}

	public function generate_data_vars(): array {
		$vars = [
			'id'   => $this->id,
			'name' => $this->name
		];

		return $vars;
	}

	function has_service($service_id): bool{
		$services = $this->get_services();
		foreach($services as $service){
			if($service->id == $service_id) return true;
		}
		return false;
	}

	function quantity_for_service($service_id): int{
		$services = $this->get_services();
		foreach($services as $service){
			if($service->id == $service_id) return (!empty($service->join_attributes['quantity']) ? $service->join_attributes['quantity'] : 0);
		}
		return 0;
	}

	function duration_for_service($service_id): int{
		$services = $this->get_services();
		foreach($services as $service){
			if($service->id == $service_id) return (!empty($service->join_attributes['duration']) ? $service->join_attributes['duration'] : $service->duration);
		}
		return 0;
	}

	function total_attendees_for_service($service_id): int{
		$services = $this->get_services();
		foreach($services as $service){
			if($service->id == $service_id) return (!empty($service->join_attributes['total_attendees']) ? $service->join_attributes['total_attendees'] : 1);
		}
		return 0;
	}

	public function generate_params_for_booking_form(){
		$params = [
		  "bundle_id" => $this->id
		];

    /**
    * Returns an array of params generated from OsBundleModel to be used in a booking form
    *
    * @since 5.0.0
    * @hook latepoint_generated_bundle_params_for_booking_form
    *
    * @param {array} $params Array of booking params
    * @param {OsBundleModel} $bundle Instance of <code>OsBundleModel</code> that params are being generated for
    *
    * @returns {array} Filtered array of booking params
	*/
		return apply_filters('latepoint_generated_bundle_params_for_booking_form', $params, $this);
	}


	/**
	 * @return mixed|void
	 *
	 * Returns full amount to charge in database format 1999.0000
	 *
	 */
  public function full_amount_to_charge(){
    return OsBundlesHelper::calculate_full_amount_for_bundle($this);
  }

	/**
	 * @return mixed|void
	 *
	 * Returns deposit amount to charge in database format 1999.0000
	 *
	 */
  public function deposit_amount_to_charge(){
    return OsBundlesHelper::calculate_deposit_amount_for_bundle($this);
  }


	public function save_services($services){
    if(!$services) return true;
    $connections_to_save = [];
    $connections_to_remove = [];
    foreach($services as $service_key => $service){
      $service_id = str_replace('service_', '', $service_key);
      $connection = [
        'bundle_id' => $this->id,
        'service_id' => $service_id,
        'quantity' => $service['quantity'],
        'total_attendees' => $service['total_attendees'],
        'duration' => $service['duration'],
        ];
      if($service['connected'] == 'yes'){
        $connections_to_save[] = $connection;
      }else{
        $connections_to_remove[] = $connection;
      }
    }
    if(!empty($connections_to_save)){
      foreach($connections_to_save as $connection_to_save){
				$join_bundle_service =  new OsJoinBundlesServicesModel();
				$existing = $join_bundle_service->where(['bundle_id' => $connection_to_save['bundle_id'], 'service_id' => $connection_to_save['service_id']])->set_limit(1)->get_results_as_models();
				if($existing){
					$existing->quantity = $connection_to_save['quantity'];
					$existing->total_attendees = $connection_to_save['total_attendees'];
					$existing->duration = $connection_to_save['duration'];
					$existing->save();
				}else{
					$join_bundle_service->set_data($connection_to_save);
					$join_bundle_service->save();
				}
      }
    }
    if(!empty($connections_to_remove)){
      foreach($connections_to_remove as $connection_to_remove){
				$join_bundle_service =  new OsJoinBundlesServicesModel();
				$join_bundle_service->delete_where(['bundle_id' => $connection_to_remove['bundle_id'], 'service_id' => $connection_to_remove['service_id']]);
      }
    }
    return true;
	}


	public function get_formatted_charge_amount(){
    if($this->charge_amount > 0){
      return OsMoneyHelper::format_price($this->charge_amount);
    }else{
      return 0;
    }
  }

	public function get_service_and_quantity_descriptions(): array{
		$bundle_services = $this->get_services();
		$bundle_services_descriptions = [];
		foreach ($bundle_services as $service) {
			$qty = $service->join_attributes['quantity'];
			$qty_html = $qty > 1 ? ' [' . $qty . ']' : '';
			$bundle_services_descriptions[] = $service->name . $qty_html;
		}
		return $bundle_services_descriptions;
	}


	public function get_services($order_item_id = false) : array{
		if (!isset($this->services)) {
			$bundle_services = new OsJoinBundlesServicesModel();
			$bundle_services = $bundle_services->get_services_for_bundle_id($this->id);

			$this->services = [];

			if ($bundle_services) {
				foreach ($bundle_services as $bundle_service) {
					$service = new OsServiceModel($bundle_service->service_id);
					$service->join_attributes['quantity'] = $bundle_service->quantity;
					$service->join_attributes['total_attendees'] = $bundle_service->total_attendees;
					$service->join_attributes['duration'] = $bundle_service->duration;
					if($order_item_id){
						$bookings = new OsBookingModel();
						$service->join_attributes['total_scheduled_bookings'] = $bookings->where(['order_item_id' => $order_item_id, 'service_id' => $service->id])->should_not_be_cancelled()->count();
					}
					$this->services[] = $service;
				}
			}
		}
		return $this->services;
	}


  public function is_hidden(){
    return ($this->visibility == LATEPOINT_BUNDLE_VISIBILITY_HIDDEN);
  }

  public function should_be_active(){
    return $this->where(['status' => LATEPOINT_BUNDLE_STATUS_ACTIVE]);
  }

  public function should_not_be_hidden(){
    return $this->where(['visibility !=' => LATEPOINT_BUNDLE_VISIBILITY_HIDDEN]);
  }

  public function is_active(){
    return ($this->status == LATEPOINT_BUNDLE_STATUS_ACTIVE);
  }


  protected function properties_to_validate(){
    $validations = array(
      'name' => array('presence'),
    );
    return $validations;
  }


	protected function params_to_sanitize(){
		return ['charge_amount' => 'money',
						'deposit_amount' => 'money'
			];
	}

  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id',
		'name',
		'short_description',
		'charge_amount',
		'deposit_amount',
		'status',
		'visibility',
		'order_number',
		'updated_at',
		'created_at');
    return $allowed_params;
  }


  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id',
		'name',
		'short_description',
		'charge_amount',
		'deposit_amount',
		'status',
		'visibility',
		'order_number',
		'updated_at',
		'created_at');
    return $params_to_save;
  }
}