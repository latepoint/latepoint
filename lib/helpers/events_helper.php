<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */


class OsEventsHelper {

	public static function week_grid(OsWpDateTime $calendar_start, OsWpDateTime $calendar_end, OsWpDateTime $target_date, array $services_with_resources, array $filter = []){
		$now_datetime = OsTimeHelper::now_datetime_object();
		$html = '';


		$start_time = 540;
		$end_time = 1080;

		$min_service_duration = false;

		$day_slots = [];
		for ($day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify('+1 day')) {
			$day_slots[$day_date->format('Y-m-d')] = [];
			foreach ($services_with_resources as $service_resources) {
				if (empty($service_resources['resources'][$day_date->format('Y-m-d')])) continue;
				$clean_periods = [];
				foreach ($service_resources['resources'][$day_date->format('Y-m-d')] as $resource) {
					if (!empty($filter['agent_id']) && $resource->agent_id != $filter['agent_id']) continue;
					if (!empty($filter['service_id']) && $resource->service_id != $filter['service_id']) continue;
					if (!empty($filter['location_id']) && $resource->location_id != $filter['location_id']) continue;
					foreach ($resource->work_time_periods as $time_period) {
						$clean_periods[] = $time_period;
					}
				}
				$merged_periods = \LatePoint\Misc\TimePeriod::merge_periods($clean_periods);


				foreach ($merged_periods as $time_period) {
					if(($time_period->end_time - $time_period->start_time) > 0){
						$min_service_duration = $min_service_duration ? min($min_service_duration, ($time_period->end_time - $time_period->start_time)) : $time_period->end_time - $time_period->start_time;
					}
					$service_box_data = ['model' => $service_resources['model'], 'time_period' => $time_period];
					$day_slots[$day_date->format('Y-m-d')][$time_period->start_time][] = $service_box_data;
					$start_time = min($start_time, $time_period->start_time);
					$end_time = max($end_time, $time_period->end_time);
				}
			}
			ksort($day_slots[$day_date->format('Y-m-d')]);
		}

		$html.= '<div class="latepoint-calendar-week">';

		// make it start a little earlier so that there is some space between day labels and first bookings
		$start_time = $start_time - 60;

		// make sure timeline starts at hour marks
		if($start_time % 60 != 0) $start_time = $start_time - ($start_time % 60);
		$day_duration = $end_time - $start_time;

		$hour_height_css = '';
		if($min_service_duration && $min_service_duration < 60){
			$default_hour_height  = 44;
			$hour_height = ceil(60 / $min_service_duration) * $default_hour_height;
			$hour_height_css = 'style="height: '.$hour_height.'px"';
		}

		for ($day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify('+1 day')) {



			$is_today = ($day_date->format('Y-m-d') == $now_datetime->format('Y-m-d'));
			$is_day_in_past = ($day_date->format('Y-m-d') < $now_datetime->format('Y-m-d'));
			$is_target_month = ($day_date->format('m') == $target_date->format('m'));
			$is_next_month = ($day_date->format('m') > $target_date->format('m'));
			$is_prev_month = ($day_date->format('m') < $target_date->format('m'));


			$day_class = '';
			if ($is_today) $day_class.= ' os-today';
			if ($is_day_in_past) $day_class.= ' os-day-passed';
			if ($is_target_month) $day_class.= ' os-month-current';
			if ($is_next_month) $day_class.= ' os-month-next';
			if ($is_prev_month) $day_class.= ' os-month-prev';
			$day_class.= ' day-weekday-' . $day_date->format('N');

			$html.= '<div class="le-day-wrapper ' . $day_class . '">';


			$html.= '<div class="le-day-weekday-wrapper">';
			$html.= '<div class="le-day-weekday">'.OsBookingHelper::get_weekday_name_by_number($day_date->format('N')).'</div>';
			if ($day_date->format('d') == '1') $html.= '<div class="os-day-month">' . OsUtilHelper::get_month_name_by_number($day_date->format('n')) . '</div>';
			$html.= '<div class="le-day-number" 
					data-os-params="' . OsUtilHelper::build_os_params(['target_date_string' => $day_date->format('Y-m-d'), 'filter' => $filter]) . '" 
					data-os-output-target="lightbox" 
					data-os-action="' . OsRouterHelper::build_route_name('events', 'events_day_view') . '">' . $day_date->format('j') .
				'</div>';
			$html.= '</div>';

			$html.= '<div class="le-day-schedule-wrapper">';
			$html.='<div class="day-schedule-timeslots">';
				for($i = $start_time; $i<$end_time; $i+= 60){
					$html.='<div class="day-schedule-timeslot-wrapper">';
					$html.='<div class="day-schedule-timeslot" '.$hour_height_css.'><div class="day-schedule-timeslot-value">'.OsTimeHelper::minutes_to_hours_and_minutes($i, null, true, true).'</div></div>';
					$html.='</div>';

				}
				foreach($day_slots[$day_date->format('Y-m-d')] as $day_slot_services){
					foreach($day_slot_services as $day_slot_service){
						$slot_duration = $day_slot_service['time_period']->end_time - $day_slot_service['time_period']->start_time;
						$start_position = ($day_slot_service['time_period']->start_time - $start_time) / $day_duration * 100;
						$height = $slot_duration / $day_duration * 100;
						$extra_attrs = 'style="top: '.$start_position.'%; height: '.$height.'%"';
						$html.= self::event_service_box_html($day_slot_service['model'], $day_slot_service['time_period'], $filter, $day_date, $extra_attrs);
					}
				}
			$html.= '</div>';
			$html.= '</div>';
			$html.= '</div>';
		}
		$html.= '</div>';
		return $html;
	}

