<?php 

class OsConnectorHelper {


	/**
	 *
	 * Returns an array of all the possible connections/resources combinations(between agent/service/location) available to satisfy a booking request
	 *
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @return OsConnectorModel[]
	 */
	public static function get_connections_that_satisfy_booking_request(\LatePoint\Misc\BookingRequest $booking_request, $filter_based_on_access = false): array{
    $connection_model = new OsConnectorModel();

		// agent
		if($filter_based_on_access && !OsRolesHelper::are_all_records_allowed('agent')){
			$allowed_ids = OsRolesHelper::get_allowed_records('agent');
			// if nothing is allowed, or the requested ID is not part of allowed - return blank
			if(empty($allowed_ids) || ($booking_request->agent_id && !in_array($booking_request->agent_id, $allowed_ids))) return [];
			if($booking_request->agent_id){
				$connection_model->where(['agent_id' => $booking_request->agent_id]);
			}else{
				// agent_id is 0, it means ANY is requested, filter based on what's allowed
				$connection_model->where(['agent_id' => $allowed_ids]);
			}
		}else{
			$active_agents = new OsAgentModel();
			$active_agent_ids = array_column($active_agents->select('id')->should_be_active()->get_results(ARRAY_A), 'id');

			if(empty($active_agent_ids)) return []; // no active agents

			if(empty($booking_request->agent_id)){
				// any agent requested, search in a list of active
				$connection_model->where(['agent_id' => $active_agent_ids]);
			}else{
				// specific agent/or agents requested, check if it's in a list of active agents
				if(is_array($booking_request->agent_id)){
					// array of agents requested, intersect
					if(array_intersect($booking_request->agent_id, $active_agent_ids)){
						$connection_model->where(['agent_id' => array_intersect($booking_request->agent_id, $active_agent_ids)]);
					}else{
						return [];
					}
				}else{
					if(in_array($booking_request->agent_id, $active_agent_ids)){
						$connection_model->where(['agent_id' => $booking_request->agent_id]);
					}else{
						// requested agent not in a list of active agents
						return [];
					}
				}
			}
		}

		// location
		if($filter_based_on_access && !OsRolesHelper::are_all_records_allowed('location')){
			$allowed_ids = OsRolesHelper::get_allowed_records('location');
			// if nothing is allowed, or the requested ID is not part of allowed - return blank
			if(empty($allowed_ids) || ($booking_request->location_id && !in_array($booking_request->location_id, $allowed_ids))) return [];
			if($booking_request->location_id){
				$connection_model->where(['location_id' => $booking_request->location_id]);
			}else{
				// location_id is 0, it means ANY is requested, filter based on what's allowed
				$connection_model->where(['location_id' => $allowed_ids]);
			}
		}else{
			$active_locations = new OsLocationModel();
			$active_location_ids = array_column($active_locations->select('id')->should_be_active()->get_results(ARRAY_A), 'id');


			if(empty($active_location_ids)) return []; // no active locations

			if(empty($booking_request->location_id)){
				// any location requested, search in a list of active
				$connection_model->where(['location_id' => $active_location_ids]);
			}else{
				// specific location/or locations requested, check if it's in a list of active locations
				if(is_array($booking_request->location_id)){
					// array of locations requested, intersect
					if(array_intersect($booking_request->location_id, $active_location_ids)){
						$connection_model->where(['location_id' => array_intersect($booking_request->location_id, $active_location_ids)]);
					}else{
						return [];
					}
				}else{
					if(in_array($booking_request->location_id, $active_location_ids)){
						$connection_model->where(['location_id' => $booking_request->location_id]);
					}else{
						// requested location not in a list of active locations
						return [];
					}
				}
			}
		}

		// service
		if($filter_based_on_access && !OsRolesHelper::are_all_records_allowed('service')){
			$allowed_ids = OsRolesHelper::get_allowed_records('service');
			// if nothing is allowed, or the requested ID is not part of allowed - return blank
			if(empty($allowed_ids) || ($booking_request->service_id && !in_array($booking_request->service_id, $allowed_ids))) return [];
			if($booking_request->service_id){
				$connection_model->where(['service_id' => $booking_request->service_id]);
			}else{
				// service_id is 0, it means ANY is requested, filter based on what's allowed
				$connection_model->where(['service_id' => $allowed_ids]);
			}
		}else{
			$active_services = new OsServiceModel();
			$active_service_ids = array_column($active_services->select('id')->should_be_active()->get_results(ARRAY_A), 'id');

			if(empty($active_service_ids)) return []; // no active services

			if(empty($booking_request->service_id)){
				// any service requested, search in a list of active
				$connection_model->where(['service_id' => $active_service_ids]);
			}else{
				// specific service/or services requested, check if it's in a list of active services
				if(is_array($booking_request->service_id)){
					// array of services requested, intersect
					if(array_intersect($booking_request->service_id, $active_service_ids)){
						$connection_model->where(['service_id' => array_intersect($booking_request->service_id, $active_service_ids)]);
					}else{
						return [];
					}
				}else{
					if(in_array($booking_request->service_id, $active_service_ids)){
						$connection_model->where(['service_id' => $booking_request->service_id]);
					}else{
						// requested service not in a list of active services
						return [];
					}
				}
			}
		}

		return $connection_model->get_results_as_models();
	}

