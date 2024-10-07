<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsProcessJobModel extends OsModel{
	var $id,
			$process_id,
      $object_id,
      $object_model_type,
      $to_run_after_utc,
      $status = LATEPOINT_JOB_STATUS_SCHEDULED,
      $settings,
      $run_result,
      $process_info,
      $updated_at,
      $created_at;

	function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_PROCESS_JOBS;

    if($id){
      $this->load_by_id($id);
    }
  }

  public function get_link_to_object(){
    $href = '#';
		$attrs = '';
    switch($this->process->event_type){
      case 'order_updated':
      case 'order_created':
        $attrs = OsOrdersHelper::quick_order_btn_html($this->object_id);
      break;
      case 'customer_created':
        $attrs = OsCustomerHelper::quick_customer_btn_html($this->object_id);
      break;
      case 'booking_start':
      case 'booking_end':
      case 'booking_created':
      case 'booking_updated':
        $attrs = OsBookingHelper::quick_booking_btn_html($this->object_id);
      break;
    }
		$link = '<a href="'.$href.'" '.$attrs.'>'.$this->object_id.'</a>';
		return $link;
  }

	public function get_original_process_attribute($attribute){
		$process_info = json_decode($this->process_info, true);
		return $process_info[$attribute] ?? __('n/a', 'latepoint');
	}

	public function get_action_by_id_from_settings($action_id){
		foreach($this->actions as $action){
			if($action->id == $action_id) return $action;
		}
		return null;
	}

	public function get_actions($force_refresh = false){
		if(!isset($this->actions) || $force_refresh){
			$this->actions = [];
			$settings = json_decode($this->settings, true);
			if($settings){
				foreach($settings['action_data'] as $action_id => $action_data){
					$action = new \LatePoint\Misc\ProcessAction();
					$action->id = $action_id;
					$action->type = $settings['action_info'][$action_id]['type'];
					$action->prepared_data_for_run = $action_data;
					$this->actions[] = $action;
				}
			}
		}
		return $this->actions;
	}

	public function get_actions_summary(){
		$actions_html = '';

		$process_actions = $this->get_actions();
		$run_result = $result = json_decode($this->run_result, true);
		if(empty($process_actions) && empty($run_result)) return __('No Actions', 'latepoint');

		foreach($process_actions as $action){
			if(isset($run_result['ran_actions_info'][$action->id])){
				$status_icon = $run_result['ran_actions_info'][$action->id]['run_status'] == LATEPOINT_STATUS_SUCCESS ? '<i style="color: #78ad06;" class="latepoint-icon latepoint-icon-checkmark"></i>' : '<i style="color: #ad0606;" class="latepoint-icon latepoint-icon-x-square"></i>';
				$action_name = '<span>'.\LatePoint\Misc\ProcessAction::get_action_name_for_type($run_result['ran_actions_info'][$action->id]['type']).'</span>'.$status_icon.'</span>';
			}else{
				$action_name = '<span>'.\LatePoint\Misc\ProcessAction::get_action_name_for_type($action->type).'</span><i class="latepoint-icon latepoint-icon-bell"></i></span>';
			}
			$action_icon = '';
			switch($action->type){
				case 'send_email':
					$action_icon = '<i class="latepoint-icon latepoint-icon-mail"></i>';
					break;
				case 'send_sms':
					$action_icon = '<i class="latepoint-icon latepoint-icon-message-circle"></i>';
					break;
				case 'trigger_webhook':
					$action_icon = '<i class="latepoint-icon latepoint-icon-globe"></i>';
					break;
			}

			$actions_html.= '<span 
				data-os-action="'.OsRouterHelper::build_route_name('process_jobs', 'preview_job_action').'" 
				data-os-output-target="side-panel" 
				data-os-after-call="latepoint_init_json_view" 
				data-os-params="'.OsUtilHelper::build_os_params(['job_id' => $this->id, 'action_id' => $action->id]).'" 
				data-os-lightbox-classes="width-800" 
				class="action-run-info-pill">'.$action_icon.$action_name.'</span>';
		}

		return $actions_html;
	}

  protected function get_process() {
	  if ($this->process_id) {
		  if (!isset($this->process) || (isset($this->process) && ($this->process->id != $this->process_id))) {
			  $this->process = new OsProcessModel($this->process_id);
				$this->process->build_from_json();
		  }
	  } else {
		  $this->process = new OsProcessModel();
	  }
	  return $this->process;
  }

	/**
	 * @param $action_ids_to_run IDs of actions that need to be run, all will run by default
	 * @return $this
	 */
	public function run(array $action_ids_to_run = []){
		$actions = $this->get_actions();
		if($actions){
			$settings = json_decode($this->settings, true);
			$error_messages = [];
			// try to load from previous run, if it ever existed and if select action IDs are passed, otherwise overwrite. This is done to not override actions that ran before with partial actions that ran now
			$previous_run_result = empty($this->run_result) ? [] : json_decode($this->run_result, true);
			$ran_actions_info = (isset($previous_run_result['ran_actions_info']) && $action_ids_to_run) ? $previous_run_result['ran_actions_info'] : [];
			foreach($actions as $action){
				// skip if specific action IDs are passed and action in this loop is not one of the requested to be run
				if($action_ids_to_run && !in_array($action->id, $action_ids_to_run)) continue;
				if(!isset($settings['action_data'][$action->id])){
					$error_messages[] = $action->id.': '.__('Process action have been modified since the job was created.', 'latepoint');
					continue;
				}
				$action->prepared_data_for_run = $settings['action_data'][$action->id];
				$result = $action->run(false);
				$ran_actions_info[$action->id] = [
					'type' => $action->type,
					'id' => $action->id,
					'run_status' => $result['status'],
					'run_datetime_utc' => OsTimeHelper::now_datetime_utc_in_db_format(),
					'run_message' => $result['message']];
				if($result['status'] != LATEPOINT_STATUS_SUCCESS){
					$error_messages[] = $action->id.': '.$result['message'];
				}
			}
			if(empty($error_messages)){
				$this->status = LATEPOINT_JOB_STATUS_COMPLETED;
				$message = $action_ids_to_run ? __('Selected actions ran successfully.', 'latepoint').' ['.implode(',', $action_ids_to_run).']' : __('The job ran successfully.', 'latepoint');
				$this->run_result = wp_json_encode(['status' => LATEPOINT_STATUS_SUCCESS, 'run_datetime_utc' => OsTimeHelper::now_datetime_utc_in_db_format(), 'message' => $message, 'ran_actions_info' => $ran_actions_info]);
			}else{
				$this->status = LATEPOINT_JOB_STATUS_ERROR;
				$this->run_result = wp_json_encode(['status' => LATEPOINT_STATUS_ERROR, 'run_datetime_utc' => OsTimeHelper::now_datetime_utc_in_db_format(), 'message' => implode(', ', $error_messages), 'ran_actions_info' => $ran_actions_info]);
			}
		}else{
			$this->run_result = wp_json_encode(['status' => LATEPOINT_STATUS_SUCCESS, 'run_datetime_utc' => OsTimeHelper::now_datetime_utc_in_db_format(), 'message' => __('Job process has no actions to run', 'latepoint')]);
			$this->status = LATEPOINT_JOB_STATUS_COMPLETED;
		}
		$activity = new OsActivityModel();

		switch($this->get_original_process_attribute('event_type')){
			case 'order_created':
			case 'order_updated':
				$order = new OsOrderModel($this->object_id);
				$activity->customer_id = $order->customer_id;
				$activity->order_id = $order->id;
				break;
			case 'booking_created':
			case 'booking_updated':
			case 'booking_start':
			case 'booking_end':
				$booking = new OsBookingModel($this->object_id);
				$activity->booking_id = $this->object_id;
				$activity->customer_id = $booking->customer_id;
				$activity->agent_id = $booking->agent_id;
				$activity->service_id = $booking->service_id;
				$activity->location_id = $booking->location_id;
				$activity->order_id = $booking->order->id;
			break;
			case 'customer_created':
				$activity->customer_id = $this->object_id;
			break;
			case 'transaction_created':
				$transaction = new OsTransactionModel($this->object_id);
				$activity->order_id = $transaction->order_id;
			break;
		}

		$this->save();

		$activity->code = 'process_job_run';

		if(OsAuthHelper::get_highest_current_user_type()){
	    $activity->initiated_by = OsAuthHelper::get_highest_current_user_type();
	    $activity->initiated_by_id = OsAuthHelper::get_highest_current_user_id();
		}
		$activity->description = wp_json_encode(['job_id' => $this->id, 'processed_datetime' => OsTimeHelper::now_datetime_in_db_format(), 'run_result' => $this->run_result, 'status' => LATEPOINT_STATUS_SUCCESS]);

		$activity->save();
		return $this;
	}

  protected function params_to_save($role = 'admin'){
    $params_to_save = [
			'id',
	    'process_id',
			'object_id',
			'object_model_type',
			'to_run_after_utc',
			'status',
			'process_info',
			'settings',
			'run_result'
    ];
    return $params_to_save;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = [
			'id',
	    'process_id',
			'object_id',
			'object_model_type',
			'to_run_after_utc',
			'status',
			'process_info',
			'settings',
			'run_result'
    ];
    return $allowed_params;
  }


  protected function properties_to_validate(){
    $validations = [];
    return $validations;
  }
}