	public static function month_grid(OsWpDateTime $calendar_start, OsWpDateTime $calendar_end, OsWpDateTime $target_date, array $services_with_resources, array $filter = []){
		$now_datetime = OsTimeHelper::now_datetime_object();
		$html = '';
		$html.= '<div class="latepoint-calendar-month">';

		$weekdays = OsBookingHelper::get_weekdays_arr();
		foreach ($weekdays as $weekday_number => $weekday_name) {
			$html.= '<div class="le-weekday weekday-' . ($weekday_number + 1) . '">' . $weekday_name . '</div>';
		}

		for ($day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify('+1 day')) {
			$is_today = ($day_date->format('Y-m-d') == $now_datetime->format('Y-m-d'));
			$is_day_in_past = ($day_date->format('Y-m-d') < $now_datetime->format('Y-m-d'));
			$is_target_month = ($day_date->format('m') == $target_date->format('m'));
			$is_next_month = ($day_date->format('m') > $target_date->format('m'));
			$is_prev_month = ($day_date->format('m') < $target_date->format('m'));

			$day_class = '';
			if ($is_today) $day_class.= ' os-today';
			if ($is_day_in_past) $day_class.= ' os-day-passed';
			if ($is_target_month) $day_class.= ' os-month-current';
			if ($is_next_month) $day_class.= ' os-month-next';
			if ($is_prev_month) $day_class.= ' os-month-prev';
			$day_class.= ' day-weekday-' . $day_date->format('N');

			$html.= '<div class="le-day-wrapper ' . $day_class . '">';

			if ($day_date->format('d') == '1') $html.= '<div class="os-day-month">' . OsUtilHelper::get_month_name_by_number($day_date->format('n')) . '</div>';
			$html.= '<div class="le-day-number" 
					data-os-params="' . OsUtilHelper::build_os_params(['target_date_string' => $day_date->format('Y-m-d'), 'filter' => $filter]) . '" 
					data-os-output-target="lightbox" 
					data-os-action="' . OsRouterHelper::build_route_name('events', 'events_day_view') . '">' . $day_date->format('j') .
				'</div>';
			$day_services_html = [];
			foreach ($services_with_resources as $service_resources) {
				if (empty($service_resources['resources'][$day_date->format('Y-m-d')])) continue;
				$clean_periods = [];
				foreach ($service_resources['resources'][$day_date->format('Y-m-d')] as $resource) {
					if(!empty($filter['agent_id']) && $resource->agent_id != $filter['agent_id']) continue;
					if(!empty($filter['service_id']) && $resource->service_id != $filter['service_id']) continue;
					if(!empty($filter['location_id']) && $resource->location_id != $filter['location_id']) continue;
					foreach ($resource->work_time_periods as $time_period) {
						$clean_periods[] = $time_period;
					}
				}
				$merged_periods = \LatePoint\Misc\TimePeriod::merge_periods($clean_periods);
				foreach ($merged_periods as $time_period) {
					$day_services_html[$time_period->start_time][] = self::event_service_box_html($service_resources['model'], $time_period, $filter, $day_date);
				}
			}
			ksort($day_services_html);
			$daily_count = 0;
			foreach ($day_services_html as $day_service_html) {
				if($daily_count >= 4) {
					// translators: %d is the number of services
					$more = sprintf(__('+ %d more', 'latepoint'), count($day_services_html) - 4);
					$html.= '<div class="le-more-services" 
					data-os-params="' . esc_attr(OsUtilHelper::build_os_params(['target_date_string' => $day_date->format('Y-m-d'), 'filter' => $filter])) . '" 
					data-os-output-target="lightbox" 
					data-os-action="' . esc_attr(OsRouterHelper::build_route_name('events', 'events_day_view')) . '">'.esc_html($more).'</div>';
					break;
				}
				$daily_count++;
				$html.= implode('', $day_service_html);
			}
			$html.= '</div>';
		}
		$html.= '</div>';
		return $html;
	}

