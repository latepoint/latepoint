<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/** @var $booking OsBookingModel */
/** @var $calendar_start_date OsWpDateTime */
/** @var $timeshift_minutes string */
/** @var $timezone_name integer */
/** @var $key string */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e('Select date and time', 'latepoint'); ?></h2>
</div>
<div class="latepoint-lightbox-content">
    <div class="reschedule-calendar-datepicker">
	<div class="os-dates-w">
		<?php OsCalendarHelper::generate_calendar_for_datepicker_step(\LatePoint\Misc\BookingRequest::create_from_booking_model($booking), $calendar_start_date, ['timezone_name' => $timezone_name, 'exclude_booking_ids' => [$booking->id]]); ?>
	</div>
	<div
		class="time-selector-w <?php echo OsStepsHelper::hide_unavailable_slots() ? 'hide-not-available-slots' : ''; ?> <?php echo 'time-system-' . esc_attr(OsTimeHelper::get_time_system()); ?> <?php echo (OsSettingsHelper::is_on('show_booking_end_time')) ? 'with-end-time' : 'without-end-time'; ?> style-<?php echo esc_attr(OsStepsHelper::get_time_pick_style()); ?>">
		<div class="times-header">
			<div class="th-line"></div>
			<div class="times-header-label">
				<?php esc_html_e('Pick a slot for', 'latepoint'); ?> <span></span>
				<?php do_action('latepoint_step_datepicker_appointment_time_header_label', $booking, $timezone_name); ?>
			</div>
			<div class="th-line"></div>
		</div>
		<div class="os-times-w">
			<div class="timeslots"></div>
		</div>
	</div>
	<?php
	echo OsFormHelper::hidden_field('booking_id', $booking->id, ['class' => 'latepoint_booking_id', 'skip_id' => true]);
	if(!empty($key)) echo OsFormHelper::hidden_field('key', $key, ['class' => 'latepoint_manage_booking_key', 'skip_id' => true]);

	echo OsFormHelper::hidden_field('booking[start_date]', $booking->start_date, ['class' => 'latepoint_start_date', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('booking[start_time]', $booking->start_time, ['class' => 'latepoint_start_time', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('timeshift_minutes', $timeshift_minutes, ['class' => 'latepoint_timeshift_minutes', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('timezone_name', $timezone_name, ['class' => 'latepoint_timezone_name', 'skip_id' => true]);
	?>
        </div>
</div>
<div class="latepoint-lightbox-footer reschedule-confirmation-button-wrapper" style="display: none;">
	<a href="#"
	   data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name((empty($key) ? 'customer_cabinet' : 'manage_booking_by_key'), 'process_reschedule_request')); ?>"
	   class="latepoint-btn latepoint-btn-primary latepoint-btn-block latepoint-request-reschedule-trigger"><?php esc_html_e('Reschedule', 'latepoint'); ?></a>
</div>