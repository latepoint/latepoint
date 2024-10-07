<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsActivitiesController' ) ) :


	class OsActivitiesController extends OsController {


		function __construct() {
			parent::__construct();

			$this->views_folder            = LATEPOINT_VIEWS_ABSPATH . 'activities/';
			$this->vars['page_header']     = OsMenuHelper::get_menu_items_by_id( 'processes' );
			$this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id( 'processes' );
			$this->vars['breadcrumbs'][]   = array(
				'label' => __( 'Activities', 'latepoint' ),
				'link'  => OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'activities', 'index' ) )
			);

		}

		public function clear_all() {
			$this->check_nonce( 'clear_activities' );
			global $wpdb;
			$wpdb->query( $wpdb->prepare("TRUNCATE TABLE %i", esc_sql(LATEPOINT_TABLE_ACTIVITIES) ));
			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array(
					'status'  => LATEPOINT_STATUS_SUCCESS,
					'message' => __( 'Activities log cleared', 'latepoint' )
				) );
			}
		}

		public function export() {
			$csv_filename = 'activities_log_' . OsUtilHelper::random_text() . '.csv';

			header( "Content-Type: text/csv" );
			header( "Content-Disposition: attachment; filename={$csv_filename}" );

			$labels_row = [
				__( 'Type', 'latepoint' ),
				__( 'Agent ID', 'latepoint' ),
				__( 'Booking ID', 'latepoint' ),
				__( 'Service ID', 'latepoint' ),
				__( 'Customer ID', 'latepoint' ),
				__( 'Location ID', 'latepoint' ),
				__( 'Action By User Type', 'latepoint' ),
				__( 'Action By User ID', 'latepoint' ),
				__( 'Date, Time', 'latepoint' ),
				__( 'Description', 'latepoint' )
			];


			$activities_data   = [];
			$activities_data[] = $labels_row;

			$activities     = new OsActivityModel();
			$activities_arr = $activities->order_by( 'created_at' )->get_results( ARRAY_A );

			if ( $activities_arr ) {
				foreach ( $activities_arr as $activity ) {
					$values_row        = [
						$activity['code'],
						$activity['agent_id'],
						$activity['booking_id'],
						$activity['service_id'],
						$activity['customer_id'],
						$activity['location_id'],
						$activity['initiated_by'],
						$activity['initiated_by_id'],
						$activity['created_at'],
						$activity['description'],
					];
					$activities_data[] = $values_row;
				}

			}

			OsCSVHelper::array_to_csv( $activities_data );

			return;
		}

		/*
			Index of activities
		*/

		public function index() {
			$per_page    = OsSettingsHelper::get_number_of_records_per_page();
			$page_number = isset( $this->params['page_number'] ) ? $this->params['page_number'] : 1;

			$activities                = new OsActivityModel();
			$count_activities          = new OsActivityModel();

			// TABLE SEARCH FILTERS
			$filter = isset( $this->params['filter'] ) ? $this->params['filter'] : false;

			$query_args = [];
			if ( $filter ) {
				if ( ! empty( $filter['code'] ) ) {
					$query_args['code'] = $filter['code'];
				}
				if ( ! empty( $filter['initiated_by_id'] ) ) {
					$query_args['initiated_by_id'] = $filter['initiated_by_id'];
				}

				if ( ! empty( $filter['created_at_from'] ) && ! empty( $filter['created_at_to'] ) ) {
					$query_args['created_at >='] = $filter['created_at_from'];
					$query_args['created_at <='] = $filter['created_at_to'];
				}
			}

			$total_activities = $count_activities->where( $query_args )->count();

			$activities = $activities->where( $query_args )->order_by( 'id desc' )->set_limit( $per_page );
			if ( $page_number > 1 ) {
				$activities = $activities->set_offset( ( $page_number - 1 ) * $per_page );
			}

			$this->vars['activities'] = $activities->get_results_as_models();

			$this->vars['total_activities']    = $total_activities;
			$this->vars['current_page_number'] = $page_number;
			$this->vars['per_page']            = $per_page;
			$total_pages                       = ceil( $total_activities / $per_page );
			$this->vars['total_pages']         = $total_pages;

			$this->vars['showing_from'] = ( ( $page_number - 1 ) * $per_page ) ? ( ( $page_number - 1 ) * $per_page ) : 1;
			$this->vars['showing_to']   = min( $page_number * $per_page, $total_activities );


			$this->vars['breadcrumbs'][] = array( 'label' => __( 'Index', 'latepoint' ), 'link' => false );

			$this->format_render( [
				'json_view_name' => '_table_body',
				'html_view_name' => __FUNCTION__
			], [], [
				'total_pages'   => $total_pages,
				'showing_from'  => $this->vars['showing_from'],
				'showing_to'    => $this->vars['showing_to'],
				'total_records' => $total_activities
			] );
		}

		public function destroy() {
			if ( filter_var( $this->params['id'], FILTER_VALIDATE_INT ) ) {

				$this->check_nonce( 'destroy_activity_' . $this->params['id'] );
				$activity = new OsActivityModel( $this->params['id'] );
				if ( $activity->delete() ) {
					$status        = LATEPOINT_STATUS_SUCCESS;
					$response_html = __( 'Activity Removed', 'latepoint' );
				} else {
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'Error Removing Activity', 'latepoint' );
				}
			} else {
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = __( 'Error Removing Activity', 'latepoint' );
			}

			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( array( 'status' => $status, 'message' => $response_html ) );
			}
		}

		public function view() {
			$activity = new OsActivityModel( $this->params['id'] );
			$data     = json_decode( $activity->description, true );

			$this->vars['activity_id']   = $activity->id;
			$this->vars['activity_name'] = $activity->name;
			$this->vars['activity_type'] = $activity->code;
			$this->vars['status']        = $data['status'] ?? '';

			$status_html = '';
			if ( ! empty( $data['status'] ) ) {
				$status_html = '<div class="status-item">' . __( 'Status:', 'latepoint' ) . ' <strong>' . $data['status'] . '</strong></div>';
				$status_html .= '<div class="status-item">' . __( 'Processed on:', 'latepoint' ) . ' <strong>' . $data['processed_datetime'] . '</strong></div>';
				if ( ! empty( $data['errors'] ) ) {
					$status_html .= '<div class="status-item">' . __( 'Errors:', 'latepoint' ) . '<strong>' . ( is_array( $data['errors'] ) ? implode( ', ', $data['errors'] ) : $data['errors'] ) . '</strong></div>';
				}
			}

			switch ( $activity->code ) {
				// orders
				case 'order_intent_updated':
					$link_to_order = $activity->order_id ? '<a href="#" ' . OsOrdersHelper::quick_order_btn_html( $activity->order_id ) . '>' . __( 'View Order', 'latepoint' ) . '</a>' : '';
					$meta_html     = '<div class="activity-preview-to">' . ( $link_to_order ? ( '<span class="os-value">' . $link_to_order . '</span>' ) : '' ) . '<span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html  = '<pre class="format-json">' . wp_json_encode( $data['order_data_vars'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'order_intent_created':
					$link_to_order = $activity->order_id ? '<a href="#" ' . OsOrdersHelper::quick_order_btn_html( $activity->order_id ) . '>' . __( 'View Order', 'latepoint' ) . '</a>' : '';
					$meta_html     = '<div class="activity-preview-to">' . ( $link_to_order ? ( '<span class="os-value">' . $link_to_order . '</span>' ) : '' ) . '<span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html  = '<pre class="format-json">' . wp_json_encode( $data['order_data_vars'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'order_intent_converted':
					$link_to_order = '<a href="#" ' . OsOrdersHelper::quick_order_btn_html( $activity->order_id ) . '>' . __( 'View Order', 'latepoint' ) . '</a>';
					$meta_html     = '<div class="activity-preview-to"><span class="os-value">' . $link_to_order . '</span><span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html  = '<pre class="format-json">' . wp_json_encode( $data['order_data_vars'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'order_created':
					$link_to_order = '<a href="#" ' . OsOrdersHelper::quick_order_btn_html( $activity->order_id ) . '>' . __( 'View Order', 'latepoint' ) . '</a>';
					$meta_html     = '<div class="activity-preview-to"><span class="os-value">' . $link_to_order . '</span><span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html  = '<pre class="format-json">' . wp_json_encode( $data['order_data_vars'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'order_updated':
					$link_to_order = '<a href="#" ' . OsOrdersHelper::quick_order_btn_html( $activity->order_id ) . '>' . __( 'View Order', 'latepoint' ) . '</a>';
					$meta_html     = '<div class="activity-preview-to"><span class="os-value">' . $link_to_order . '</span><span class="os-label">' . __( 'Updated On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$changes       = OsUtilHelper::compare_model_data_vars( $data['order_data_vars']['new'], $data['order_data_vars']['old'] );
					$content_html  = '<pre class="format-json">' . wp_json_encode( $changes, JSON_PRETTY_PRINT ) . '</pre>';
					break;

				// bookings
				case 'booking_change_status':
					$link_to_order = $activity->order_id ? '<a href="#" ' . OsBookingHelper::quick_booking_btn_html( $activity->booking_id ) . '>' . __( 'View Booking', 'latepoint' ) . '</a>' : '';
					$meta_html     = '<div class="activity-preview-to">' . ( $link_to_order ? ( '<span class="os-value">' . $link_to_order . '</span>' ) : '' ) . '<span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html  = '<div class="activity-preview-content">' . $activity->description . '</div>';
					break;
				case 'booking_created':
					$link_to_booking = '<a href="#" ' . OsBookingHelper::quick_booking_btn_html( $activity->booking_id ) . '>' . __( 'View Booking', 'latepoint' ) . '</a>';
					$meta_html       = '<div class="activity-preview-to"><span class="os-value">' . $link_to_booking . '</span><span class="os-label">' . __( 'Created On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$content_html    = '<pre class="format-json">' . wp_json_encode( $data['booking_data_vars'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'booking_updated':
					$link_to_booking = '<a href="#" ' . OsBookingHelper::quick_booking_btn_html( $activity->booking_id ) . '>' . __( 'View Booking', 'latepoint' ) . '</a>';
					$meta_html       = '<div class="activity-preview-to"><span class="os-value">' . $link_to_booking . '</span><span class="os-label">' . __( 'Updated On:', 'latepoint' ) . '</span><span class="os-value">' . $activity->nice_created_at . '</div>';
					$changes         = OsUtilHelper::compare_model_data_vars( $data['booking_data_vars']['new'], $data['booking_data_vars']['old'] );
					$content_html    = '<pre class="format-json">' . wp_json_encode( $changes, JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'email_sent':
					$meta_html    = '<div class="activity-preview-subject">' . esc_html( $data['extra_data']['subject'] ) . '</div>';
					$meta_html    .= '<div class="activity-preview-to"><span class="os-label">' . __( 'To:', 'latepoint' ) . '</span><span class="os-value">' . esc_html( $data['to'] ) . '</div>';
					$content_html = '<div class="activity-preview-content">' . $data['content'] . '</div>';
					break;
				case 'sms_sent':
					$meta_html    = '<div class="activity-preview-to"><span class="os-label">' . __( 'To:', 'latepoint' ) . '</span><span class="os-value">' . esc_html( $data['to'] ) . '</div>';
					$content_html = '<div class="activity-preview-content">' . $data['content'] . '</div>';
					break;
				case 'http_request':
					$meta_html    = '<div class="activity-preview-to"><span class="os-label">' . __( 'URL:', 'latepoint' ) . '</span><span class="os-value"><a href="#" target="_blank">' . esc_html( $data['to'] ) . '</a></div>';
					$content_html = '<pre class="format-json">' . wp_json_encode( $data['content'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
				case 'process_job_run':
					$job          = new OsProcessJobModel( $data['job_id'] );
					$name         = $job->process->name . ', ID: ' . $job->process->id;
					$meta_html    = '<div class="activity-preview-to"><span class="os-label">' . __( 'Process:', 'latepoint' ) . '</span><span class="os-value">' . esc_html( $name ) . '</div>';
					$content_html = '<pre class="format-json">' . $data['run_result'] . '</pre>';
					break;
				case 'error':
					$meta_html    = '<div class="activity-preview-to"><span class="os-label">' . __( 'Error Message:', 'latepoint' ) . '</span><span class="os-value">' . esc_html( $data['message'] ) . ' | ' . esc_html( $data['error_code'] ) . '</div>';
					$content_html = '<pre class="format-json">' . wp_json_encode( $data['extra_description'], JSON_PRETTY_PRINT ) . '</pre>';
					break;
			}

			$this->vars['content_html'] = $content_html ?? '';
			$this->vars['meta_html']    = $meta_html ?? '';
			$this->vars['status_html']  = $status_html ?? '';

			$this->vars = apply_filters( 'latepoint_activity_view_vars', $this->vars, $activity );

			$this->format_render( __FUNCTION__ );
		}
	}


endif;