<?php
/**
 * @var $booking_request \LatePoint\Misc\BookingRequest
 * @var $target_date OsWpDateTime
 * @var $today_date OsWpDateTime
 * @var $calendar_start OsWpDateTime
 * @var $calendar_end OsWpDateTime
 * @var $agents OsAgentModel[]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="daily-availability-calendar-wrapper">
	<a href="#" data-target-date="<?php echo esc_attr((clone $target_date)->modify('last day of previous month')->format('Y-m-d')); ?>" class="daily-calendar-action-navigation-btn"><i class="latepoint-icon latepoint-icon-chevron-left"></i></a>
	<div class="daily-availability-calendar horizontal-calendar">
		<?php
			$settings_for_single_month = [
				'active' => true,
				'layout' => 'horizontal',
				'accessed_from_backend' => false, // we don't want it to filter based on access, since we already filtered that internally in booking request
				'highlight_target_date' => true
			];
			OsCalendarHelper::generate_single_month($booking_request, $target_date, $settings_for_single_month);
		?>
	</div>
	<a href="#" data-target-date="<?php echo esc_attr((clone $target_date)->modify('first day of next month')->format('Y-m-d')); ?>" class="daily-calendar-action-navigation-btn"><i class="latepoint-icon latepoint-icon-chevron-right"></i></a>
</div>
<?php for($day_date=clone $calendar_start; $day_date<=$calendar_end; $day_date->modify('+1 day')){


	$show_today_indicator = false;
	if(($work_boundaries->start_time < $work_boundaries->end_time) && ($timeblock_interval > 0)) {
		$valid_work_hours = true;
		$total_periods = floor(($work_boundaries->end_time - $work_boundaries->start_time) / $timeblock_interval) + 1;

		// if standard height of 20px per period is not enought to fill the minimum calendar height use calculated height
		$default_period_height = 20;
		$period_height = (($total_periods * $default_period_height) < $day_view_calendar_min_height) ? ceil($day_view_calendar_min_height / $total_periods) : $default_period_height;
		$period_css = ($period_height != $default_period_height) ? "height: {$period_height}px;" : '';

		// decide if we need to show today indicator
		if($target_date->format('Y-m-d') == $today_date->format('Y-m-d')){
			$time_now = OsTimeHelper::now_datetime_object();
			$time_now_in_minutes = OsTimeHelper::convert_datetime_to_minutes($time_now);
			if(($time_now_in_minutes<=$work_boundaries->end_time && $time_now_in_minutes>=$work_boundaries->start_time)){
				$time_now_label = $time_now->format(OsTimeHelper::get_time_format());
				// agents row with avatars and margin below - offset that needs to be accounted for when calculating "time now" indicator position
				$agents_row_height = 70;
				$time_now_indicator_top_offset = ($period_height * ($total_periods - 1)) * (($time_now_in_minutes - $work_boundaries->start_time) / $work_total_minutes * 100) / 100 + $agents_row_height;
				$show_today_indicator = true;
			}
		}
	}else{
		$valid_work_hours = false;
	}
	?>
<div class="daily-agent-calendar-w <?php if(count($agents) > 5) echo 'make-scrollable'; ?>">
	<?php if($show_today_indicator) echo '<div class="current-time-indicator" style="top: '.esc_attr($time_now_indicator_top_offset).'px"><span>'.esc_html($time_now_label).'</span></div>'; ?>
	<div class="calendar-daily-agent-w">
		<?php if($valid_work_hours){ ?>

			<div class="calendar-hours">
				<div class="ch-hours">
					<div class="ch-filter">
						<span><?php esc_html_e('Agent', 'latepoint'); ?></span>
						<span><?php esc_html_e('Time', 'latepoint'); ?></span>
					</div>
					<?php for($minutes = $work_boundaries->start_time; $minutes <= $work_boundaries->end_time; $minutes+= $timeblock_interval){ ?>
						<?php
						$period_class = 'chh-period';
						$period_class.= (($minutes == $work_boundaries->end_time) || (($minutes + $timeblock_interval) > $work_boundaries->end_time)) ? ' last-period' : '';
						$period_class.= (($minutes % 60) == 0) ? ' chh-period-hour' : ' chh-period-minutes';
						echo '<div class="'.esc_attr($period_class).'" style="'.esc_attr($period_css).'"><span>'.esc_html(OsTimeHelper::minutes_to_hours_and_minutes($minutes, false, true, true)).'</span></div>';
						?>
					<?php } ?>
				</div>
				<div class="ch-agents">
					<div class="da-head-agents">
				  <?php foreach($agents as $agent){ ?>
						<div class="da-head-agent">
							<div class="da-head-agent-avatar" style="background-image: url(<?php echo esc_url($agent->get_avatar_url()); ?>)"></div>
							<a href="<?php echo esc_url(OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $agent->id])); ?>" class="da-head-agent-name"><?php echo esc_html($agent->full_name); ?></a>
						</div>
					<?php } ?>
					</div>
					<div class="da-agents-bookings">
					  <?php foreach($agents as $agent){
							$day_off_class = empty($work_time_periods_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id]) ? 'agent-has-day-off' : ''; ?>
							<div class="da-agent-bookings-and-periods">
								<div class="ch-day-periods ch-day-<?php echo esc_attr(strtolower($target_date->format('N'))); ?>">

									<?php for($minutes = $work_boundaries->start_time; $minutes <= $work_boundaries->end_time; $minutes+= $timeblock_interval){ ?>
										<?php
										$period_class = 'chd-period';
										if(!OsBookingHelper::is_minute_in_work_periods($minutes, $work_time_periods_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id])) $period_class.= ' chd-period-off ';
										$period_class.= (($minutes == $work_boundaries->end_time) || (($minutes + $timeblock_interval) > $work_boundaries->end_time)) ? ' last-period' : '';
										$period_class.= (($minutes % 60) == 0) ? ' chd-period-hour' : ' chd-period-minutes';
										$btn_data = [ 'start_time'=> $minutes,
																	'start_date' => $target_date->format('Y-m-d'),
																	'agent_id' => $agent->id,
																	'location_id' => is_array($booking_request->location_id) ? $booking_request->location_id[0] : $booking_request->location_id,
																	'service_id' => is_array($booking_request->service_id) ? $booking_request->service_id[0] : $booking_request->service_id ];
										$btn_params = OsOrdersHelper::quick_order_btn_html(false, $btn_data);
										echo '<div class="'.esc_attr($period_class).'" '.$btn_params.' style="'.esc_attr($period_css).'"><div class="chd-period-minutes-value">'.esc_html(OsTimeHelper::minutes_to_hours_and_minutes($minutes)).'</div></div>';
										?>
									<?php } ?>

								</div>
								<div class="da-agent-bookings">
									<?php
									if(isset($bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id]) && !empty($bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id])){
										$overlaps_count = 1;
										$total_attendees_in_group = 0;
										$total_bookings_in_group = 0;
										$total_bookings = count($bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id]);
										foreach($bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id] as $index => $booking){
											$next_booking = (($index + 1) < $total_bookings) ? $bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id][$index + 1] : false;

											if(OsBookingHelper::check_if_group_bookings($booking, $next_booking)){
												// skip this output because multiple bookings in the same slot because next booking has the same start and end time
												$total_attendees_in_group+= $booking->total_attendees;
												$total_bookings_in_group++;
												continue;
											}else{
												include('_booking_box_on_calendar.php');
												// time overlaps
												$overlaps_count = ($next_booking && ($next_booking->start_time < $booking->end_time)) ? $overlaps_count + 1 : 1;
												// reset
												$total_attendees_in_group = 0;
											}
										}
									}
									do_action('latepoint_calendar_daily_timeline', $target_date, ['agent_id' => $agent->id, 'work_start_minutes' => $work_boundaries->start_time, 'work_end_minutes' => $work_boundaries->end_time, 'work_total_minutes' => $work_total_minutes]);
									?>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php }else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-calendar"></i></div>
		    <h2><?php esc_html_e('You have not set any working hours for this day.', 'latepoint'); ?></h2>
			  <?php if(OsAuthHelper::is_agent_logged_in()){ ?>
			    <a href="<?php echo esc_url(OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => OsAuthHelper::get_logged_in_agent_id()])); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-edit-2"></i><span><?php esc_html_e('Edit Working Hours', 'latepoint'); ?></span></a>
				<?php }elseif(OsRolesHelper::can_user('settings__edit')){ ?>
			    <a href="<?php echo esc_url(OsRouterHelper::build_link(['settings', 'work_periods'])); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-edit-2"></i><span><?php esc_html_e('Edit Working Hours', 'latepoint'); ?></span></a>
				<?php } ?>
		  </div>
		<?php } ?>
	</div>
</div>
<?php } ?>
<?php include('_shared.php'); ?>