	public static function event_service_box_html(OsServiceModel $service, \LatePoint\Misc\TimePeriod $time_period, array $filter, OsWpDateTime $start_date, string $extra_attrs = ''){
		$service_box_html = '';
		$selected_attributes = '';

		if(!empty($filter['agent_id'])) $selected_attributes.= ' data-selected-agent="'.esc_attr($filter['agent_id']).'" ';
		if(!empty($filter['location_id'])) $selected_attributes.= ' data-selected-location="'.esc_attr($filter['location_id']).'" ';
		if(!empty($filter['show_agents'])) $selected_attributes.= ' data-show-agents="'.esc_attr($filter['show_agents']).'" ';
		if(!empty($filter['show_locations'])) $selected_attributes.= ' data-show-locations="'.esc_attr($filter['show_locations']).'" ';


		if(OsEventsHelper::is_service_slot_passed($service, $start_date, $time_period)){
			$service_box_html.= '<div class="le-service-wrapper is-passed" '.$extra_attrs.'><div class="is-passed-message">'.esc_html__('This event has passed', 'latepoint').'</div>';
		}else{
			$start_time_attr = (($time_period->end_time - $time_period->start_time) > $service->duration) ? '' : 'data-selected-start-time="' . esc_attr($time_period->start_time) . '"';
			$service_box_html.= '<div class="le-service-wrapper os_trigger_booking" '.$extra_attrs.' '.$selected_attributes.'
			data-selected-service="' . esc_attr($service->id) . '" 
			data-selected-start-date="' . esc_attr($start_date->format('Y-m-d')) . '" 
			'.$start_time_attr.' 
			data-hide-side-panel="yes">';
		}
		$service_box_html.= '<div class="le-service-inner">';
		$service_box_html.= '<div class="le-color-elem" style="background-color: ' . esc_attr($service->bg_color) . '"></div>';
		$service_box_html.= '<div class="le-service-name">' . esc_html($service->name) . '</div>';
		$service_box_html.= '<div class="le-service-time-period">' . OsTimeHelper::minutes_to_hours_and_minutes($time_period->start_time) . ' - ' . OsTimeHelper::minutes_to_hours_and_minutes($time_period->end_time) . '</div>';
		$service_box_html.= '</div>';
		$service_box_html.= '</div>';
		return $service_box_html;
	}