  // expects [agent_id, 'service_id', 'location_id']
  public static function count_connections($connection_query_arr, $group_by = false){
    $connection_model = new OsConnectorModel();
    $connection_model->where($connection_query_arr);
    if($group_by){
      $results = $connection_model->select($group_by)->group_by($group_by)->get_results();
      $total = count($results);
    }else{
      $total = $connection_model->count();
    }
    return $total;
  }

	public static function can_satisfy_booking_request(\LatePoint\Misc\BookingRequest $booking_request){
  	$connection_model = new OsConnectorModel();
  	return $connection_model->where(['agent_id' => $booking_request->agent_id, 'location_id' => $booking_request->location_id, 'service_id' => $booking_request->service_id])->set_limit(1)->get_results_as_models();
	}

	public static function has_connection($connection_arr){
  	$connection_model = new OsConnectorModel();
  	return $connection_model->where($connection_arr)->set_limit(1)->get_results_as_models();
	}

  // expects [agent_id, 'service_id', 'location_id']
  public static function save_connection($connection_arr){
  	$connection_model = new OsConnectorModel();
  	$existing_connection = $connection_model->where($connection_arr)->set_limit(1)->get_results_as_models();
    if($existing_connection){
    	// Update
    }else{
    	// Insert
    	$connection_model->set_data($connection_arr);
    	return $connection_model->save();
    }
  }


  // object type: agent_id, service_id, location_id
  public static function get_connected_object_ids($object_type = 'agent_id', $connections = []){
    if(!in_array($object_type, ['agent_id', 'service_id', 'location_id'])) return false;
    $clean_connections = [];
    if(isset($connections['agent_id']) && !empty($connections['agent_id']) && $connections['agent_id'] != LATEPOINT_ANY_AGENT) $clean_connections['agent_id'] = $connections['agent_id'];
    if(isset($connections['service_id']) && !empty($connections['service_id'])) $clean_connections['service_id'] = $connections['service_id'];
    if(isset($connections['location_id']) && !empty($connections['location_id']) && $connections['location_id'] != LATEPOINT_ANY_LOCATION) $clean_connections['location_id'] = $connections['location_id'];
    $connection_model = new OsConnectorModel();
    if(!empty($clean_connections)) $connection_model->where($clean_connections);
    $objects = $connection_model->select($object_type)->group_by($object_type)->get_results();
    $ids = [];
    if($objects){
      foreach($objects as $object){
        if(isset($object->$object_type)) $ids[] = $object->$object_type;
      }
    }
    return $ids;
  }


  // expects [agent_id, 'service_id', 'location_id']
  public static function remove_connection($connection_arr){
  	$connection_model = new OsConnectorModel();
  	if(isset($connection_arr['agent_id']) && isset($connection_arr['service_id']) && isset($connection_arr['location_id'])){
	  	$existing_connection = $connection_model->where($connection_arr)->set_limit(1)->get_results_as_models();
	  	if($existing_connection){
	  		$existing_connection->delete();
	  	}
  	}
  }

}