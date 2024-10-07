<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsProcessesHelper
{

	public static function check_if_process_exists(OsProcessModel $process): bool{
		$existing_process = new OsProcessModel();
		$existing_process = $existing_process->select('id')->where(['event_type' => $process->event_type, 'name' => $process->name])->set_limit(1)->get_results();
		return !empty($existing_process);
	}

	public static function get_object_by_source(string $source, string $value, bool $include_model = true){
		$object = false;
		switch($source){
			case 'booking_id':
				$object = ['model' => 'booking', 'id' => $value];
				if($include_model){
					$model = new OsBookingModel($value);
					$object['model_ready'] = $model;
				}
				break;
		}
		return $object;
	}

	public static function processes_list_for_select(){
		$processes = new OsProcessModel();
		$processes = $processes->get_results_as_models();
		$processes_list = [];
		foreach($processes as $process){
			$processes_list[] = ['value' => $process->id, 'label' => $process->name];
		}
		return $processes_list;
	}

	public static function extract_trigger_conditions_from_groups($groups){
		$trigger_conditions = [];
		if($groups){
			foreach($groups as $group){
				if($group['type'] == 'group' && !empty($group['trigger_condition'])){
					$trigger_condition = ['id' => \LatePoint\Misc\ProcessEvent::generate_trigger_condition_id()];
					$trigger_condition['property'] = $group['trigger_condition']['property'];
					foreach($group['items'] as $item){
						if($item['type'] == 'trigger_condition_branch'){
							$trigger_condition['operator'] = $item['settings']['operator'];
							$trigger_condition['value'] = $item['settings']['value'];
						}
						$trigger_conditions[] = $trigger_condition;

						if($item['items']){
							$temp = self::extract_trigger_conditions_from_groups($item['items']);
							if(!empty($temp)) $trigger_conditions = array_merge($trigger_conditions, $temp);
						}
					}
				}
			}
		}
		return $trigger_conditions;
	}

	/**
	 * @param \LatePoint\Misc\ProcessEvent $event
	 * @return string
	 *
	 * Generate conditions form for an event
	 *
	 */
	public static function trigger_conditions_html_for_event(\LatePoint\Misc\ProcessEvent $event): string{
		if(empty(\LatePoint\Misc\ProcessEvent::get_available_trigger_condition_objects_for_event_type($event->type))) return '';
		$temp_id = 'pe_'.\OsUtilHelper::random_text('alnum', 6);
		$conditions_html = '<div class="pe-conditions" id="pe-conditions-for-'.esc_attr($temp_id).'" style="'.((!empty($event->trigger_conditions)) ? 'display:  block;' : 'display: none;').'">
							            <div class="pe-conditions-heading">'.__('Trigger only if:', 'latepoint').'</div>
							            '.$event->trigger_conditions_form_html().'
							          </div>';
		$conditions_html = apply_filters('latepoint_process_conditions_html', $conditions_html, $event, $temp_id);
		$html = '<div class="sub-section-row">
	        <div class="sub-section-label">
	          <h3>'.__('Conditional', 'latepoint').'</h3>
	        </div>
	        <div class="sub-section-content">
	          '.OsFormHelper::toggler_field('process[event][conditional]', __('Trigger only when specific conditions are met', 'latepoint'), !empty($event->trigger_conditions), "pe-conditions-for-". $temp_id).'
	          '.$conditions_html.'
	        </div>
	      </div>';
		return $html;
	}

	public static function time_offset_html_for_event(\LatePoint\Misc\ProcessEvent $event): string{
		$temp_id = 'pe_'.\OsUtilHelper::random_text('alnum', 6);

		$time_offset_settings_html = OsUtilHelper::pro_feature_block();
		$time_offset_settings_html = apply_filters('latepoint_event_time_offset_settings_html', $time_offset_settings_html, $event);

		$html = '<div class="sub-section-row">
	        <div class="sub-section-label">
	          <h3>'.__('Time offset', 'latepoint').'</h3>
	        </div>
	        <div class="sub-section-content">
	          '.OsFormHelper::toggler_field('process[event][has_time_offset]', __('Execute actions with a time offset', 'latepoint'), !empty($event->time_offset), "pe-conditions-for-". $temp_id).'
	          <div class="pe-conditions" id="pe-conditions-for-'.esc_attr($temp_id).'" style="'.((!empty($event->time_offset)) ? 'display:  block;' : 'display: none;').'">
	            '.$time_offset_settings_html.'
	          </div>
	        </div>
	      </div>';
		return $html;
	}

	public static function extract_actions_from_groups($groups){
		$actions = [];
		if($groups){
			foreach($groups as $group){
				if($group['type'] == 'action'){
					$action_data = $group['settings'];
					$actions[] = new \LatePoint\Misc\ProcessAction(['type' => $action_data['type'], 'id' => $group['id'], 'status' => $action_data['status'] ?? LATEPOINT_STATUS_ACTIVE, 'settings' => $action_data['settings']]);
				}
				if(!empty($group['items'])){
					$temp = self::extract_actions_from_groups($group['items']);
					if(!empty($temp)) $actions = array_merge($actions, $temp);
				}
			}
		}
		return $actions;
	}

	public static function iterate_trigger_conditions($trigger_conditions, $actions){
		$trigger_condition = reset($trigger_conditions);
		if(!empty($trigger_conditions)){
			$group = [[
				'type' => 'group',
				'trigger_condition' => [
					'property' => $trigger_condition['property']
				],
				'items' => [[
					'type' => 'trigger_condition_branch',
					'settings' => [
						'operator' => $trigger_condition['operator'],
						'value' => $trigger_condition['value']
					],
					'items' => self::iterate_trigger_conditions(array_slice($trigger_conditions, 1), $actions)
				]]
			]];
		}else{
			// no more conditions, do actions
			$action_items = [];
			if(!empty($actions)){
				foreach($actions as $action_id => $action){
					$action_items[] = [
						'type' => 'action',
						'id' => $action_id,
						'settings' => [
							'status' => $action['status'] ?? LATEPOINT_STATUS_ACTIVE,
							'type' => $action['type'],
							'settings' => $action['settings'] ?? []
						]
					];
				}
			}
			$group = [[
				'type' => 'group',
				'trigger_condition' => false,
				'items' => $action_items
			]];
		}
		return $group;
	}

	public static function values_for_trigger_condition_property($property){
		$values = [];
		$property_data = explode('__', $property);
		$property_object = $property_data[0];
		$property_attribute = $property_data[1];
		switch($property_object){
			case 'order':
			case 'old_order':
				switch($property_attribute) {
					case 'fulfillment_status':
						$fulfillment_statuses = OsOrdersHelper::get_fulfillment_statuses_list();
						foreach ( $fulfillment_statuses as $fulfillment_status_key => $fulfillment_status ) {
							$values[] = [ 'value' => $fulfillment_status_key, 'label' => $fulfillment_status ];
						}
						break;
					case 'payment_status':
						$payment_statuses = OsOrdersHelper::get_order_payment_statuses_list();
						foreach ( $payment_statuses as $payment_status_key => $payment_status ) {
							$values[] = [ 'value' => $payment_status_key, 'label' => $payment_status ];
						}
						break;
					case 'status':
						$statuses = OsOrdersHelper::get_order_statuses_list();
						foreach ( $statuses as $status_key => $status ) {
							$values[] = [ 'value' => $status_key, 'label' => $status ];
						}
						break;
				}
				break;
			case 'booking':
			case 'old_booking':
				switch($property_attribute){
					case 'payment_status':
						$payment_statuses = OsOrdersHelper::get_order_payment_statuses_list();
						foreach($payment_statuses as $payment_status_key => $payment_status){
							$values[] = ['value' => $payment_status_key, 'label' => $payment_status];
						}
						break;
					case 'status':
						$statuses = OsBookingHelper::get_statuses_list();
						foreach($statuses as $status_key => $status){
							$values[] = ['value' => $status_key, 'label' => $status];
						}
						break;
					case 'agent_id':
		        $values = OsFormHelper::model_options_for_multi_select('agent');
						break;
					case 'service_id':
		        $values = OsFormHelper::model_options_for_multi_select('service');
						break;
				}
				break;
			case 'customer':
				break;
			case 'transaction':
				switch($property_attribute){
					case 'payment_method':
						$values = OsPaymentsHelper::get_all_payment_methods_for_select();
						break;
					case 'payment_portion':
						$values = OsPaymentsHelper::get_payment_portions_list();
						break;
					case 'kind':
						$values = OsPaymentsHelper::get_list_of_transaction_kinds();
						break;
					case 'status':
						$values = OsPaymentsHelper::get_transaction_statuses_list();
						break;
				}
				break;
		}
    $values = apply_filters('latepoint_available_values_for_process_event_trigger_condition_property', $values, $property);
		return $values;
	}


}