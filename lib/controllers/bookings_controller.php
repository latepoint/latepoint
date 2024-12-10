<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsBookingsController' ) ) :


  class OsBookingsController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'bookings/';
      $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('appointments');
      $this->vars['breadcrumbs'][] = array('label' => __('Appointments', 'latepoint'), 'link' => OsRouterHelper::build_link(['bookings', 'pending_approval'] ) );

    }

		public function view_booking_log(){
			$activities = new OsActivityModel();
			$activities = $activities->where(['booking_id' => absint($this->params['booking_id'])])->order_by('id desc')->get_results_as_models();

			$booking = new OsBookingModel($this->params['booking_id']);

			$this->vars['booking'] = $booking;
			$this->vars['activities'] = $activities;

      $this->format_render(__FUNCTION__);
		}

    public function grouped_bookings_quick_view(){
      if(!isset($this->params['booking_id'])) return false;

      $booking = new OsBookingModel($this->params['booking_id']);
      $this->vars['booking'] = $booking;

      $group_bookings = new OsBookingModel();
      $group_bookings = $group_bookings->where(['start_time' => $booking->start_time,
                              'start_date' => $booking->start_date,
                              'service_id' => $booking->service_id,
                              'location_id' => $booking->location_id,
                              'agent_id' => $booking->agent_id])->should_not_be_cancelled()->get_results_as_models();
      $total_attendees = 0;
      if($group_bookings){
        foreach($group_bookings as $group_booking){
          $total_attendees = $total_attendees + $group_booking->total_attendees;
        }
      }
      $this->vars['total_attendees'] = $total_attendees;
      $this->vars['group_bookings'] = $group_bookings;
      $this->format_render(__FUNCTION__);
    }

    public function pending_approval(){
      $this->vars['page_header'] = __('Pending Appointments', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Pending Appointments', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = OsSettingsHelper::get_number_of_records_per_page();
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;

      $bookings = new OsBookingModel();
      $query_args = ['status' => OsBookingHelper::get_booking_statuses_for_pending_page()];

			$bookings->where($query_args)->filter_allowed_records();

      $count_total_bookings = clone $bookings;
      $total_bookings = $count_total_bookings->count();

      $this->vars['bookings'] = $bookings->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();

      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['total_bookings'] = $total_bookings;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;

      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(__FUNCTION__);
    }

    public function customize_table(){
      $this->vars['selected_columns'] = OsSettingsHelper::get_selected_columns_for_bookings_table();
      $this->vars['available_columns'] = OsSettingsHelper::get_available_columns_for_bookings_table();

      $this->format_render(__FUNCTION__);
    }

    public function index(){

      $this->vars['page_header'] = false;
      $this->vars['breadcrumbs'][] = array('label' => __('All', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = OsSettingsHelper::get_number_of_records_per_page();
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;


      $bookings = new OsBookingModel();
      $query_args = [];

			$selected_columns = OsSettingsHelper::get_selected_columns_for_bookings_table();
			$available_columns = OsSettingsHelper::get_available_columns_for_bookings_table();

      $filter = $this->params['filter'] ?? false;

			$order_by = ['key' => 'booking_id', 'direction' => 'desc', 'column' => 'id'];

      // TABLE SEARCH FILTERS
      if($filter){
				if(!empty($filter['records_ordered_by_key']) && !empty($filter['records_ordered_by_direction'])){
					if(in_array($filter['records_ordered_by_direction'], ['desc', 'asc'])) $order_by['direction'] = $filter['records_ordered_by_direction'];
					$order_by['key'] = $filter['records_ordered_by_key'];
					switch($filter['records_ordered_by_key']){
						case 'booking_id':
							$order_by['column'] = 'id';
							break;
						case 'booking_start_datetime':
							$order_by['column'] = 'start_datetime_utc';
							break;
						case 'booking_created_on':
							$order_by['column'] = 'created_at';
							break;
						case 'booking_time_left':
							$order_by['column'] = 'start_datetime_utc';
							break;
					}
				}
        if(!empty($filter['service_id'])) $query_args['service_id'] = $filter['service_id'];
        if(!empty($filter['agent_id'])) $query_args['agent_id'] = $filter['agent_id'];
        if(!empty($filter['location_id'])) $query_args['location_id'] = $filter['location_id'];
        if(!empty($filter['time_status'])){
					switch($filter['time_status']){
						case 'now':
							$query_args['start_datetime_utc <='] = OsTimeHelper::now_datetime_utc_in_db_format();
							$query_args['end_datetime_utc >='] = OsTimeHelper::now_datetime_utc_in_db_format();
							break;
						case 'upcoming':
							$query_args['start_datetime_utc >='] = OsTimeHelper::now_datetime_utc_in_db_format();
							break;
						case 'past':
							$query_args['end_datetime_utc <='] = OsTimeHelper::now_datetime_utc_in_db_format();
							break;
					}
        }
        if(!empty($filter['status'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.status'] = $filter['status'];
        if(!empty($filter['payment_status'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.payment_status'] = $filter['payment_status'];
        if(!empty($filter['id'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.id'] = $filter['id'];
        if(!empty($filter['created_date_from']) && !empty($filter['created_date_to'])){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at >='] = $filter['created_date_from'].' 00:00:00';
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at <='] = $filter['created_date_to'].' 23:59:59';
        }
        if(!empty($filter['booking_date_from']) && !empty($filter['booking_date_to'])){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date >='] = $filter['booking_date_from'];
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date <='] = $filter['booking_date_to'];
        }

				$selected_columns = OsSettingsHelper::get_selected_columns_for_bookings_table();
        if(!empty($filter['customer'])){
	        $bookings->select( LATEPOINT_TABLE_BOOKINGS . '.*' );
	        if ( ! empty( $filter['customer']['full_name'] ) ) {
		        $bookings->select( LATEPOINT_TABLE_CUSTOMERS . '.first_name, ' . LATEPOINT_TABLE_CUSTOMERS . '.last_name' );
		        $query_args[ 'concat_ws(" ", ' . LATEPOINT_TABLE_CUSTOMERS . '.first_name,' . LATEPOINT_TABLE_CUSTOMERS . '.last_name) LIKE' ] = '%' . $filter['customer']['full_name'] . '%';
		        $this->vars['customer_name_query'] = $filter['customer']['full_name'];
	        }
	        $bookings->join( LATEPOINT_TABLE_CUSTOMERS, [ 'id' => LATEPOINT_TABLE_BOOKINGS . '.customer_id' ] );


	        if ( ! empty( $selected_columns['customer'] ) ) {
		        $meta_filter = [];
		        foreach ( $selected_columns['customer'] as $customer_column_key ) {
			        if ( isset( $available_columns['customer'][ $customer_column_key ] ) && ! empty( $filter['customer'][ $customer_column_key ] ) ) {
				        $meta_filter[$customer_column_key] = $filter['customer'][ $customer_column_key ];
			        }
		        }
		        if (count($meta_filter)){
					$customers_ids = OsMetaHelper::get_customers_by_filter($meta_filter) ?: [-1];
			        $query_args[ LATEPOINT_TABLE_CUSTOMERS . '.id  IN' ] = $customers_ids;
		        }
	        }
        }
				// filters for custom selected columns, only related to booking fields
	      if ( ! empty( $selected_columns['booking'] ) ) {
		      foreach ( $selected_columns['booking'] as $booking_column_key ) {
			      if ( ! empty( $available_columns['booking'][ $booking_column_key ] ) && ! empty( $filter[ $booking_column_key ] ) ) {
				      $query_args[ $booking_column_key . ' LIKE' ] = '%' . $filter[ $booking_column_key ] . '%';
			      }
		      }
	      }
      }

      $this->vars['agents_list'] = OsAgentHelper::get_agents_list(true);
      $this->vars['locations_list'] = OsLocationHelper::get_locations_list(true);
      $this->vars['services_list'] = OsServiceHelper::get_services_list(true);

      $this->vars['selected_columns'] = $selected_columns;
      $this->vars['available_columns'] = $available_columns;

			$this->vars['records_ordered_by_key'] = $order_by['key'];
			$this->vars['records_ordered_by_direction'] = $order_by['direction'];

      // OUTPUT CSV IF REQUESTED
      if(isset($this->params['download']) && $this->params['download'] == 'csv'){
        $csv_filename = 'all_bookings_'.OsUtilHelper::random_text().'.csv';
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$csv_filename}");

        $labels_row = [  __('ID', 'latepoint'), 
                              __('Service', 'latepoint'), 
                              __('Start Date & Time', 'latepoint'), 
                              __('Duration', 'latepoint'), 
                              __('Customer', 'latepoint'), 
                              __('Customer Phone', 'latepoint'), 
                              __('Customer Email', 'latepoint'), 
                              __('Agent', 'latepoint'), 
                              __('Agent Phone', 'latepoint'), 
                              __('Agent Email', 'latepoint'), 
                              __('Status', 'latepoint'), 
                              __('Price', 'latepoint'),
                              __('Booked On', 'latepoint') ];


        $bookings_data = [];
        $bookings_data[] = $labels_row;


        $bookings_arr = $bookings->where($query_args)->filter_allowed_records()->order_by($order_by['column'].' '.$order_by['direction'])->get_results_as_models();

        if($bookings_arr){
          foreach($bookings_arr as $booking){
	          $order_item     = new OsOrderItemModel( $booking->order_item_id );
            $values_row = [  $booking->id,
                                  $booking->service->name, 
                                  $booking->nice_start_datetime,
                                  $booking->get_total_duration(), 
                                  $booking->customer->full_name, 
                                  $booking->customer->phone, 
                                  $booking->customer->email, 
                                  $booking->agent->full_name, 
                                  $booking->agent->phone, 
                                  $booking->agent->email, 
                                  $booking->nice_status,
						            OsMoneyHelper::format_price($order_item->get_total()),
                                  $booking->nice_created_at];
            $values_row = apply_filters('latepoint_booking_row_for_csv_export', $values_row, $booking, $this->params);
            $bookings_data[] = $values_row;
          }

        }

        $bookings_data = apply_filters('latepoint_bookings_data_for_csv_export', $bookings_data, $this->params);
        OsCSVHelper::array_to_csv($bookings_data);
        return;
      }

			$query_args = OsRolesHelper::filter_allowed_records_from_arguments_or_filter($query_args);
			$bookings->where($query_args)->filter_allowed_records();
      $count_total_bookings = clone $bookings;
      $total_bookings = $count_total_bookings->count();

      $this->vars['bookings'] = $bookings->set_limit($per_page)->set_offset($offset)->order_by($order_by['column'].' '.$order_by['direction'])->get_results_as_models();
      $this->vars['total_bookings'] = $total_bookings;
      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
			$this->vars['total_records'] = $total_bookings;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_bookings]);
    }

    function quick_availability(){

			$trigger_form_booking_id = $this->params['trigger_form_booking_id'];
			$trigger_form_order_item_id = $this->params['trigger_form_order_item_id'];

			$booking = OsOrdersHelper::create_booking_object_from_booking_data_form($this->params['order_items'][$trigger_form_order_item_id]['bookings'][$trigger_form_booking_id]);

      $calendar_start_date = isset($this->params['start_date']) ? new OsWpDateTime($this->params['start_date']) : new OsWpDateTime($booking->start_date);
      // show one more day before so the current selection does not look weird
      if(isset($this->params['previous_days'])){
        $calendar_end_date = clone $calendar_start_date;
        $calendar_start_date->modify('-30 days');
      }else{
        if(!isset($this->params['show_days_only'])) $calendar_start_date->modify('-1 day');
        $calendar_end_date = clone $calendar_start_date;
				$calendar_end_date->modify('+30 days');
      }
      
      if(OsAuthHelper::get_current_user()->is_single_record_allowed('agent')) $booking->agent_id = OsRolesHelper::get_allowed_records('agent')[0];

      $work_periods = OsWorkPeriodsHelper::get_work_periods(new \LatePoint\Misc\Filter(['date_from' => $calendar_start_date->format('Y-m-d'), 'date_to' => $calendar_end_date->format('Y-m-d'), 'service_id' => $booking->service_id, 'agent_id' => $booking->agent_id, 'location_id' => $booking->location_id]));
      $work_start_end = OsWorkPeriodsHelper::get_work_start_end_time($work_periods);

			$booking_request = \LatePoint\Misc\BookingRequest::create_from_booking_model($booking);
			$settings = [];
			$settings['accessed_from_backend'] = true;
			if(!$booking->is_new_record()) $settings['exclude_booking_ids'] = [$booking->id];
			$resources = OsResourceHelper::get_resources_grouped_by_day($booking_request, $calendar_start_date, $calendar_end_date, $settings);
			$work_boundaries = OsResourceHelper::get_work_boundaries_for_groups_of_resources($resources);

			$this->vars['trigger_form_booking_id'] = $trigger_form_booking_id;
			$this->vars['trigger_form_order_item_id'] = $trigger_form_order_item_id;

      $this->vars['booking'] = $booking;
      $this->vars['work_boundaries'] = $work_boundaries;
      $this->vars['show_days_only'] = isset($this->params['show_days_only']) ? true : false;
      
      $this->vars['timeblock_interval'] = $booking->service->get_timeblock_interval();
      $this->vars['calendar_start_date'] = $calendar_start_date;
      $this->vars['calendar_end_date'] = $calendar_end_date;
      $this->vars['booking_request'] = $booking_request;
      $this->vars['resources'] = $resources;

      $agents = new OsAgentModel();
      $this->vars['agents'] = $agents->filter_allowed_records()->get_results_as_models();

      $this->format_render(__FUNCTION__);
    }




    function change_status(){

	    if(filter_var($this->params['id'], FILTER_VALIDATE_INT)) {
		    $this->check_nonce('change_status_booking_' . $this->params['id']);
		    $booking_id = $this->params['id'];
		    $new_status = $this->params['status'];
		    $booking = new OsBookingModel($booking_id);
		    if (!OsRolesHelper::can_user_make_action_on_model_record($booking, 'edit')) exit;

		    if ($booking->update_status($new_status)) {
			    $status = LATEPOINT_STATUS_SUCCESS;
			    $response_html = __('Appointment Status Updated', 'latepoint');
		    } else {
			    $status = LATEPOINT_STATUS_ERROR;
			    $response_html = __('Error Updating Booking Status!', 'latepoint') . ' ' . implode(',', $booking->get_error_messages());
		    }
	    }else{
			    $status = LATEPOINT_STATUS_ERROR;
			    $response_html = __('Error Updating Booking Status! Invalid ID', 'latepoint');
	    }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

  }

endif;