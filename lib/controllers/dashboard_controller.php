<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsDashboardController' ) ) :


  class OsDashboardController extends OsController {

    private $booking;


    function __construct(){
      parent::__construct();

      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'dashboard/';
      $this->vars['page_header'] = __('Dashboard', 'latepoint');

    }


		public function pro_agents(){

		}

	  /*
			Index
		*/

    public function index(){
      $this->vars['page_header'] = false;

      ob_start();
      $this->widget_bookings_and_availability_timeline();
      $this->vars['widget_bookings_and_availability_timeline'] = ob_get_clean();

      ob_start();
      $this->widget_daily_bookings_chart();
      $this->vars['widget_daily_bookings_chart'] = ob_get_clean();

      ob_start();
      $this->widget_upcoming_appointments(3);
      $this->vars['widget_upcoming_appointments'] = ob_get_clean();

      $this->set_layout('admin');
      $this->format_render(__FUNCTION__);
    }

    public function widget_upcoming_appointments($limit = 3){
      $agents = new OsAgentModel();
      $services = new OsServiceModel();
      $bookings = new OsBookingModel();
      $locations = new OsLocationModel();

      $selected_agent_id = isset($this->params['agent_id']) ? OsAuthHelper::get_current_user()->check_if_allowed_record_id($this->params['agent_id'], 'agent') : false;
      $selected_service_id = isset($this->params['service_id']) ? OsAuthHelper::get_current_user()->check_if_allowed_record_id($this->params['service_id'], 'service') : false;
      $selected_location_id = isset($this->params['location_id']) ? OsAuthHelper::get_current_user()->check_if_allowed_record_id($this->params['location_id'], 'location') : false;

      $this->vars['upcoming_bookings'] = $bookings->get_upcoming_bookings($selected_agent_id, false, $selected_service_id, $selected_location_id, $limit);

      $this->vars['agents'] = $agents->should_be_active()->filter_allowed_records()->get_results_as_models();
      $this->vars['services'] = $services->should_be_active()->filter_allowed_records()->get_results_as_models();
      $this->vars['locations'] = $locations->should_be_active()->filter_allowed_records()->get_results_as_models();

      $this->vars['selected_agent_id'] = $selected_agent_id;
      $this->vars['selected_service_id'] = $selected_service_id;
      $this->vars['selected_location_id'] = $selected_location_id;


      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }



    public function widget_daily_bookings_chart($date_from = false, $date_to = false){
      if($date_from == false){
        $date_from = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('-1 week');
      }
      if($date_to == false){
        $date_to = isset($this->params['date_to']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_to']) : new OsWpDateTime('now');
      }


			$filter = new \LatePoint\Misc\Filter();
			$filter = OsRolesHelper::filter_allowed_records_from_arguments_or_filter($filter);

      if(!empty($this->params['agent_id'])) $filter->agent_id = $this->params['agent_id'];
      if(!empty($this->params['service_id'])) $filter->service_id = $this->params['service_id'];
      if(!empty($this->params['location_id'])) $filter->location_id = $this->params['location_id'];

			if(!OsRolesHelper::are_all_records_allowed()){
				if(!OsRolesHelper::are_all_records_allowed('agent')) $agent_id = OsRolesHelper::get_allowed_records('agent');
			}



      $daily_bookings = OsBookingHelper::get_total_bookings_per_day_for_period($date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $filter);

      $daily_chart_data = [];
      // fill data array with all the days
      for($day_date=clone $date_from; $day_date<=$date_to; $day_date->modify('+1 day')){
        $daily_chart_data[OsTimeHelper::get_nice_date_with_optional_year($day_date->format('Y-m-d'), false)] = 0;
      }
      // update the days with count of bookings
      foreach($daily_bookings as $bookings_for_day){
        $daily_chart_data[OsTimeHelper::get_nice_date_with_optional_year(gmdate( 'Y-m-d', strtotime($bookings_for_day->start_date)), false)] = $bookings_for_day->bookings_per_day;
      }

      $this->vars['total_bookings'] = OsBookingHelper::get_stat_for_period('bookings', $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $filter);
      $this->vars['total_price'] = OsBookingHelper::get_stat_for_period('price', $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $filter);
      $this->vars['total_duration'] = OsBookingHelper::get_stat_for_period('duration', $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $filter);
      $this->vars['total_new_customers'] = OsBookingHelper::get_new_customer_stat_for_period($date_from, $date_to, $filter);

      $day_difference = $date_from->diff($date_to);
      $day_difference = ($day_difference->d > 0) ? $day_difference->d : 1;

      $prev_date_from = clone $date_from;
      $prev_date_from->modify('-'.$day_difference.' days');
      $prev_date_to = clone $date_to;
      $prev_date_to->modify('-'.$day_difference.' days');

      $this->vars['prev_total_bookings'] = OsBookingHelper::get_stat_for_period('bookings', $prev_date_from->format('Y-m-d'), $prev_date_to->format('Y-m-d'), $filter);
      $this->vars['prev_total_price'] = OsBookingHelper::get_stat_for_period('price', $prev_date_from->format('Y-m-d'), $prev_date_to->format('Y-m-d'), $filter);
      $this->vars['prev_total_duration'] = OsBookingHelper::get_stat_for_period('duration', $prev_date_from->format('Y-m-d'), $prev_date_to->format('Y-m-d'), $filter);
      $this->vars['prev_total_new_customers'] = OsBookingHelper::get_new_customer_stat_for_period($prev_date_from, $prev_date_to, $filter);


      $agents = new OsAgentModel();
      $services = new OsServiceModel();
      $locations = new OsLocationModel();

      $this->vars['agents'] = $agents->should_be_active()->filter_allowed_records()->get_results_as_models();
      $this->vars['services'] = $services->should_be_active()->filter_allowed_records()->get_results_as_models();
      $this->vars['locations'] = $locations->should_be_active()->filter_allowed_records()->get_results_as_models();

      $this->vars['filter'] = $filter;

      $this->vars['date_from'] = $date_from->format('Y-m-d');
      $this->vars['date_to'] = $date_to->format('Y-m-d');

      $this->vars['daily_bookings_chart_labels_string'] = implode(',', array_keys($daily_chart_data));
      $this->vars['daily_bookings_chart_data_values_string'] = implode(',', array_values($daily_chart_data));

      $pie_labels = [];
      $pie_colors = [];
      $pie_values = [];
      $pie_chart_data = OsBookingHelper::get_stat_for_period('bookings', $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), $filter,'service_id');
      foreach($pie_chart_data as $pie_data){
        $service = new OsServiceModel($pie_data['service_id']);
        $pie_labels[] = $service->name;
        $pie_colors[] = $service->bg_color;
        $pie_values[] = $pie_data['stat'];
      }

      $this->vars['pie_chart_data'] = ['labels' => $pie_labels, 'colors' => $pie_colors, 'values' => $pie_values];

      $this->vars['date_period_string'] = OsTimeHelper::format_date_with_locale(OsSettingsHelper::get_readable_date_format(), $date_from).' - '.OsTimeHelper::format_date_with_locale(OsSettingsHelper::get_readable_date_format(), $date_to);

      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
    }



    public function widget_bookings_and_availability_timeline(){
      $target_date = isset($this->params['date_from']) ? OsWpDateTime::os_createFromFormat('Y-m-d', $this->params['date_from']) : new OsWpDateTime('now');

      $services = new OsServiceModel();
      $agents = new OsAgentModel();
      $locations = new OsLocationModel();

      $agents_models = $agents->should_be_active()->filter_allowed_records()->get_results_as_models();
      $services_models = $services->should_be_active()->filter_allowed_records()->get_results_as_models();
      $locations_models = $locations->should_be_active()->filter_allowed_records()->get_results_as_models();

      $this->vars['services']   = $services_models;
      $this->vars['locations']  = $locations_models;
      $this->vars['agents']     = $agents_models;

      if($services_models && !empty($this->params['service_id'])){
        $selected_service = $services->load_by_id($this->params['service_id']);
      }else{
        $selected_service = false;
      }


      if($locations_models){
        // show all locations option if agent can only be present at one place - because it means he does not have overlapping appointments on the calendar
        $default_location_id = OsSettingsHelper::is_on('one_location_at_time') ? false : $locations_models[0]->id;
        $selected_location_id = !empty($this->params['location_id']) ? $this->params['location_id'] : $default_location_id;
      }else{
        $selected_location_id = false;
      }

      $this->vars['selected_location'] = $selected_location_id ? new OsLocationModel($selected_location_id) : false;
      $this->vars['selected_location_id'] = $selected_location_id;

      $timeblock_interval = OsSettingsHelper::get_default_timeblock_interval();
      $selected_service_id = ($selected_service) ? $selected_service->id : false;

      $this->vars['selected_service'] = $selected_service;
      $this->vars['selected_service_id'] = $selected_service_id;



			// we are using two separate booking requests because the calendar on top has to generate availability timeline,
	    // which can only be generated if we know service to check for. The second booking request is used to retrieve
	    // shared resources for all services and locations (unless specific location is selected)
			$availability_booking_request = new \LatePoint\Misc\BookingRequest(['start_date' => $target_date->format('Y-m-d')]);
			$general_booking_request = new \LatePoint\Misc\BookingRequest(['start_date' => $target_date->format('Y-m-d')]);
			if($selected_location_id){
				$availability_booking_request->location_id = $selected_location_id;
				$general_booking_request->location_id = $selected_location_id;
			}
			if($selected_service){
				$availability_booking_request->service_id = $selected_service->id;
				// TODO add capacity and duration select box and POST params if multiple durations in a service
				$availability_booking_request->duration = $selected_service->duration;
				$timeblock_interval = $selected_service->get_timeblock_interval();
			}

      if(count($agents_models) == 1) {
				$availability_booking_request->agent_id = $agents_models[0]->id;
				$general_booking_request->agent_id = $agents_models[0]->id;
      }

			$settings = ['accessed_from_backend' => true];
			$resources = OsResourceHelper::get_resources_grouped_by_day($general_booking_request, $target_date, $target_date, $settings);
			$availability_resources = OsResourceHelper::get_resources_grouped_by_day($availability_booking_request, $target_date, $target_date, $settings);
			$work_boundaries = OsResourceHelper::get_work_boundaries_for_resources($resources[$target_date->format('Y-m-d')]);
			$work_total_minutes = $work_boundaries->end_time - $work_boundaries->start_time;

      $this->vars['timeblock_interval'] = $timeblock_interval;

      $bookings = [];
			$agent_work_time_periods = [];
      if($agents_models){
        foreach($agents_models as $agent){
					$agent_work_time_periods[$agent->id] = [];
          $args = ['agent_id' => $agent->id];
          if($selected_location_id) $args['location_id'] = $selected_location_id;
					$args['status'] = OsCalendarHelper::get_booking_statuses_to_display_on_calendar();
					$args = OsRolesHelper::filter_allowed_records_from_arguments_or_filter($args);
          $bookings[$agent->id] = OsBookingHelper::get_bookings_for_date($target_date->format('Y-m-d'), $args);
        }
				foreach($availability_resources[$target_date->format('Y-m-d')] as $resource){
					if(isset($agent_work_time_periods[$resource->agent_id])) $agent_work_time_periods[$resource->agent_id] = array_merge($agent_work_time_periods[$resource->agent_id], $resource->work_time_periods);
				}
      }


      $this->vars['agent_work_time_periods'] = $agent_work_time_periods;

			$this->vars['availability_booking_request'] = $availability_booking_request;
			$this->vars['general_booking_request'] = $general_booking_request;

			$agents_resources = [];
			foreach ($agents_models as $agent) {
				$agent_booking_request = clone $availability_booking_request;
				$agent_booking_request->agent_id = $agent->id;
				$daily_resources = OsResourceHelper::get_resources_grouped_by_day($agent_booking_request, $target_date, null, $settings);
				$agents_resources['agent_' . $agent->id] = $daily_resources[$target_date->format('Y-m-d')];
			}
			$this->vars['agents_resources'] = $agents_resources;
			$this->vars['timeline_boundaries'] = OsResourceHelper::get_work_boundaries_for_groups_of_resources($agents_resources);

			$this->vars['work_total_minutes'] = $work_total_minutes;
      $this->vars['work_boundaries'] = $work_boundaries;
      $this->vars['show_day_info'] = OsAuthHelper::is_admin_logged_in();
      $this->vars['target_date_obj'] = $target_date;
      $this->vars['target_date'] = $target_date->format('Y-m-d');
      $this->vars['target_date_string'] = OsTimeHelper::get_readable_date($target_date);

			$this->vars['what_to_show'] = isset($this->params['what_to_show']) ? $this->params['what_to_show'] : 'appointments';


      $today_date = new OsWpDateTime('today');

			if($target_date->format('Y-m-d') == $today_date->format('Y-m-d')){
				$time_now = OsTimeHelper::now_datetime_object();
				$time_now_in_minutes = OsTimeHelper::convert_datetime_to_minutes($time_now);
				if(($time_now_in_minutes<=$work_boundaries->end_time && $time_now_in_minutes>=$work_boundaries->start_time)){
					$this->vars['time_now_label'] = $time_now->format(OsTimeHelper::get_time_format());
					// agents row with avatars and margin below - offset that needs to be accounted for when calculating "time now" indicator position
					$this->vars['time_now_indicator_left_offset'] = ($time_now_in_minutes - $work_boundaries->start_time) / $work_total_minutes * 100;
					$this->vars['show_today_indicator'] = true;
				}else{
					$this->vars['show_today_indicator'] = false;
				}
			}else{
				$this->vars['show_today_indicator'] = false;
			}

      $this->set_layout('none');

      $this->format_render(__FUNCTION__);
    }


  }

endif;