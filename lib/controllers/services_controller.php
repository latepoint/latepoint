<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsServicesController' ) ) :


	class OsServicesController extends OsController {


		function __construct() {
			parent::__construct();

			$this->views_folder            = LATEPOINT_VIEWS_ABSPATH . 'services/';
			$this->vars['page_header']     = OsMenuHelper::get_menu_items_by_id( 'services' );
			$this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id( 'services' );
			$this->vars['breadcrumbs'][]   = array( 'label' => __( 'Services', 'latepoint' ), 'link' => OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'services', 'index' ) ) );
		}

		/*
		  Edit service
		*/

		public function edit_form() {
			$service_id = $this->params['id'];

			$this->vars['pre_page_header'] = '';
			$this->vars['page_header']     = __( 'Edit Service', 'latepoint' );
			$this->vars['breadcrumbs'][]   = array( 'label' => __( 'Edit Service', 'latepoint' ), 'link' => false );


			$service            = new OsServiceModel( $service_id );
			$service_categories = new OsServiceCategoryModel();
			$agents             = new OsAgentModel();
			$locations          = new OsLocationModel();


			$this->vars['service']                       = $service;
			$this->vars['service_categories_for_select'] = $service_categories->index_for_select();
			$this->vars['agents']                        = $agents->get_results_as_models();
			$this->vars['locations']                     = $locations->get_results_as_models();

			$custom_work_periods               = OsWorkPeriodsHelper::get_work_periods( new \LatePoint\Misc\Filter( [ 'service_id' => $service_id, 'exact_match' => true ] ), true );
			$this->vars['custom_work_periods'] = $custom_work_periods;
			$this->vars['is_custom_schedule']  = ( $custom_work_periods && ( count( $custom_work_periods ) > 0 ) );

			$this->format_render( __FUNCTION__ );
		}


		/*
		  New service form
		*/

		public function new_form() {
			$this->vars['pre_page_header'] = '';
			$this->vars['page_header']     = __( 'New Service', 'latepoint' );
			$this->vars['breadcrumbs'][]   = array( 'label' => __( 'Create New Service', 'latepoint' ), 'link' => false );


			$service            = new OsServiceModel();
			$service_categories = new OsServiceCategoryModel();
			$agents             = new OsAgentModel();
			$locations          = new OsLocationModel();


			$service->bg_color = $service->generate_new_bg_color();

			if ( isset( $this->params['service_category_id'] ) ) {
				$service->category_id = $this->params['service_category_id'];
			}

			$this->vars['service']                       = $service;
			$this->vars['service_categories_for_select'] = $service_categories->index_for_select();
			$this->vars['agents']                        = $agents->get_results_as_models();
			$this->vars['locations']                     = $locations->get_results_as_models();

			$this->vars['custom_work_periods'] = [];
			$this->vars['is_custom_schedule']  = false;


			$this->format_render( __FUNCTION__ );
		}


		/*
		  Index of services
		*/

		public function index() {
			$service_categories = new OsServiceCategoryModel();
			$service_categories = $service_categories->order_by( 'order_number asc' )->get_results_as_models();


			$this->vars['service_categories'] = $service_categories;

			$services                             = new OsServiceModel();
			$this->vars['uncategorized_services'] = $services->should_be_active()->where( array(
				'category_id' => [
					'OR' => [
						0,
						'IS NULL'
					]
				]
			) )->order_by( 'order_number asc' )->get_results_as_models();
			$this->vars['disabled_services']      = $services->where( [ 'status' => LATEPOINT_SERVICE_STATUS_DISABLED ] )->get_results_as_models();

			$this->format_render( __FUNCTION__ );
		}


		/*
		  Create service
		*/

		public function create() {
			$this->update();
		}


		/*
		  Update service
		*/

		public function update() {
			$is_new_record = ( isset( $this->params['service']['id'] ) && $this->params['service']['id'] ) ? false : true;

			$this->check_nonce( $is_new_record ? 'new_service' : 'edit_service_' . $this->params['service']['id'] );
			$service = new OsServiceModel();
			$service->set_data( $this->params['service'] );
			$extra_response_vars = array();

			$this->params['service']['durations'] = isset( $this->params['service']['durations'] ) ? $this->params['service']['durations'] : [];
			$this->params['service']['agents']    = isset( $this->params['service']['agents'] ) ? $this->params['service']['agents'] : [];

			if ( $service->save() && $service->save_durations( $this->params['service']['durations'] ) && $service->save_agents_and_locations( $this->params['service']['agents'] ) ) {
				if ( $is_new_record ) {
					$response_html = __( 'Service Created. ID:', 'latepoint' ) . $service->id;
					OsActivitiesHelper::create_activity( array( 'code' => 'service_create', 'service_id' => $service->id ) );
				} else {
					$response_html = __( 'Service Updated. ID:', 'latepoint' ) . $service->id;
					OsActivitiesHelper::create_activity( array( 'code' => 'service_update', 'service_id' => $service->id ) );
				}
				$status = LATEPOINT_STATUS_SUCCESS;
				// save schedules
				if ( $this->params['is_custom_schedule'] == 'on' ) {
					$service->save_custom_schedule( $this->params['work_periods'] );
				} elseif ( $this->params['is_custom_schedule'] == 'off' ) {
					$service->delete_custom_schedule();
				}
				$extra_response_vars['record_id'] = $service->id;
				do_action( 'latepoint_service_saved', $service, $is_new_record, $this->params['service'] );
			} else {
				$response_html = $service->get_error_messages();
				$status        = LATEPOINT_STATUS_ERROR;
			}
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html ) + $extra_response_vars );
			}
		}


		/*
		  Delete service
		*/

		public function destroy() {
			if ( filter_var( $this->params['id'], FILTER_VALIDATE_INT ) ) {
				$this->check_nonce( 'destroy_service_' . $this->params['id'] );
				$service = new OsServiceModel( $this->params['id'] );
				if ( $service->delete() ) {
					$status        = LATEPOINT_STATUS_SUCCESS;
					$response_html = __( 'Service Removed', 'latepoint' );
				} else {
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'Error Removing Service', 'latepoint' );
				}
			} else {
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = __( 'Error Removing Service', 'latepoint' );
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html ) );
			}
		}

	}


endif;