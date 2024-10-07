<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-widget os-widget-agents-bookings-timeline os-widget-animated" data-os-reload-action="<?php echo esc_attr(OsRouterHelper::build_route_name('dashboard', 'widget_bookings_and_availability_timeline')); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php esc_html_e('Day Preview', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<div class="os-date-range-picker" data-single-date="yes">
				<span class="range-picker-value"><?php echo esc_html(OsTimeHelper::get_readable_date($target_date_obj)); ?></span>
				<i class="latepoint-icon latepoint-icon-chevron-down"></i>
				<input type="hidden" name="date_from" value="<?php echo esc_attr($target_date_obj->format('Y-m-d')); ?>"/>
				<input type="hidden" name="date_to" value="<?php echo esc_attr($target_date_obj->format('Y-m-d')); ?>"/>
			</div>
			<?php echo OsFormHelper::hidden_field('what_to_show', $what_to_show, ['class' => 'os-trigger-reload-widget', 'id' => 'what_to_show_on_timeline_widget']); ?>
			<select name="location_id" id="" class="os-trigger-reload-widget">
				<?php if(OsSettingsHelper::is_on('one_location_at_time')) echo '<option value="">'.esc_html__('All Locations', 'latepoint').'</option>'; ?>
				<?php foreach($locations as $location){ ?>
				<option value="<?php echo esc_attr($location->id); ?>" <?php if($location->id == $selected_location_id) echo 'selected="selected"' ?>><?php echo esc_html($location->name); ?></option>
				<?php } ?>
			</select>
			<select name="service_id" id="" class="os-trigger-reload-widget">
				<?php echo '<option value="">'.esc_html__('All Services', 'latepoint').'</option>'; ?>
				<?php foreach($services as $service){ ?>
				<option value="<?php echo esc_attr($service->id); ?>" <?php if($service->id == $selected_service_id) echo 'selected="selected"' ?>><?php echo esc_html($service->name); ?></option>
				<?php } ?>
			</select>
			<div class="timeline-type-toggle" data-value-holder-id="what_to_show_on_timeline_widget">
				<div class="timeline-type-option <?php echo ($what_to_show == 'appointments') ? 'active' : ''; ?>" data-value="appointments"><?php esc_html_e('Show Appointments', 'latepoint'); ?></div>
				<div class="timeline-type-option <?php echo ($what_to_show == 'availability') ? 'active' : ''; ?>" data-value="availability"><?php esc_html_e('Show Availability', 'latepoint'); ?></div>
			</div>
		</div>
	</div>
	<div class="os-widget-content no-padding">
		<?php if($agents){ ?>
			<div class="timeline-with-info-w">
				<div class="timeline-and-availability-w">
						<div class="timeline-and-availability-contents shows-<?php echo esc_attr($what_to_show); ?>">
							<div class="agent-day-bookings-timeline-compact-w">
								<div class="agents-avatars">
										<?php foreach($agents as $agent){ ?>
											<div class="avatar-w">
												<a href="<?php echo esc_url(OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $agent->id] )); ?>" class="avatar-i" style="background-image: url(<?php echo esc_url($agent->get_avatar_url()); ?>);">
													<span><?php echo esc_html($agent->full_name); ?></span>
												</a>
											</div>
										<?php } ?>
								</div>
								<div class="agents-timelines-w">
									<?php if($show_today_indicator) echo '<div class="current-time-indicator" style="left: '.esc_attr($time_now_indicator_left_offset).'%"><span>'.esc_html($time_now_label).'</span></div>'; ?>
									<div class="timeline-top-w">
										<?php
										for($minutes = $work_boundaries->start_time; $minutes <= $work_boundaries->end_time; $minutes+= 5){
											if(($minutes % 60) == 0){
												echo '<div class="timeslot with-tick"><div class="tick"></div><div class="timeslot-time"><div class="timeslot-hour">'.esc_html(OsTimeHelper::minutes_to_hours($minutes)).'</div><div class="timeslot-ampm">'.esc_html(OsTimeHelper::am_or_pm($minutes)).'</div></div></div>';
											}else{
												echo '<div class="timeslot"></div>';
											}
										}
										?>
									</div>
									<?php foreach($agents as $agent){
										$timeline_booking_request = clone $availability_booking_request;
										$timeline_booking_request->agent_id = $agent->id; ?>
										<div class="agent-timeline-w">
											<div class="agent-timeline">
												<?php echo OsTimelineHelper::availability_timeline($timeline_booking_request, $work_boundaries, $agents_resources['agent_'.$agent->id], ['show_ticks' => false]); ?>
												<?php
												$bookings_filter = new \LatePoint\Misc\Filter(['statuses' => OsCalendarHelper::get_booking_statuses_to_display_on_calendar(), 'date_from' => $target_date, 'agent_id' => $agent->id, 'location_id' => $selected_location_id]);
												$bookings = OsBookingHelper::get_bookings($bookings_filter, true);
												echo '<div class="booking-blocks">';
													if($bookings && $work_total_minutes){
														$overlaps_count = 1;
														$total_attendees_in_group = 0;
														$total_bookings_in_group = 0;
														$total_bookings = count($bookings);
														foreach($bookings as $index => $booking){
															$next_booking = (($index + 1) < $total_bookings) ? $bookings[$index + 1] : false;
															if(OsBookingHelper::check_if_group_bookings($booking, $next_booking)){
																// skip this output because multiple bookings in the same slot because next booking has the same start and end time
																$total_attendees_in_group+= $booking->total_attendees;
																$total_bookings_in_group++;
																continue;
															}else{

																$width = ($booking->end_time - $booking->start_time) / $work_total_minutes * 100;
																$left = ($booking->start_time - $work_boundaries->start_time) / $work_total_minutes * 100;
																if($width <= 0 || $left >= 100 || (($left + $width) <= 0)) continue;
																if($left < 0){
																	$width = $width + $left;
																	$left = 0;
																}
																if(($left + $width) > 100) $width = 100 - $left;
																$max_capacity = OsServiceHelper::get_max_capacity($booking->service);
																if($max_capacity > 1){
																  $action_html = OsBookingHelper::group_booking_btn_html($booking->id);
																}else{
																	$action_html = OsBookingHelper::quick_booking_btn_html($booking->id);
																}

																$custom_height = (isset($overlaps_count) && $overlaps_count > 1) ? 'height:'.(26 / $overlaps_count).'px;' : '';

																echo '<div data-start="'.esc_attr($booking->start_time).'" data-end="'.esc_attr($booking->end_time).'" class="booking-block status-'.esc_attr($booking->status).'" '.$action_html.' style="background-color: '.esc_attr($booking->service->bg_color).'; left: '.esc_attr($left).'%; width: '.esc_attr($width).'%;'.esc_attr($custom_height).'">';
																	$hide_agent_info = true;
																	include('_booking_info_box_small.php');
																echo '</div>';

																// time overlaps
																$overlaps_count = ($next_booking && ($next_booking->start_time < $booking->end_time)) ? $overlaps_count + 1 : 1;
																// reset
																$total_attendees_in_group = 0;
															}
														}
													}
													do_action('latepoint_appointments_timeline', OsWpDateTime::os_createFromFormat('Y-m-d', $target_date), ['agent_id' => $agent->id,'work_start_minutes' => $work_boundaries->start_time, 'work_end_minutes' => $work_boundaries->end_time, 'work_total_minutes' => $work_total_minutes]);
												echo '</div>';
												?>
											</div>
										</div>
									<?php } ?>
									<?php if(count($agents) > 3){ ?>
										<div class="timeline-bottom-w">
											<?php

											for($minutes = $work_boundaries->start_time; $minutes <= $work_boundaries->end_time; $minutes+= 5){
												if(($minutes % 60) == 0){
													echo '<div class="timeslot with-tick"><div class="timeslot-time"><div class="timeslot-hour">'.esc_html(OsTimeHelper::minutes_to_hours($minutes)).'</div><div class="timeslot-ampm">'.esc_html(OsTimeHelper::am_or_pm($minutes)).'</div></div></div>';
												}else{
													echo '<div class="timeslot"></div>';
												}
											}
											?>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php
		}else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-user-plus"></i></div>
		    <h2><?php esc_html_e('No Agents Created', 'latepoint'); ?></h2>
		    <a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'new_form') )); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus-square"></i><span><?php esc_html_e('Create Agent', 'latepoint'); ?></span></a>
		  </div>
		<?php } ?>
	</div>
</div>