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
if($agents){ ?>
	<?php
	$agents_head_html = '';
	foreach($agents as $agent){
		$agents_head_html.=
			'<div class="ma-head-agent">
				<div class="ma-head-agent-avatar" style="background-image: url('.esc_url($agent->get_avatar_url()).')"></div>
				<div class="ma-head-agent-name">'.esc_html($agent->full_name).'</div>
			</div>';
	}
	$calendar_not_scrollable_class = (count($agents) > 4) ? '' : 'calendar-month-not-scrollable';
	echo '<div class="calendar-month-agents-w '.esc_attr($calendar_not_scrollable_class).'" data-route="'.esc_attr(OsRouterHelper::build_route_name('calendars', 'month_view')).'">';
		echo '<div class="ma-floated-days-w">';
			echo '<div class="ma-head-info"><span>'.esc_html__('Date', 'latepoint').'</span><span>'.esc_html__('Agent', 'latepoint').'</span></div>';
	    for($day_date=clone $calendar_start; $day_date<=$calendar_end; $day_date->modify('+1 day')){
				echo '<div class="ma-day ma-day-number-'.esc_attr($day_date->format('N')).' '.(($today_date == $day_date) ? 'is-today' : '').'">';
					echo '<div class="ma-day-info">';
						echo '<span class="ma-day-number">'.esc_html($day_date->format('j')).'</span>';
						echo '<span class="ma-day-weekday">'.esc_html(OsUtilHelper::get_weekday_name_by_number($day_date->format('N'), true)).'</span>';
					echo '</div>';
				echo '</div>';
			}
		echo '</div>';
		echo '<div class="ma-days-with-bookings-w">';
			echo '<div class="ma-days-with-bookings-i">';
				echo '<div class="ma-head">';
					echo $agents_head_html;
				echo '</div>';
		    for($day_date=clone $calendar_start; $day_date<=$calendar_end; $day_date->modify('+1 day')){
					echo '<div class="ma-day ma-day-number-'.esc_attr($day_date->format('N')).' '.(($today_date == $day_date) ? 'is-today' : '').'">';
						foreach($agents as $agent){
							$day_periods = [];
							$blocked_blocks = [];
							foreach($resources[$day_date->format('Y-m-d')] as $resource){
								// TODO if multiple resources available for this day - merge blocked periods to find intersections - those intersection perons are truly blocked
								if($resource->agent_id == $agent->id){
									$day_periods = array_merge($day_periods, $resource->work_time_periods);
									foreach($resource->blocked_time_periods as $blocked_time_period){
										$left = max(($blocked_time_period->start_time - $work_boundaries->start_time) / $work_total_minutes * 100, 0);
										$right = max(($work_boundaries->end_time - $blocked_time_period->end_time) / $work_total_minutes * 100, 0);
										$blocked_blocks[] = ['left' => $left, 'right' => $right];
									}
								}
							}


							usort($day_periods,function($first,$second){
								return ($first->start_time > $second->start_time) ? 1 : 0;
							});
							$day_periods = \LatePoint\Misc\TimePeriod::merge_periods($day_periods);
							if($day_periods){
								echo '<div class="ma-day-agent-bookings" '.OsOrdersHelper::quick_order_btn_html(false, array('agent_id' => $agent->id, 'location_id' => $booking_request->location_id, 'start_date' => $day_date->format('Y-m-d'))).'>';
									$off_blocks = [];
									foreach($day_periods as $day_period){
										if($day_period->start_time > $work_boundaries->start_time){
											if($off_blocks){
												$right = ($work_boundaries->end_time - $day_period->start_time) / $work_total_minutes * 100;
												$off_blocks[count($off_blocks) - 1]['right'] = $right;
											}else{
												$right = ($work_boundaries->end_time - $day_period->start_time) / $work_total_minutes * 100;
												$off_blocks[] = ['left' => 0, 'right' => $right];
											}
										}
										if($day_period->end_time < $work_boundaries->end_time){
											$left = ($day_period->end_time - $work_boundaries->start_time) / $work_total_minutes * 100;
											$off_blocks[] = ['left' => $left, 'right' => 0];
										}
									}
									foreach($blocked_blocks as $blocked_block){
										echo '<div class="ma-day-off" style="left:'.esc_attr($blocked_block["left"]).'%; right: '.esc_attr($blocked_block["right"]).'%;"></div>';
									}
									foreach($off_blocks as $off_block){
										echo '<div class="ma-day-off" style="left:'.esc_attr($off_block["left"]).'%; right: '.esc_attr($off_block["right"]).'%;"></div>';
									}
									echo '<div class="ma-day-work-periods">';
										echo '<div class="ma-day-label">'.esc_html($day_date->format($date_format)).': </div>';
										foreach($day_periods as $day_period){
											echo '<div class="ma-day-work-period">';
												echo esc_html(OsTimeHelper::minutes_to_hours_and_minutes($day_period->start_time).' - '.OsTimeHelper::minutes_to_hours_and_minutes($day_period->end_time));
											echo '</div>';
										}
									echo '</div>';
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
												if($booking->end_date && ($booking->start_date != $booking->end_date)){
													$width = (24*60 - $booking->start_time) / $work_total_minutes * 100;
													$left = ($booking->start_time - $work_boundaries->start_time) / $work_total_minutes * 100;
												}else{
													$width = ($booking->end_time - $booking->start_time) / $work_total_minutes * 100;
													$left = ($booking->start_time - $work_boundaries->start_time) / $work_total_minutes * 100;
												}

												$max_capacity = OsServiceHelper::get_max_capacity($booking->service);
												if($max_capacity > 1){
												  $action_html = OsBookingHelper::group_booking_btn_html($booking->id);
												}else{
													$action_html = OsBookingHelper::quick_booking_btn_html($booking->id);
												}
												if($width <= 0 || $left >= 100 || (($left + $width) <= 0)) continue;
												if($left < 0){
													$width = $width + $left;
													$left = 0;
												}
												if(($left + $width) > 100) $width = 100 - $left;

												echo '<div class="ma-day-booking" style="left: '.esc_attr($left).'%; width: '.esc_attr($width).'%; background-color: '.esc_attr($booking->service->bg_color).'" '.$action_html.'>';
																$hide_agent_info = true;
																include(LATEPOINT_VIEWS_ABSPATH.'dashboard/_booking_info_box_small.php');
												echo '</div>';
												// time overlaps
												$overlaps_count = ($next_booking && ($next_booking->start_time < $booking->end_time)) ? $overlaps_count + 1 : 1;
												// reset
												$total_attendees_in_group = 0;
											}
										}
									}
								echo '</div>';
							}else{
								echo '<div class="ma-day-agent-bookings is-day-off">';
									echo '<div class="ma-day-off full"><span><strong>'.esc_html($day_date->format($date_format)).': </strong>'.esc_html__('Day Off', 'latepoint').'</span></div>';
								echo '</div>';
							}
						}
					echo '</div>';
			}
			echo '</div>';
		echo '</div>';
	echo '</div>';
}else{ ?>
  <div class="no-results-w">
    <div class="icon-w"><i class="latepoint-icon latepoint-icon-grid"></i></div>
    <h2><?php esc_html_e('No Agents Created', 'latepoint'); ?></h2>
    <a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'new_form') )); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php esc_html_e('Create Agent', 'latepoint'); ?></span></a>
  </div>
<?php } ?>
<?php include('_shared.php'); ?>