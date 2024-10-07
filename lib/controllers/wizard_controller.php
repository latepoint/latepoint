<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsWizardController' ) ) :


	class OsWizardController extends OsController {

		var $steps_info, $steps_in_order;

		protected $show_next_btn = false,
			$show_prev_btn = false;


		function __construct() {
			parent::__construct();

			$this->views_folder        = LATEPOINT_VIEWS_ABSPATH . 'wizard/';
			$this->vars['page_header'] = __( 'Wizard', 'latepoint' );

			$this->set_layout( 'wizard' );
			$this->steps_info     = array(
				'default_agent' => array(
					'show_in_sidemenu' => true,
					'name'             => __( 'Setup Notifications', 'latepoint' )
				),
				'agents'        => array( 'show_in_sidemenu' => true, 'name' => __( 'Create Agents', 'latepoint' ) ),
				'intro'         => array( 'show_in_sidemenu' => false, 'name' => __( 'Intro', 'latepoint' ) ),
				'services'      => array( 'show_in_sidemenu' => true, 'name' => __( 'Add Services', 'latepoint' ) ),
				'work_periods'  => array(
					'show_in_sidemenu' => true,
					'name'             => __( 'Set Working Hours', 'latepoint' )
				),
				'info'          => array(
					'show_in_sidemenu' => true,
					'name'             => __( 'Fill Business Info', 'latepoint' )
				),
				'complete'      => array( 'show_in_sidemenu' => true, 'name' => __( 'Setup Complete', 'latepoint' ) ),
			);
			$this->steps_in_order = array( 'intro', 'default_agent', 'services', 'work_periods', 'complete' );

			$this->vars['steps_in_order'] = $this->steps_in_order;
			$this->vars['steps_info']     = $this->steps_info;
		}

		function save_service() {
			$service = new OsServiceModel();
			$service->set_data( $this->params['service'] );

			if ( $service->save() && $service->save_agents_and_locations( $this->params['service']['agents'] ) ) {
				$this->vars['current_step_code'] = 'agents';
				$this->step_services();
				$response_html = $this->render( $this->get_view_uri( 'steps/_list_services' ) );
				$status        = LATEPOINT_STATUS_SUCCESS;
			} else {
				$response_html = $service->get_error_messages();
				$status        = LATEPOINT_STATUS_ERROR;
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status'        => $status,
				                         'message'       => $response_html,
				                         'show_prev_btn' => true,
				                         'show_next_btn' => $this->show_next_btn
				) );
			}
		}

		function save_agent() {
			$agent = new OsAgentModel();
			$agent->set_data( $this->params['agent'] );
			if ( $agent->save() ) {
				$this->vars['current_step_code'] = 'agents';
				$this->step_agents();
				$response_html = $this->render( $this->get_view_uri( 'steps/_list_agents' ) );
				$status        = LATEPOINT_STATUS_SUCCESS;
			} else {
				$response_html = $agent->get_error_messages();
				$status        = LATEPOINT_STATUS_ERROR;
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status'        => $status,
				                         'message'       => $response_html,
				                         'show_prev_btn' => $this->show_prev_btn,
				                         'show_next_btn' => $this->show_next_btn
				) );
			}
		}


		function setup() {
			$current_step_code  = $this->steps_in_order[0];
			$step_function_name = 'step_' . $current_step_code;
			self::$step_function_name();

			add_option( 'latepoint_wizard_visited', true );

			$this->vars['current_step_code']    = $current_step_code;
			$this->vars['current_step_number']  = array_search( $current_step_code, $this->steps_in_order );
			$this->vars['step_file_to_include'] = 'steps/_' . $current_step_code . '.php';

			$this->format_render( __FUNCTION__ );
		}

		function next_step() {
			$this->show_prev_btn = true;
			$this->show_next_btn = true;

			// Check if a valid step_code name
			if ( isset( $this->steps_info[ $this->params['current_step_code'] ] ) ) {
				$current_step_code = $this->params['current_step_code'];
			} else {
				$current_step_code = $this->steps_in_order[0];
			}


			$process_step_function_name = 'process_step_' . $current_step_code;
			self::$process_step_function_name();

			$new_current_step_code = $this->steps_in_order[ array_search( $current_step_code, $this->steps_in_order ) + 1 ];
			if ( array_search( $new_current_step_code, $this->steps_in_order ) <= 1 ) {
				$this->show_prev_btn = false;
			}

			$step_function_name = 'step_' . $new_current_step_code;
			self::$step_function_name();

			$this->vars['current_step_code']   = $new_current_step_code;
			$this->vars['current_step_number'] = array_search( $new_current_step_code, $this->steps_in_order );
			$this->format_render( 'steps/_' . $new_current_step_code, array(), array( 'step_code'     => $new_current_step_code,
			                                                                          'show_prev_btn' => $this->show_prev_btn,
			                                                                          'show_next_btn' => $this->show_next_btn
			) );
		}

		function prev_step() {
			// Check if a valid step_code name
			if ( isset( $this->steps_info[ $this->params['current_step_code'] ] ) ) {
				$current_step_code = $this->params['current_step_code'];
			} else {
				$current_step_code = $this->steps_in_order[0];
			}

			$new_current_step_code = ( array_search( $current_step_code, $this->steps_in_order ) > 0 ) ? $this->steps_in_order[ array_search( $current_step_code, $this->steps_in_order ) - 1 ] : $this->steps_in_order[0];
			$this->show_prev_btn   = array_search( $new_current_step_code, $this->steps_in_order ) > 0;

			if ( array_search( $new_current_step_code, $this->steps_in_order ) <= 1 ) {
				$this->show_prev_btn = false;
			}

			$step_function_name = 'step_' . $new_current_step_code;
			self::$step_function_name();

			$this->vars['current_step_code']   = $new_current_step_code;
			$this->vars['current_step_number'] = array_search( $new_current_step_code, $this->steps_in_order );
			$this->format_render( 'steps/_' . $new_current_step_code, array(), array( 'step_code'     => $new_current_step_code,
			                                                                          'show_prev_btn' => $this->show_prev_btn,
			                                                                          'show_next_btn' => $this->show_next_btn
			) );
		}

		function load_step() {
			// Check if a valid step_code name
			if ( isset( $this->steps_info[ $this->params['current_step_code'] ] ) ) {
				$current_step_code = $this->params['current_step_code'];
			} else {
				$current_step_code = $this->steps_in_order[0];
			}

			$step_function_name = 'step_' . $current_step_code;
			self::$step_function_name();

			$this->vars['current_step_code']   = $current_step_code;
			$this->vars['current_step_number'] = array_search( $current_step_code, $this->steps_in_order );
			$this->format_render( 'steps/_' . $current_step_code, array(), array( 'step_code' => $current_step_code ) );
		}

		function add_or_edit_agent() {
			$agents               = new OsAgentModel();
			$this->vars['agents'] = $agents->get_results_as_models();

			$agent = new OsAgentModel();
			if ( ! empty( $this->params['id'] ) && is_numeric( $this->params['id'] ) ) {
				$agent->load_by_id( $this->params['id'] );
			}
			$this->vars['agent'] = $agent;
			$this->format_render( 'steps/_form_agent', array(), array() );
		}

		function add_or_edit_service() {
			$services               = new OsServiceModel();
			$this->vars['services'] = $services->get_results_as_models();

			$service = new OsServiceModel();
			if ( isset( $this->params['id'] ) && is_numeric( $this->params['id'] ) ) {
				$service->load_by_id( $this->params['id'] );
			}
			$agents             = new OsAgentModel();
			$service_categories = new OsServiceCategoryModel();

			$this->vars['service_categories_for_select'] = $service_categories->index_for_select();
			$this->vars['agents']                        = $agents->get_results_as_models();
			$this->vars['location']                      = OsLocationHelper::get_default_location();

			$this->vars['service'] = $service;
			$this->format_render( 'steps/_form_service', array(), array() );
		}


		function step_services() {
			$services               = new OsServiceModel();
			$services               = $services->get_results_as_models();
			$this->show_prev_btn    = false;
			$this->vars['services'] = $services;
			$this->vars['location'] = OsLocationHelper::get_default_location();
			$agents                = new OsAgentModel();
			$this->vars['agents']  = $agents->get_results_as_models();
			if ( ! $services ) {
				$service               = new OsServiceModel();
				$this->vars['service'] = $service;
				$this->show_next_btn   = false;
			} else {
				$this->show_next_btn = true;
				$this->show_prev_btn = true;
			}
		}

		function step_agents() {
			$agents               = new OsAgentModel();
			$agents               = $agents->get_results_as_models();
			$this->vars['agents'] = $agents;
			$this->show_prev_btn  = false;
			if ( ! $agents ) {
				$agent               = new OsAgentModel();
				$this->vars['agent'] = $agent;
				$this->show_next_btn = false;
			} else {
				$this->show_next_btn = true;
			}
		}


		function step_default_agent(){

			$this->vars['agent'] = OsAgentHelper::get_default_agent();
			$this->show_next_btn = true;
	}

		function step_work_periods() {
			$work_periods                  = OsWorkPeriodsHelper::get_work_periods( new \LatePoint\Misc\Filter() );
			$working_periods_with_weekdays = array();
			if ( $work_periods ) {
				foreach ( $work_periods as $work_period ) {
					$working_periods_with_weekdays[ 'day_' . $work_period->week_day ][] = $work_period;
				}
			}
			$this->vars['working_periods_with_weekdays'] = $working_periods_with_weekdays;
		}

		function step_intro() {
			$this->show_next_btn = true;
		}


		function step_settings() {

		}

		function step_complete() {
			$this->show_next_btn = false;
			$this->show_prev_btn = false;
		}


		function process_step_agents() {

		}

		function process_step_services() {

		}

		function process_step_intro() {

		}

		function process_step_default_agent() {

			$default_agent = OsAgentHelper::get_default_agent();
			if ( ! $default_agent->is_new_record() ) {
				$default_agent->set_data( $this->params['agent'] );
				$default_agent->save();
			}

		}

		function process_step_work_periods() {
			$work_periods_form_data = $this->params['work_periods'];
			OsWorkPeriodsHelper::save_work_periods( $work_periods_form_data );
		}

		function process_step_info() {

		}


	}


endif;
