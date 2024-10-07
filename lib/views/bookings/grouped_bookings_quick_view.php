<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e('Group Appointment', 'latepoint'); ?></h2>
</div>
<div class="latepoint-lightbox-content">
	<div class="grouped-bookings-main-info">
		<div class="avatar" style="background-image: url(<?php echo esc_url($booking->agent->get_avatar_url()); ?>)"></div>
		<div class="gb-info">
			<div class="gbi-sub"><?php echo esc_html($booking->nice_start_date); ?></div>
			<div class="gbi-main"><?php echo esc_html($booking->service->name); ?></div>
			<div class="gbi-high"><?php echo esc_html(OsTimeHelper::minutes_to_hours_and_minutes($booking->start_time)); ?>
				- <?php echo esc_html(OsTimeHelper::minutes_to_hours_and_minutes($booking->end_time)); ?></div>
		</div>
		<div class="gb-capacity">
			<div class="gbc-label">
                <?php
                // translators: %1$d total booked number
                // translators: %2$d total available number
                echo esc_html__('Booked:', 'latepoint') . ' <span>' . esc_html(sprintf(__('%1$d of %2$d', 'latepoint'), $total_attendees, $booking->service->capacity_max)) . '<span>'; ?></div>
			<div class="booked-percentage">
				<div class="booked-bar"
				     style="width: <?php echo esc_attr(OsServiceHelper::get_percent_of_capacity_booked($booking->service, $total_attendees)); ?>%;"></div>
			</div>
		</div>
	</div>
	<div class="group-bookings-list">
		<div class="gb-heading"><span><?php esc_html_e('Bookings', 'latepoint'); ?></span></div>
		<?php foreach ($group_bookings as $group_booking) { ?>
			<div class="gb-booking" <?php echo OsBookingHelper::quick_booking_btn_html($group_booking->id); ?>>
				<div class="gbb-status"></div>
				<div class="gbb-avatar"
				     style="background-image: url(<?php echo esc_url($group_booking->customer->get_avatar_url()); ?>)"></div>
				<div class="gbb-customer">
					<div class="gbb-name"><?php echo esc_html($group_booking->customer->full_name); ?></div>
					<div class="gbb-email"><?php echo esc_html($group_booking->customer->email); ?></div>
				</div>
				<div class="gbb-attendees">
					<div class="gb-value"><?php echo esc_html($group_booking->total_attendees); ?></div>
					<div class="gb-label">
						<?php echo esc_html(_n( 'Attendee', 'Attendees', $group_booking->total_attendees, 'latepoint' )); ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="add-booking-to-group-box-wrapper">
		<div
			class="os-add-box add-booking-to-group-box" <?php echo OsOrdersHelper::quick_order_btn_html(false, ['start_time' => $group_booking->start_time,
			'end_time' => $group_booking->end_time,
			'agent_id' => $group_booking->agent_id,
			'start_date' => $group_booking->start_date,
			'service_id' => $group_booking->service_id,
			'location_id' => $group_booking->location_id]); ?>>
			<div class="add-box-graphic-w">
				<div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
			</div>
			<div class="add-box-label"><?php esc_html_e('Add Booking', 'latepoint'); ?></div>
		</div>
	</div>
</div>