	public static function events_grid(OsWpDateTime $target_date, array $filter = [], $range_type = 'week', array $restrictions = []): string {
		$html = '';

		switch($range_type){
			case 'week':
				# Get bounds for a month of a targeted day
				$calendar_start = clone $target_date;
				// set monday as start of the week
				$start_of_the_week_day = 1;
				// figure out what week day is target and then find start and end of that week
				$shift = $calendar_start->format('N') - $start_of_the_week_day;
				if($shift) $calendar_start->modify('-'.$shift.' days');
				$calendar_end = clone $calendar_start;
				$calendar_end->modify('+6 days');

				$next_period = clone $calendar_start;
				$next_period->modify('+7 days');

				$prev_period = clone $calendar_start;
				$prev_period->modify('-7 days');
				break;
			case 'month':
				# Get bounds for a month of a targeted day
				$calendar_start = clone $target_date;
				$calendar_start->modify('first day of this month');
				$calendar_end = clone $target_date;
				$calendar_end->modify('last day of this month');

				$next_period = clone $target_date;
				$next_period->modify('first day of next month');

				$prev_period = clone $target_date;
				$prev_period->modify('first day of previous month');


				$weekday_for_first_day_of_month = $calendar_start->format('N') - 1;
				$weekday_for_last_day_of_month = $calendar_end->format('N') - 1;


				if ($weekday_for_first_day_of_month > 0) {
					$calendar_start->modify('-' . $weekday_for_first_day_of_month . ' days');
				}

				if ($weekday_for_last_day_of_month < 6) {
					$days_to_add = 6 - $weekday_for_last_day_of_month;
					$calendar_end->modify('+' . $days_to_add . ' days');
				}
				break;
		}


		$now_datetime = OsTimeHelper::now_datetime_object();


		$services = new OsServiceModel();
		if(!empty($restrictions['show_services'])) $services->where(['id' => explode(',', $restrictions['show_services'])]);
		$services = $services->should_be_active()->get_results_as_models();

		$services_with_resources = [];

		foreach ($services as $service) {
			$services_with_resources['service_' . $service->id] = ['model' => $service, 'work_periods' => []];
			$booking_request = new \LatePoint\Misc\BookingRequest([
				'service_id' => $service->id
			]);
			$services_with_resources['service_' . $service->id]['resources'] = OsResourceHelper::get_resources_grouped_by_day($booking_request, $calendar_start, $calendar_end);
		}

		$clean_filter = [];
		foreach($filter as $filter_key => $filter_value){
			if(!empty($filter_value)) $clean_filter[$filter_key] = $filter_value;
		}

		$filter_class = empty($clean_filter) ? '' : 'show-filters';

		$clean_filter = array_merge($clean_filter, $restrictions);
		$agent_restriction = !empty($restrictions['show_agents']) ? explode(',', $restrictions['show_agents']) : [];
		$service_restriction = !empty($restrictions['show_services']) ? explode(',', $restrictions['show_services']) : [];

		$html.= '<div class="latepoint-calendar-wrapper '.$filter_class.'" data-route-name="' . OsRouterHelper::build_route_name('events', 'load_calendar_events') . '" data-target-date="' . $target_date->format('Y-m-d') . '">';
		$html.= '<div class="latepoint-calendar-controls-wrapper">';
		$html.= OsFormHelper::hidden_field('restrictions', wp_json_encode($restrictions));
			$html.= '<div class="latepoint-calendar-controls">';
			if($range_type == 'week'){
				if($calendar_start->format('M') == $calendar_end->format('M')){
					$html.= '<div class="le-week">' . $calendar_start->format('M j') . ' - ' . $calendar_end->format('j') .'</div>';
				}else{
					$html.= '<div class="le-week">' . $calendar_start->format('M j') . ' - ' . $calendar_end->format('M j') .'</div>';
				}
			}
			if($range_type == 'month'){
				$html.= '<div class="le-month">' . $target_date->format('F') . '</div>';
			}
			$html.= '<div class="le-range-selector">'.OsFormHelper::select_field('calendar_range_type', false, ['week' => 'Weekly', 'month' => 'Monthly'], $range_type).'</div>';
			$html.= '<div class="le-filter le-filter-trigger"><i class="latepoint-icon latepoint-icon-ui-47"></i><span>' . __('Filters', 'latepoint') . '</span></div>';
			$html.= OsFormHelper::hidden_field('target_date_string', $target_date->format('Y-m-d'));
			$html.= '<div class="le-navigation-wrapper">';
			$html.= '<div class="le-navigation">
									<div class="le-navigation-button le-navigation-trigger" data-target-date="' . $prev_period->format('Y-m-d') . '"><i class="latepoint-icon latepoint-icon-arrow-left"></i></div>
									<div class="le-today le-navigation-trigger" data-target-date="' . $now_datetime->format('Y-m-d') . '">' . __('Today', 'latepoint') . '</div>
									<div class="le-navigation-button le-navigation-trigger" data-target-date="' . $next_period->format('Y-m-d') . '"><i class="latepoint-icon latepoint-icon-arrow-right"></i></div>
								</div>';
			$html.= '</div>';
			$html.= '</div>';
			$html.= '<div class="latepoint-calendar-filters">';
				$html.= '<div class="le-filters-label">'.__('Show:', 'latepoint').'</div>';
				$html.= OsFormHelper::select_field('filter[agent_id]', false, array_merge([['value' => '', 'label' => __('All Agents', 'latepoint')]], OsAgentHelper::get_agents_list(false, $agent_restriction)), $clean_filter['agent_id'] ?? '');
				$html.= OsFormHelper::select_field('filter[service_id]', false, array_merge([['value' => '', 'label' => __('All Services', 'latepoint')]], OsServiceHelper::get_services_list(false, $service_restriction)), $clean_filter['service_id'] ?? '');
			$html.= '</div>';
		$html.= '</div>';

		switch($range_type){
			case 'week':
				$html.= self::week_grid($calendar_start, $calendar_end, $target_date, $services_with_resources, $clean_filter);
				break;
			case 'month':
				$html.= self::month_grid($calendar_start, $calendar_end, $target_date, $services_with_resources, $clean_filter);
				break;
		}

		$html.= '</div>';
		return $html;
	}

	private static function is_service_slot_passed(OsServiceModel $service, OsWpDateTime $start_date, \LatePoint\Misc\TimePeriod $time_period) :bool {
		$now_datetime = OsTimeHelper::now_datetime_object();
		$is_day_in_past = ($start_date->format('Y-m-d') < $now_datetime->format('Y-m-d'));
		$is_today = ($start_date->format('Y-m-d') == $now_datetime->format('Y-m-d'));

		try{
			$is_passed = ($is_day_in_past || $is_today && (OsWpDateTime::os_createFromFormat('Y-m-d H:i:s', $start_date->format('Y-m-d').' '.OsTimeHelper::minutes_to_army_hours_and_minutes($time_period->end_time - $service->duration).':00') < $now_datetime));
		}catch(Exception $e){
			$is_passed = false;
		}
		return $is_passed;
	}
}