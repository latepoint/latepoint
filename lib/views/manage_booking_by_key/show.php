<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/**
 * @var $booking OsBookingModel
 * @var $for string
 * @var $key string
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="manage-booking-wrapper" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'show')); ?>" data-key="<?php echo esc_attr($key); ?>">
    <?php if($for == 'agent'){ ?>
	    <div class="manage-booking-controls status-<?php echo esc_attr($booking->status); ?>">
			<div class="change-booking-status-trigger-wrapper" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'change_status')); ?>">
                <?php echo OsFormHelper::select_field('booking[status]', __('Status:', 'latepoint'), OsBookingHelper::get_statuses_list(), $booking->status, ['id' => 'booking_status_'.$booking->id, 'class' => 'change-booking-status-trigger']); ?>
            </div>
            <a href="#" class="latepoint-btn latepoint-btn-white latepoint-request-booking-reschedule latepoint-btn-link" data-os-after-call="latepoint_init_reschedule" data-os-lightbox-classes="width-400 reschedule-calendar-wrapper" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'request_reschedule_calendar')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['key' => $key])); ?>" data-os-output-target="lightbox">
                <i class="latepoint-icon latepoint-icon-calendar"></i>
                <span><?php esc_html_e('Reschedule', 'latepoint'); ?></span>
            </a>
        </div>
    <?php } ?>
	<div class="manage-booking-inner">
		<?php include(LATEPOINT_VIEWS_ABSPATH.'bookings/_full_summary.php'); ?>
	</div>
</div>
