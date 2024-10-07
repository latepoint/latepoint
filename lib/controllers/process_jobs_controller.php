<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsProcessJobsController' ) ) :


  class OsProcessJobsController extends OsController
  {

	  function __construct()
	  {
		  parent::__construct();


		  $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'process_jobs/';
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('processes');
      $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('processes');
		  $this->vars['breadcrumbs'][] = array('label' => __('Process Jobs', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('process_jobs', 'index')));
	  }

		public function view_job_run_result(){
			$job = new OsProcessJobModel($this->params['id']);
			$data = json_decode($job->run_result);
			$process_info = json_decode($job->process_info);

			$this->vars['job'] = $job;
			$this->vars['meta_html'] = '<div class="activity-preview-to"><span class="os-label">'.__('Process:', 'latepoint').'</span><span class="os-value">'.$process_info->name.'</span><span class="os-label">'.__('Trigger:', 'latepoint').'</span><span class="os-value">'.\LatePoint\Misc\ProcessEvent::get_event_name_for_type($process_info->event_type).'</span></div>';
			$this->vars['content_html'] = '<pre class="format-json">'.$job->run_result.'</pre>';
			$this->vars['process_name'] = __('Job Results', 'latepoint');
			$this->vars['status_html'] = '<div class="status-item">'.__('Status:', 'latepoint').' <strong>'.$data->status.'</strong></div>';
			$this->vars['status_html'].= '<div class="status-item">'.__('Processed on (UTC):', 'latepoint').' <strong>'.$data->run_datetime_utc.'</strong></div>';
			$this->vars['status'] = $data->status;

      $this->format_render(__FUNCTION__);
		}

		public function preview_job_action(){

			$job = new OsProcessJobModel($this->params['job_id']);
			$action = $job->get_action_by_id_from_settings($this->params['action_id']);

			$result_data = !empty($job->run_result) ? json_decode($job->run_result, true) : [];


			$status_html = '';
			if(!empty($result_data['ran_actions_info'])){
				foreach($result_data['ran_actions_info'] as $ran_action_info){
					if($ran_action_info['id'] == $action->id){
						$status_html = '<div class="activity-status-wrapper status-'.$ran_action_info['run_status'].'"><div class="activity-status-content">';
						$status_html.= '<div class="status-item">'.__('Status:', 'latepoint').' <strong>'.$ran_action_info['run_status'].'</strong></div>';
						$status_html.= '<div class="status-item">'.__('Processed on:', 'latepoint').' <strong>'.$ran_action_info['run_datetime_utc'].'</strong></div>';
						if($ran_action_info['run_status'] == 'error') $status_html.= '<div class="status-item">'.__('Error:', 'latepoint').' <strong>'.$ran_action_info['run_message'].'</strong></div>';
						$status_html.= '</div></div>';
					}
				}
			}

			$this->vars['preview_html'] = $action->generate_preview();
			$this->vars['action'] = $action;
			$this->vars['action_status_html'] = $status_html;
			$this->vars['job'] = $job;

      $this->format_render(__FUNCTION__);
		}

		public function run_job(){
			if(!filter_var($this->params['job_id'], FILTER_VALIDATE_INT)) return false;
			$this->check_nonce('run_job_'.$this->params['job_id']);
			$action_ids = $this->params['action_ids'] ?? [];
			$job = new OsProcessJobModel($this->params['job_id']);
			if($job) $job->run($action_ids);
			$result = json_decode($job->run_result, true);

      if($this->get_return_format() == 'json'){
        $this->send_json(['status' => $result['status'], 'message' => $result['message']]);
      }
		}

		public function cancel(){
			if(!filter_var($this->params['id'], FILTER_VALIDATE_INT)) return false;
			$job = new OsProcessJobModel($this->params['id']);
			if($job){
				$job->update_attributes(['status' => LATEPOINT_JOB_STATUS_CANCELLED]);
			}
      if($this->get_return_format() == 'json'){
        $this->send_json(['status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Job cancelled', 'latepoint')]);
      }
		}

		public function index(){
      $per_page = OsSettingsHelper::get_number_of_records_per_page();
      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;


      $query_args = [];

			$jobs = new OsProcessJobModel();
			$count_jobs = new OsProcessJobModel();

      // TABLE SEARCH FILTERS
      $filter = isset($this->params['filter']) ? $this->params['filter'] : false;
      if($filter){
        if(!empty($filter['process_id'])) $query_args['process_id'] = $filter['process_id'];
        if(!empty($filter['status'])) $query_args['status'] = $filter['status'];
        if(!empty($filter['object_id'])) $query_args['object_id'] = $filter['object_id'];

        if(!empty($filter['to_run_after_utc_from']) && !empty($filter['to_run_after_utc_to'])){
          $query_args['to_run_after_utc >='] = $filter['to_run_after_utc_from'];
          $query_args['to_run_after_utc <='] = $filter['to_run_after_utc_to'];
        }

        if(isset($filter['event_type']) && !empty($filter['event_type'])){
          $jobs->select(LATEPOINT_TABLE_PROCESS_JOBS.'.*, '.LATEPOINT_TABLE_PROCESSES.'.event_type');
          $jobs->join(LATEPOINT_TABLE_PROCESSES, [LATEPOINT_TABLE_PROCESSES.'.id' => LATEPOINT_TABLE_PROCESS_JOBS.'.process_id']);
          $count_jobs->select(LATEPOINT_TABLE_PROCESS_JOBS.'.*, '.LATEPOINT_TABLE_PROCESSES.'.event_type');
          $count_jobs->join(LATEPOINT_TABLE_PROCESSES, [LATEPOINT_TABLE_PROCESSES.'.id' => LATEPOINT_TABLE_PROCESS_JOBS.'.process_id']);
          $query_args[LATEPOINT_TABLE_PROCESSES.'.event_type'] = $filter['event_type'];
        }
      }

			$total_jobs = $count_jobs->where($query_args)->count();

			$jobs = $jobs->where($query_args)->order_by('created_at desc')->set_limit($per_page);
      if($page_number > 1){
        $jobs = $jobs->set_offset(($page_number - 1) * $per_page);
      }


			$this->vars['jobs'] = $jobs->get_results_as_models();

      $this->vars['current_page_number'] = $page_number;
      $this->vars['per_page'] = $per_page;
      $total_pages = ceil($total_jobs / $per_page);
			$this->vars['total_pages'] = $total_pages;
			$this->vars['total_records'] = $total_jobs;

      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $total_jobs);


      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_jobs]);
		}

  }

endif;