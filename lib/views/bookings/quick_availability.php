<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php
if(!$show_days_only) echo '<div class="quick-availability-per-day-w side-sub-panel-wrapper" data-trigger-form-order-item-id="'.esc_attr($trigger_form_order_item_id).'" data-trigger-form-booking-id="'.esc_attr($trigger_form_booking_id).'" data-agent-id="'.esc_attr($booking->agent_id).'">'; ?>
<div class="side-sub-panel-header os-form-header">
	<h2><?php esc_html_e('Availability', 'latepoint'); ?></h2>
  <a href="#" class="latepoint-quick-availability-close  latepoint-side-sub-panel-close latepoint-side-sub-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
</div>
<?php
	echo '<div class="separate-timeslots-w">';
		for($current_minutes = $work_boundaries->start_time; $current_minutes <= $work_boundaries->end_time; $current_minutes+=$timeblock_interval){
	    $ampm = OsTimeHelper::am_or_pm($current_minutes);
	    $timeslot_class = 'separate-timeslot';
	    $tick_html = '';
	    if(($current_minutes % 60) == 0){
	      $timeslot_class.= ' with-tick';
	      $tick_html = '<span class="separate-timeslot-tick"><strong>'. esc_html(OsTimeHelper::minutes_to_hours($current_minutes)) .'</strong>'.' '.esc_html($ampm).'</span>';
	    }
	    echo '<div class="'.esc_attr($timeslot_class).'">'.$tick_html.'</div>';
	  }
	echo '</div>';
	echo '<div class="os-availability-days">';
		echo '<div class="os-availability-prev-w">
						<a href="#" data-start-date="'. esc_attr($calendar_start_date->format('Y-m-d')).'" 
												class="load-prev-quick-availability os-availability-prev-w latepoint-btn latepoint-btn-outline latepoint-btn-block">
							<i class="latepoint-icon latepoint-icon-arrow-up"></i>
							<span>'.esc_html__('Load previous 30 days', 'latepoint').'</span>
						</a>
					</div>';
		echo OsBookingHelper::get_quick_availability_days($calendar_start_date, $calendar_end_date, $booking_request, $resources, ['exclude_booking_ids' => $booking->is_new_record() ? [] : [$booking->id], 'work_boundaries' => $work_boundaries]);
		echo '<div class="os-availability-next-w">
						<a href="#" data-start-date="'. esc_attr($calendar_end_date->format('Y-m-d')).'" 
												class="load-more-quick-availability os-availability-next-w latepoint-btn latepoint-btn-outline latepoint-btn-block">
							<i class="latepoint-icon latepoint-icon-arrow-down"></i>
							<span>'.esc_html__('Load next 30 days', 'latepoint').'</span>
						</a>
					</div>';
	echo '</div>';
if(!$show_days_only) echo '</div>';