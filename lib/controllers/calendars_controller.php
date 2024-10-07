<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCalendarsController' ) ) :


	class OsCalendarsController extends OsController {

		private $booking;

		function __construct() {
			parent::__construct();
			$this->views_folder          = LATEPOINT_VIEWS_ABSPATH . 'calendars/';
			$this->vars['page_header']   = OsMenuHelper::get_menu_items_by_id( 'calendar' );
			$this->vars['breadcrumbs'][] = array( 'label' => __( 'Appointments', 'latepoint' ), 'link' => OsRouterHelper::build_link( [ 'calendars', 'pending_approval' ] ) );
		}


		public function view() {
			$this->vars['page_header'] = '';

			$today_date = new OsWpDateTime( 'today' );


			$services  = ( new OsServiceModel() )->filter_allowed_records()->should_be_active()->get_results_as_models();
			$locations = ( new OsLocationModel() )->filter_allowed_records()->should_be_active()->get_results_as_models();
			$agents    = ( new OsAgentModel() )->filter_allowed_records()->should_be_active()->get_results_as_models();

			// extract groups from services
			$service_categories        = new OsServiceCategoryModel();
			$service_categories        = $service_categories->get_results_as_models();
			$categorized_services_list = [];
			// uncategorized
			$categorized_services_list['category_none'] = [ 'name' => __( 'Uncategorized', 'latepoint' ), 'items' => [] ];

			if ( $service_categories ) {
				foreach ( $service_categories as $service_category ) {
					$categorized_services_list[ 'category_' . $service_category->id ] = [ 'name' => $service_category->name, 'items' => [] ];
				}
			}
			foreach ( $services as $service ) {
				if ( $service->category_id ) {
					$categorized_services_list[ 'category_' . $service->category_id ]['items'][] = $service;
				} else {
					$categorized_services_list['category_none']['items'][] = $service;
				}
			}

			$this->vars['categorized_services_list'] = $categorized_services_list;

			$default_calendar_settings = [
				'view'                         => OsAuthHelper::get_current_user()->get_wp_user_meta( 'latepoint_calendar_view', 'day' ),
				'target_date_string'           => $today_date->format( 'Y-m-d' ),
				'show_service_ids'             => OsUtilHelper::get_array_of_ids_from_array_of_models( $services ),
				'show_agent_ids'               => OsUtilHelper::get_array_of_ids_from_array_of_models( $agents ),
				'show_location_ids'            => OsUtilHelper::get_array_of_ids_from_array_of_models( $locations ),
				'overlay_service_availability' => LATEPOINT_VALUE_OFF,
				'availability_service_id'      => '',
				'selected_agent_id'            => '' // used for weekly calendar only
			];
			$calendar_settings         = ! empty( $this->params['calendar_settings'] ) ? array_merge( $default_calendar_settings, $this->params['calendar_settings'] ) : $default_calendar_settings;
			if ( $default_calendar_settings['view'] != $calendar_settings['view'] ) {
				// update default view for this user
				OsAuthHelper::get_current_user()->update_wp_user_meta( 'latepoint_calendar_view', $calendar_settings['view'] );
			}


			if ( $agents ) {
				// show only agents that offer services and locations that are selected to be shown
				$connected_agent_ids       = OsConnectorHelper::get_connected_object_ids( 'agent_id', [
					'service_id'  => $calendar_settings['show_service_ids'],
					'location_id' => $calendar_settings['show_location_ids']
				] );
				$connected_agents          = [];
				$connected_show_agents_ids = [];
				foreach ( $agents as $agent ) {
					if ( ! in_array( $agent->id, $connected_agent_ids ) || ! in_array( $agent->id, $calendar_settings['show_agent_ids'] ) ) {
						continue;
					}
					$connected_agents[]          = $agent;
					$connected_show_agents_ids[] = $agent->id;
				}
				$calendar_settings['show_agent_ids'] = $connected_show_agents_ids;
				$agents                              = $connected_agents;
				if ( $agents && ( empty( $calendar_settings['selected_agent_id'] ) || ! in_array( $calendar_settings['selected_agent_id'], $connected_show_agents_ids ) ) ) {
					$calendar_settings['selected_agent_id'] = $agents[0]->id;
				}
			}

			// CHECK IF BOOKABLE RESOURCES EXIST
			if ( empty( $services ) || empty( $agents ) || empty( $locations ) ) {
				if ( $this->get_return_format() == 'json' ) {
					$response_html = $this->render( $this->views_folder . 'missing_resources.php', 'none' );
					$this->send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ] );
				} else {
					$this->format_render( 'missing_resources' );
					exit();
				}
			}

			$selected_service_for_availability = false;
			if ( $calendar_settings['overlay_service_availability'] == LATEPOINT_VALUE_ON && ! empty( $calendar_settings['availability_service_id'] ) ) {
				foreach ( $services as $service ) {
					if ( $service->id == $calendar_settings['availability_service_id'] ) {
						$selected_service_for_availability = clone $service;
						break;
					}
				}
			}


			if ( $calendar_settings['view'] == 'list' ) {
				$per_page               = OsSettingsHelper::get_number_of_records_per_page();
				$bookings               = new OsBookingModel();
				$bookings               = $bookings->get_upcoming_bookings( $calendar_settings['show_agent_ids'], false, $calendar_settings['show_service_ids'], $calendar_settings['show_location_ids'], $per_page );
				$this->vars['bookings'] = $bookings;

				$calendar_start = ( new OsWpDateTime( 'now' ) );
				$calendar_end   = false;

				$top_date_label = OsUtilHelper::translate_months( $calendar_start->format( 'F' ), false );
			} else {
				switch ( $calendar_settings['view'] ) {
					case 'day':
						$calendar_start                             = new OsWpDateTime( $calendar_settings['target_date_string'] );
						$calendar_end                               = new OsWpDateTime( $calendar_settings['target_date_string'] );
						$this->vars['day_view_calendar_min_height'] = OsSettingsHelper::get_day_calendar_min_height();

						$prev_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( '-1 day' );
						$next_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( '+1 day' );
						$top_date_label   = OsUtilHelper::translate_months( $calendar_start->format( 'F j' ), false );
						break;
					case 'week':
						$calendar_start   = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'monday this week' );
						$calendar_end     = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'sunday this week' );
						$top_date_label   = OsUtilHelper::translate_months( $calendar_start->format( 'F' ), false ) . ' ' . $calendar_start->format( 'j' ) . ' - ' . OsUtilHelper::translate_months( $calendar_end->format( 'F' ), false ) . ' ' . $calendar_end->format( 'j' );
						$prev_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( '-7 days' );
						$next_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( '+7 days' );
						break;
					case 'month':
						$calendar_start   = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'first day of this month' );
						$calendar_end     = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'last day of this month' );
						$top_date_label   = OsUtilHelper::translate_months( $calendar_start->format( 'F' ), false );
						$prev_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'first day of previous month' );
						$next_target_date = ( new OsWpDateTime( $calendar_settings['target_date_string'] ) )->modify( 'first day of next month' );
						break;
				}
				$booking_request              = new \LatePoint\Misc\BookingRequest();
				$booking_request->agent_id    = $calendar_settings['show_agent_ids'];
				$booking_request->service_id  = $calendar_settings['show_service_ids'];
				$booking_request->location_id = $calendar_settings['show_location_ids'];

				if ( $selected_service_for_availability ) {
					$booking_request->service_id = $selected_service_for_availability->id;
					$booking_request->duration   = $selected_service_for_availability->duration; // TODO add capacity and duration select box and POST params if multiple durations in a service
					$timeblock_interval          = $selected_service_for_availability->get_timeblock_interval();
				} else {
					$timeblock_interval = OsSettingsHelper::get_default_timeblock_interval();
				}


				$resources = OsResourceHelper::get_resources_grouped_by_day( $booking_request, $calendar_start, $calendar_end );

				// if user wants to overlay availability for a specific service - we need to create a separate set of resources
				// for the work boundaries, since the original one is only querying that service and not all other "shown" services,
				// we want to show start and work time for all shown services, not just the selected one for the availability overlay
				// we need to check if there is a single "shown" service - in this case we don't need a separate call for work boundaries
				if ( count( $calendar_settings['show_service_ids'] ) > 1 && $selected_service_for_availability ) {
					$booking_request_for_shown_services             = clone $booking_request;
					$booking_request_for_shown_services->service_id = $calendar_settings['show_service_ids'];
					$resources_for_work_boundaries                  = OsResourceHelper::get_resources_grouped_by_day( $booking_request_for_shown_services, $calendar_start, $calendar_end );
					$work_boundaries                                = OsResourceHelper::get_work_boundaries_for_groups_of_resources( $resources_for_work_boundaries );
				} else {
					$work_boundaries = OsResourceHelper::get_work_boundaries_for_groups_of_resources( $resources );
				}

				$work_time_periods_grouped_by_date_and_agent = [];
				$bookings_grouped_by_date_and_agent          = [];
				foreach ( $agents as $agent ) {
					for ( $day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify( '+1 day' ) ) {
						// fill in all days and agents bookings with blanks
						$bookings_grouped_by_date_and_agent[ $day_date->format( 'Y-m-d' ) ][ $agent->id ]          = [];
						$work_time_periods_grouped_by_date_and_agent[ $day_date->format( 'Y-m-d' ) ][ $agent->id ] = [];
						$work_boundaries_grouped_by_date_and_agent[ $day_date->format( 'Y-m-d' ) ][ $agent->id ]   = OsResourceHelper::get_work_boundaries_for_groups_of_resources( [ $resources[ $day_date->format( 'Y-m-d' ) ] ] );
					}
				}
				$filter = new \LatePoint\Misc\Filter( [
					'date_from'   => $calendar_start->format( 'Y-m-d' ),
					'date_to'     => $calendar_end->format( 'Y-m-d' ),
					'service_id'  => $calendar_settings['show_service_ids'],
					'agent_id'    => $calendar_settings['show_agent_ids'],
					'location_id' => $calendar_settings['show_location_ids'],
					'statuses'    => OsCalendarHelper::get_booking_statuses_to_display_on_calendar()
				] );
				$filter = OsRolesHelper::filter_allowed_records_from_arguments_or_filter( $filter );

				// loop bookings to fill in array
				$bookings = OsBookingHelper::get_bookings( $filter, true );
				foreach ( $bookings as $booking ) {
					$bookings_grouped_by_date_and_agent[ $booking->start_date ][ $booking->agent_id ][] = $booking;
				}

				// loop resources to fill in work periods array
				foreach ( $resources as $day => $daily_resources ) {
					foreach ( $daily_resources as $daily_resource ) {
						$work_time_periods_grouped_by_date_and_agent[ $day ][ $daily_resource->agent_id ] = array_merge( $work_time_periods_grouped_by_date_and_agent[ $day ][ $daily_resource->agent_id ], $daily_resource->work_time_periods );
					}
				}

				$this->vars['bookings_grouped_by_date_and_agent']        = $bookings_grouped_by_date_and_agent;
				$this->vars['work_boundaries_grouped_by_date_and_agent'] = $work_boundaries_grouped_by_date_and_agent;
				$this->vars['timeblock_interval']                        = $timeblock_interval;

				$this->vars['booking_request']                             = $booking_request;
				$this->vars['work_time_periods_grouped_by_date_and_agent'] = $work_time_periods_grouped_by_date_and_agent;
				$this->vars['prev_target_date']                            = $prev_target_date;
				$this->vars['next_target_date']                            = $next_target_date;
				$this->vars['resources']                                   = $resources;
				$this->vars['work_boundaries']                             = $work_boundaries;
				$this->vars['work_total_minutes']                          = $work_boundaries->end_time - $work_boundaries->start_time;
			}

			$this->vars['calendar_start'] = $calendar_start;
			$this->vars['calendar_end']   = $calendar_end;

			$this->vars['date_format'] = OsSettingsHelper::get_readable_date_format( true );

			$this->vars['target_date']    = new OsWpDateTime( $calendar_settings['target_date_string'] );
			$this->vars['today_date']     = $today_date;
			$this->vars['top_date_label'] = $top_date_label;

			$this->vars['agents']    = $agents;
			$this->vars['services']  = $services;
			$this->vars['locations'] = $locations;

			$this->vars['calendar_settings'] = $calendar_settings;

			if ( $this->get_return_format() == 'json' ) {
				$response_html = $this->render( $this->views_folder . 'scopes/_' . $calendar_settings['view'], 'none' );
				$this->send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html, 'top_date_label' => $top_date_label ] );
			} else {
				$this->format_render( __FUNCTION__ );
			}
		}


		public function load_monthly_calendar_days_only() {
			$target_date               = new OsWpDateTime( $this->params['target_date_string'] );
			$this->vars['target_date'] = $target_date;

			$this->set_layout( 'none' );
			$this->format_render( __FUNCTION__ );
		}



	}

endif;