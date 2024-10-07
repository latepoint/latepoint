<?php
/**
 * @var $target_date OsWpDateTime
 * @var $today_date OsWpDateTime
 * @var $calendar_start OsWpDateTime
 * @var $calendar_end OsWpDateTime
 * @var $booking_request \LatePoint\Misc\BookingRequest
 * @var $calendar_settings array
 * @var $agents OsAgentModel[]
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php
if(count($agents) > 1){
	echo '<div class="weekly-calendar-agents-list">';
	foreach($agents as $agent){
		echo '<div data-agent-id="'.esc_attr($agent->id).'" class="weekly-calendar-agent-selector '.($calendar_settings['selected_agent_id'] == $agent->id ? 'selected' : '').'">';
			echo '<div class="agent-avatar" style="background-image: url('.esc_url($agent->avatar_url).')"></div>';
			echo '<div class="agent-name">'.esc_html($agent->full_name).'</div>';
		echo '</div>';
	}
	echo '</div>';
}
?>
<div class="calendar-week-agent-w">
	<div class="calendar-self-w">
		<?php if(($work_boundaries->start_time < $work_boundaries->end_time) && ($timeblock_interval > 0)){
				$total_periods = floor(($work_boundaries->end_time - $work_boundaries->start_time) / $timeblock_interval) + 1;
				$period_height = floor(OsSettingsHelper::get_day_calendar_min_height() / $total_periods);
				$period_css = (($total_periods * 20) < OsSettingsHelper::get_day_calendar_min_height()) ? "height: {$period_height}px;" : '';
				foreach($agents as $agent){ ?>
					<div class="agent-weekly-calendar <?php echo ($agent->id == $calendar_settings['selected_agent_id']) ? 'selected' : ''; ?>" data-agent-id="<?php echo esc_attr($agent->id); ?>">
					<div class="calendar-hours">
						<div class="ch-hours">
							<div class="ch-info">
								<span><?php esc_html_e('Date', 'latepoint'); ?></span>
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
						<div class="ch-day-periods-w">
						<?php
				    for($day_date=clone $calendar_start; $day_date<=$calendar_end; $day_date->modify('+1 day')){
				      ?>
							<div class="ch-day-periods-i">
								<div class="ch-day ch-day-<?php echo esc_attr(strtolower($day_date->format('N'))); ?> <?php if($today_date == $day_date) echo 'is-today'; ?>">
									<span><?php echo esc_html(OsUtilHelper::get_weekday_name_by_number($day_date->format('N'), true)); ?></span>
									<strong><?php echo esc_html($day_date->format('j')); ?></strong>
								</div>
					      <?php
								$day_off_class = empty($work_time_periods_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id]) ? 'agent-has-day-off' : ''; ?>
								<div class="ch-day-periods ch-day-<?php echo esc_attr(strtolower($day_date->format('N')));?> <?php echo esc_attr($day_off_class); ?>">

									<?php for($minutes = $work_boundaries->start_time; $minutes <= $work_boundaries->end_time; $minutes+= $timeblock_interval){ ?>
										<?php
										$period_class = 'chd-period';
										if(!OsBookingHelper::is_minute_in_work_periods($minutes, $work_time_periods_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id])) $period_class.= ' chd-period-off ';
										$period_class.= (($minutes == $work_boundaries->end_time) || (($minutes + $timeblock_interval) > $work_boundaries->end_time)) ? ' last-period' : '';
										$period_class.= (($minutes % 60) == 0) ? ' chd-period-hour' : ' chd-period-minutes';
										$btn_params = OsOrdersHelper::quick_order_btn_html(false, ['start_time'=> $minutes, 'agent_id' => $agent->id, 'start_date' => $day_date->format('Y-m-d'), 'location_id' => $booking_request->location_id, 'service_id' => $booking_request->service_id]);
										echo '<div class="'.esc_attr($period_class).'" '.$btn_params.' style="'.esc_attr($period_css).'"><div class="chd-period-minutes-value">'.esc_html(OsTimeHelper::minutes_to_hours_and_minutes($minutes)).'</div></div>';
										?>
									<?php } ?>

									<?php
									if($bookings_grouped_by_date_and_agent[$day_date->format('Y-m-d')][$agent->id]){
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
									do_action('latepoint_calendar_daily_timeline', $day_date, ['agent_id' => $agent->id, 'work_start_minutes' => $work_boundaries->start_time, 'work_end_minutes' => $work_boundaries->end_time, 'work_total_minutes' => $work_total_minutes]);
									?>
								</div>
							</div>
						<?php } ?>
						</div>
					</div>
						</div>
				<?php } ?>
		<?php }else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-calendar"></i></div>
		    <h2><?php esc_html_e('Looks like you have not set your working hours yet, or the agent you selected does not offer this service.', 'latepoint'); ?></h2>
		    <a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('settings', 'general'))); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-settings"></i><span><?php esc_html_e('Edit Work Hours', 'latepoint'); ?></span></a>
		  </div>
		<?php } ?>
	</div>
	</div>
<?php include('_shared.php'); ?>