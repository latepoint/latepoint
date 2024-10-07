<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsProcessModel extends OsModel{
	var $id,
			$name,
			$status = LATEPOINT_STATUS_ACTIVE,
			$event_type, //'booking_created', 'booking_updated', 'booking_start', 'booking_end', 'customer_created', 'transaction_created'
			$actions_json,
			$trigger_conditions,
			$actions,
			$time_offset,
      $updated_at,
      $created_at;

	function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_PROCESSES;

    if($id){
      $this->load_by_id($id);
    }
  }

	public function should_be_active(){
    return $this->where(['status' => LATEPOINT_STATUS_ACTIVE]);
  }

	/**
	 * @param array $objects example format: ['model' => 'booking', 'id' => $booking->id, 'model_ready' => OsModel $booking]
	 * @return bool
	 */
	public function check_if_objects_satisfy_trigger_conditions(array $objects): bool{
		if($this->event->trigger_conditions){
			foreach($this->event->trigger_conditions as $condition){
				foreach($objects as $object){
					if($object['model'] == \LatePoint\Misc\ProcessEvent::get_object_from_property($condition['property'])){
						$attribute = \LatePoint\Misc\ProcessEvent::get_object_attribute_from_property($condition['property']);
						switch($condition['operator']){
							case 'equal':
								$value_arr = explode(',', $condition['value']);
								if(!in_array($object['model_ready']->$attribute, $value_arr)){
									return false;
								}
							break;
							case 'not_equal':
								$value_arr = explode(',', $condition['value']);
								if(in_array($object['model_ready']->$attribute, $value_arr)){
									return false;
								}
							break;
							// below cases are similar:
							// this operator is only available for models prefixed with "old_", we need to iterate through other
							// objects and find the matching one by stripping "old_" from the one that we are comparing change to
							case 'not_changed':
								foreach($objects as $object_to_compare){
									if($object_to_compare['model'] == str_replace('old_', '', $object['model'])){
										if($object['model_ready']->$attribute != $object_to_compare['model_ready']->$attribute){
											return false;
										}
									}
								}
							case 'changed':
								foreach($objects as $object_to_compare){
									if($object_to_compare['model'] == str_replace('old_', '', $object['model'])){
										if($object['model_ready']->$attribute == $object_to_compare['model_ready']->$attribute){
											return false;
										}
									}
								}
							break;
						}
					}
				}
			}
		}
		return true;
	}

	public function get_info(){
		return ['name' => $this->name, 'event_type' => $this->event_type];
	}

  public function delete($id = false){
    if(!$id && isset($this->id)){
      $id = $this->id;
    }
    if($id && $this->db->delete( $this->table_name, array('id' => $id), array( '%d' ))){
      $this->db->delete(LATEPOINT_TABLE_PROCESS_JOBS, array('process_id' => $id, 'status' => LATEPOINT_JOB_STATUS_SCHEDULED), array( '%d', '%s' ) );
      do_action('latepoint_process_deleted', $id);
      return true;
    }else{
      return false;
    }
  }

	public function set_from_params(array $params){
		$this->name = $params['name'];
		if(!empty($params['event'])){
			$this->event_type = $params['event']['type'];
			$this->event = new \LatePoint\Misc\ProcessEvent();
			$this->event->set_from_params($params['event']);

		}


		if(!empty($params['actions'])){
			foreach($params['actions'] as $action_id => $action_params){
				$action = new \LatePoint\Misc\ProcessAction();
				$action->id = $action_id;
				$action->set_from_params($action_params);
				$this->actions[] = $action;
			}
		}
	}

	public function build_from_json(){
		$groups = empty($this->actions_json) ? [] : json_decode($this->actions_json, true);
		$this->trigger_conditions = OsProcessesHelper::extract_trigger_conditions_from_groups($groups);
		$this->actions = OsProcessesHelper::extract_actions_from_groups($groups);
		$this->time_offset = $groups[0]['time_offset'] ?? [];
	}

  protected function get_event() :\LatePoint\Misc\ProcessEvent{
		$event_data = [];
		if(!empty($this->event_type)) $event_data['type'] = $this->event_type;
		if(!empty($this->trigger_conditions)) $event_data['trigger_conditions'] = $this->trigger_conditions;
		if(!empty($this->time_offset)) $event_data['time_offset'] = $this->time_offset;

    $this->event = new \LatePoint\Misc\ProcessEvent($event_data);
    return $this->event;
  }

	protected function params_to_sanitize(){
		return [];
	}

  protected function params_to_save($role = 'admin'){
    $params_to_save = [
			'id',
	    'event_type',
	    'status',
	    'name',
	    'actions_json'
    ];
    return $params_to_save;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = [
			'id',
	    'event_type',
	    'status',
	    'name',
	    'actions_json'
    ];
    return $allowed_params;
  }


  protected function properties_to_validate(){
    $validations = [];
    return $validations;
  }
}