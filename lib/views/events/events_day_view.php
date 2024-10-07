<?php
/** @var $target_date OsWpDateTime */
/** @var $filter array */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="le-day-view-wrapper" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('events', 'events_day_view')); ?>">
	<div class="le-day-info-section">
		<div class="le-day-info">
			<div class="le-day-number"><?php echo esc_html($target_date->format('j')); ?></div>
			<div class="le-day-month"><?php echo esc_html($target_date->format('M')); ?></div>
		</div>
		<div class="le-day-filters-wrapper">
			<div class="le-day-heading"><?php esc_html_e('Daily Schedule', 'latepoint'); ?></div>
			<div class="le-day-filters">
				<?php echo OsFormHelper::hidden_field('target_date_string', $target_date->format('Y-m-d')); ?>
				<?php echo OsFormHelper::select_field('filter[agent_id]', false, array_merge([['value' => '', 'label' => __('All Agents', 'latepoint')]], OsAgentHelper::get_agents_list()), $filter['agent_id'] ?? ''); ?>
				<?php echo OsFormHelper::select_field('filter[service_id]', false, array_merge([['value' => '', 'label' => __('All Services', 'latepoint')]], OsServiceHelper::get_services_list()), $filter['service_id'] ?? ''); ?>
			</div>
		</div>
		<a href="#" class="latepoint-lightbox-close"><i class="latepoint-icon latepoint-icon-x"></i></a>
	</div>
	<div class="le-day-schedule-wrapper">
		<?php
		$services_with_resources = [];

		$clean_filter = [];
		foreach($filter as $filter_key => $filter_value){
			if(!empty($filter_value)) $clean_filter[$filter_key] = $filter_value;
		}

		$services = new OsServiceModel();
		$services = $services->should_be_active()->get_results_as_models();

		foreach ($services as $service) {
			$services_with_resources['service_' . $service->id] = ['model' => $service, 'work_periods' => []];
			$booking_request = new \LatePoint\Misc\BookingRequest([
				'service_id' => $service->id
			]);
			$services_with_resources['service_' . $service->id]['resources'] = OsResourceHelper::get_resources_grouped_by_day($booking_request, $target_date);
		}

		$start_time = 540;
		$end_time = 1080;
		$min_service_duration = false;

		$day_slots = [];
		foreach ($services_with_resources as $service_resources) {
			if (empty($service_resources['resources'][$target_date->format('Y-m-d')])) continue;
			$clean_periods = [];
			foreach ($service_resources['resources'][$target_date->format('Y-m-d')] as $resource) {
				if(!empty($clean_filter['agent_id']) && $resource->agent_id != $clean_filter['agent_id']) continue;
				if(!empty($clean_filter['service_id']) && $resource->service_id != $clean_filter['service_id']) continue;
				if(!empty($clean_filter['location_id']) && $resource->location_id != $clean_filter['location_id']) continue;
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
				$day_slots[$time_period->start_time][] = $service_box_data;
				$start_time = min($start_time, $time_period->start_time);
				$end_time = max($end_time, $time_period->end_time);
			}
		}
		ksort($day_slots);
		// make sure timeline starts at hour marks
		if($start_time % 60 != 0) $start_time = $start_time - ($start_time % 60);
		$is_today = $target_date->format('Y-m-d') == OsTimeHelper::today_date('Y-m-d');
		$is_day_in_past = $target_date->format('Y-m-d') < OsTimeHelper::today_date('Y-m-d');
		echo '<div class="day-schedule-timeslots">';
			$hour_height_css = '';
			if($min_service_duration && $min_service_duration < 60){
				$default_hour_height  = 44;
				$hour_height = ceil(60 / $min_service_duration) * $default_hour_height;
				$hour_height_css = 'style="height: '.esc_attr($hour_height).'px"';
			}
			for($i = $start_time; $i<$end_time; $i+= 60){
				echo '<div class="day-schedule-timeslot-wrapper">';
				echo '<div class="day-schedule-timeslot" '.$hour_height_css.'><div class="day-schedule-timeslot-value">'.esc_html(OsTimeHelper::minutes_to_hours_and_minutes($i, null, true, true)).'</div></div>';
				echo '</div>';
			}
			$day_duration = $end_time - $start_time;
			foreach($day_slots as $day_slot_services){
				foreach($day_slot_services as $day_slot_service){
					$slot_duration = $day_slot_service['time_period']->end_time - $day_slot_service['time_period']->start_time;
					$start_position = ($day_slot_service['time_period']->start_time - $start_time) / $day_duration * 100;
					$height = $slot_duration / $day_duration * 100;
					$extra_attrs = 'style="top: '.esc_attr($start_position).'%; height: '.esc_attr($height).'%"';
					echo OsEventsHelper::event_service_box_html($day_slot_service['model'], $day_slot_service['time_period'], $clean_filter, $target_date, $extra_attrs);
				}
			}
		echo '</div>';

		?>
	</div>
</div>

