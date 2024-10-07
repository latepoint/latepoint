<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsJoinBundlesServicesModel extends OsModel {

	var $id,
		$bundle_id,
		$total_attendees,
		$duration,
		$service_id,
		$quantity,
		$updated_at,
		$created_at;

	function __construct($id = false) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_JOIN_BUNDLES_SERVICES;

		if ($id) {
			$this->load_by_id($id);
		}
	}

	function get_services_for_bundle_id($bundle_id){
		return $this->where(['bundle_id' => $bundle_id])->get_results_as_models();
	}


  protected function allowed_params($role = 'admin'){
    $allowed_params = array('id',
		'bundle_id',
		'service_id',
		'total_attendees',
		'duration',
		'quantity',
		'updated_at',
		'created_at');
    return $allowed_params;
  }


  protected function params_to_save($role = 'admin'){
    $params_to_save = array('id',
		'bundle_id',
		'service_id',
		'total_attendees',
		'duration',
		'quantity',
		'updated_at',
		'created_at');
    return $params_to_save;
  }
}