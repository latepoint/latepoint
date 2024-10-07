<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsProcessesController' ) ) :


  class OsProcessesController extends OsController
  {

	  function __construct()
	  {
		  parent::__construct();


		  $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'processes/';
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('processes');
      $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('processes');
		  $this->vars['breadcrumbs'][] = array('label' => __('Workflows', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('processes', 'index')));
	  }

    public function new_form(){
      $this->vars['process'] = new OsProcessModel();
      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }

		public function reload_event_trigger_conditions(){
			$event = new \LatePoint\Misc\ProcessEvent(['type' => $this->params['event_type']]);
			$trigger_conditions_form_section_html = OsProcessesHelper::trigger_conditions_html_for_event($event);

			if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $trigger_conditions_form_section_html));
      }
		}



		public function available_properties_for_object_code(){
			$object_code = $this->params['object_code'];
			$properties_for_select = \LatePoint\Misc\ProcessEvent::get_properties_for_object_code($object_code, true);
			$html = '';
			foreach($properties_for_select as $property){
				$html.= '<option value="'.$property['value'].'">'.$property['label'].'</option>';
			}
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html));
      }
		}


		public function available_operators_for_trigger_condition_property(){
			// example format: old_booking__agent_id
			$property = $this->params['property'];
			$operators = \LatePoint\Misc\ProcessEvent::trigger_condition_operators_for_property($property);
			$html = '';
			foreach($operators as $value => $label){
				$html.= '<option value="'.$value.'">'.$label.'</option>';
			}
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $html));
      }
		}

    public function available_values_for_trigger_condition_property(){
      $values = [];
      $property = $this->params['property'];
      $operator = $this->params['operator'];
      $trigger_condition_id = $this->params['trigger_condition_id'];
			$values = OsProcessesHelper::values_for_trigger_condition_property($property);
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => OsFormHelper::multi_select_field('process[event][trigger_conditions]['.$trigger_condition_id.'][value]', false, $values, false, [])));
      }
    }

		function destroy(){
      if(filter_var($this->params['id'], FILTER_VALIDATE_INT)){
	      $this->check_nonce('destroy_process_' . $this->params['id']);
        $process = new OsProcessModel($this->params['id']);
        if($process->delete()){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Process Removed', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error Removing Workflow', 'latepoint');
        }
      }else{
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Process Removed', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
		}

		function save(){
			$process_data = $this->params['process'];
			if(!empty($process_data['id'])){
	      $this->check_nonce('edit_process_' . $process_data['id']);
				$process = new OsProcessModel($process_data['id']);
				$new_process = false;
			}else{
	      $this->check_nonce('new_process');
				$process = new OsProcessModel();
				$new_process = true;
			}

			$process->status = $process_data['status'] ?? LATEPOINT_PROCESS_ACTIVE;
			$process->name = $process_data['name'];
			$process->event_type = $process_data['event']['type'];
			$actions = [];


			// check if conditions are turned ON and exist in params
			$trigger_conditions = (isset($process_data['event']['conditional']) && $process_data['event']['conditional'] == LATEPOINT_VALUE_ON && isset($process_data['event']['trigger_conditions']) && !empty($process_data['event']['trigger_conditions'])) ? $process_data['event']['trigger_conditions'] : [];
			if(isset($process_data['actions'])){
				$actions = OsProcessesHelper::iterate_trigger_conditions($trigger_conditions, $process_data['actions']);

				if($process_data['event']['has_time_offset'] == LATEPOINT_VALUE_ON){
					$actions[0]['time_offset'] = $process_data['event']['time_offset'];
				}else{
					$actions[0]['time_offset'] = [];
				}
			}else{
				$actions = [];
			}

			$process->actions_json = wp_json_encode($actions);
			$old_process = $process->is_new_record() ? [] : clone $process;
			if($process->save()){
				if(!$new_process){
					// remove previously created jobs for this process that hasn't run yet
					$jobs = new OsProcessJobModel();
					$jobs->delete_where(['process_id' => $process->id, 'status' => LATEPOINT_JOB_STATUS_SCHEDULED]);
					/**
			     * Process was updated
			     *
			     * @since 4.7.0
			     * @hook latepoint_process_updated
			     *
			     * @param {OsProcessModel} $process instance of process model that was updated
			     * @param {OsProcessModel} $old_process instance of process model before it was updated
			     */
					do_action('latepoint_process_updated', $process, $old_process);
				}else{
					/**
			     * Process was created
			     *
			     * @since 4.7.0
			     * @hook latepoint_process_created
			     *
			     * @param {OsProcessModel} $process instance of process model that was created
			     */
					do_action('latepoint_process_created', $process);
				}
				$process->build_from_json();
				OsProcessJobsHelper::recreate_jobs_for_existing_records($process);
				$message = __('Process Saved', 'latepoint');
				$status = LATEPOINT_STATUS_SUCCESS;
			}else{
				$message = __('Error saving process', 'latepoint');
				$status = LATEPOINT_STATUS_ERROR;
			}

			if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $message));
      }
		}

		function new_trigger_condition(){
			$process_event = new \LatePoint\Misc\ProcessEvent(['type' => $this->params['event_type']]);
      $trigger_condition_html = $process_event->generate_trigger_condition_form_html();
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $trigger_condition_html));
      }
		}

		function new_action(){
			$action = new \LatePoint\Misc\ProcessAction();
			$response_html = \LatePoint\Misc\ProcessAction::generate_form($action);

			if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html));
      }
		}

		function load_action_settings(){
			$action = new \LatePoint\Misc\ProcessAction(['type' => $this->params['action_type'], 'id' => $this->params['action_id']]);

			$template_id = $this->params['template_id'] ?? false;
			if($template_id){
				$action->load_settings_from_template($template_id);
			}

			if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => \LatePoint\Misc\ProcessAction::generate_settings_fields($action)));
      }
		}

		function index(){
			$processes = new OsProcessModel();
			$this->vars['processes'] = $processes->get_results_as_models();
      $this->format_render(__FUNCTION__);
		}

		function action_test_run(){
			$action = new \LatePoint\Misc\ProcessAction();
			$action->set_from_params($this->params['action']);

			$available_data_sources = $action->event->get_available_data_sources();
			foreach($available_data_sources as $data_source){
				$action->selected_data_objects[] = ['model' => $data_source['model'], 'id' => $this->params['data_source'][$data_source['name']]];
			}

			$result = $action->run();
			if($this->get_return_format() == 'json') {
				$this->send_json(['status' => $result['status'], 'message' => $result['message']]);
			}
		}

		function test_run(){
			$process = new OsProcessModel();
			$process->set_from_params($this->params['process']);

			$action_ids_to_run = isset($this->params['action_ids']) ? explode(',', $this->params['action_ids']) : [];
			$data_sources = $this->params['data_source'];

			$selected_data_objects = [];
			foreach($data_sources as $source_id => $data_source_value){
				$selected_data_objects[] = OsProcessesHelper::get_object_by_source($source_id, $data_source_value);
			}

			if($process->check_if_objects_satisfy_trigger_conditions($selected_data_objects)){
				foreach($process->actions as $action){
					if($action->status != LATEPOINT_STATUS_ACTIVE) continue;
					if(!in_array($action->id, $action_ids_to_run)) continue;
					$action->selected_data_objects = $selected_data_objects;
					$action->run();
				}
				$status = LATEPOINT_STATUS_SUCCESS;
				$message = __('Run complete', 'latepoint').'. <a href="'.OsRouterHelper::build_link(['activities', 'index']).'" target="_blank">'.__('view logs', 'latepoint').'</a>';
			}else{
				$status = LATEPOINT_STATUS_ERROR;
				$message = __('Trigger conditions not met', 'latepoint');
			}


			if($this->get_return_format() == 'json') {
				$this->send_json(['status' => $status, 'message' => $message]);
			}
		}

		function test_preview(){
			$process = new OsProcessModel();
			$process->set_from_params($this->params['process']);

			$action_settings_html = '';
			$available_data_sources = $process->event->get_available_data_sources();


			foreach($available_data_sources as $data_source){
				$action_settings_html.= OsFormHelper::select_field('data_source['.$data_source['name'].']', $data_source['label'], $data_source['values'], $data_source['values']['0']['value'], [ 'class' => 'process-test-data-source-selector', 'data-route' => OsRouterHelper::build_route_name('processes', 'reload_action_test_preview') ]);
			}
			$this->vars['action_settings_html'] = $action_settings_html;
			$this->vars['process'] = $process;

      $this->format_render(__FUNCTION__);
		}

		function action_test_preview(){
			$action = new \LatePoint\Misc\ProcessAction();
			// because this data is part of a bigger process form, we need to extract just the action params
			$action->set_from_params(reset($this->params['process']['actions']));
			$action->event = new \LatePoint\Misc\ProcessEvent(['type' => $this->params['process_event_type']]);
			$action_settings_html = '';
			$available_data_sources = $action->event->get_available_data_sources();
			foreach($available_data_sources as $data_source){
				$action_settings_html.= OsFormHelper::select_field('data_source['.$data_source['name'].']', $data_source['label'], $data_source['values'], $data_source['values']['0']['value'], [ 'class' => 'process-action-test-data-source-selector', 'data-route' => OsRouterHelper::build_route_name('processes', 'reload_action_test_preview') ]);
				$action->selected_data_objects[] = ['model' => $data_source['model'], 'id' => $data_source['values'][0]['value']];
			}
			$action_settings_html.= OsFormHelper::hidden_field('action[type]', $action->type);
			$action_settings_html.= OsFormHelper::hidden_field('action[event][type]', $action->event->type);
			foreach($action->settings as $key => $setting){
				$action_settings_html.= OsFormHelper::hidden_field('action[settings]['.$key.']', esc_attr($setting));
			}
			$preview_html = $action->generate_preview();

			$this->vars['action'] = $action;
			$this->vars['preview_html'] = $preview_html;
			$this->vars['action_settings_html'] = $action_settings_html;

      $this->format_render(__FUNCTION__);
		}

		function reload_action_test_preview(){
			$action = new \LatePoint\Misc\ProcessAction();
			$action->set_from_params($this->params['action']);
			$available_data_sources = $action->event->get_available_data_sources();
			foreach($available_data_sources as $data_source){
				$action->selected_data_objects[] = ['model' => $data_source['model'], 'id' => $this->params['data_source'][$data_source['name']]];
			}
			if($this->get_return_format() == 'json'){
        $this->send_json(['status' => LATEPOINT_STATUS_SUCCESS, 'message' => $action->generate_preview()]);
      }
		}

  }

endif;