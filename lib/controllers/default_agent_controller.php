<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsDefaultAgentController' ) ) :


  class OsDefaultAgentController extends OsController {



    function __construct(){
      parent::__construct();

      $this->views_folder = plugin_dir_path( __FILE__ ) . '../views/default_agent/';
		  $this->vars['page_header']   = OsMenuHelper::get_menu_items_by_id( 'agents' );
      $this->vars['breadcrumbs'][] = array('label' => __('Agent', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'index') ) );
    }



    /*
      Edit agent
    */

    public function edit_form(){
			$agent = OsAgentHelper::get_default_agent();

      if(!OsAuthHelper::get_current_user()->check_if_allowed_record_id($agent->id, 'agent')) $this->access_not_allowed();

      $this->vars['page_header'] = __('Agents', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Agents', 'latepoint'), 'link' => false );

      if($agent->id){

        $this->vars['agent'] = $agent;

      }

      $this->format_render(__FUNCTION__);
    }


    public function update(){
      $is_new_record = (isset($this->params['agent']['id']) && $this->params['agent']['id']) ? false : true;

      $this->check_nonce($is_new_record ? 'new_agent' :  'edit_agent_'. $this->params['agent']['id']);
      $agent = new OsAgentModel();
      $agent->set_data($this->params['agent']);
      $agent->set_features($this->params['agent']['features']);
      $extra_response_vars = array();

      if($agent->save() && (empty($this->params['agent']['services']) || $agent->save_locations_and_services($this->params['agent']['services']))){
        if($is_new_record){
          $response_html = __('Agent Created. ID:', 'latepoint') . $agent->id;
          OsActivitiesHelper::create_activity(array('code' => 'agent_create', 'agent_id' => $agent->id));
        }else{
          $response_html = __('Agent Updated. ID:', 'latepoint') . $agent->id;
          OsActivitiesHelper::create_activity(array('code' => 'agent_update', 'agent_id' => $agent->id));
        }
        $status = LATEPOINT_STATUS_SUCCESS;
        // save schedules
        if($this->params['is_custom_schedule'] == 'on'){
          $agent->save_custom_schedule($this->params['work_periods']);
        }elseif($this->params['is_custom_schedule'] == 'off'){
          $agent->delete_custom_schedule();
        }
        $extra_response_vars['record_id'] = $agent->id;
        do_action('latepoint_agent_saved', $agent, $is_new_record, $this->params['agent']);
      }else{
        $response_html = $agent->get_error_messages();
        $status = LATEPOINT_STATUS_ERROR;
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html) + $extra_response_vars);
      }
    }


  }


endif;