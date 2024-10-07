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
	<div class="manage-booking-controls status-<?php echo esc_attr($booking->status); ?>">
		<?php if($for == 'agent'){
			echo '<div class="change-booking-status-trigger-wrapper" data-route-name="'.esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'change_status')).'">';
			echo OsFormHelper::select_field('booking[status]', __('Status:', 'latepoint'), OsBookingHelper::get_statuses_list(), $booking->status, ['id' => 'booking_status_'.$booking->id, 'class' => 'change-booking-status-trigger']);
			echo '</div>'; ?>
			<a href="#" class="latepoint-btn latepoint-btn-white latepoint-request-booking-reschedule latepoint-btn-link" data-os-after-call="latepoint_init_reschedule" data-os-lightbox-classes="width-400 reschedule-calendar-wrapper" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'request_reschedule_calendar')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['key' => $key])); ?>" data-os-output-target="lightbox">
				<i class="latepoint-icon latepoint-icon-calendar"></i>
				<span><?php esc_html_e('Reschedule', 'latepoint'); ?></span>
			</a>
			<?php
		}else{ ?>
			<div class="manage-status-info">
				<span class="status-info-label"><?php esc_html_e('Status:', 'latepoint'); ?></span>
				<span class="status-info-value status-<?php echo esc_attr($booking->status); ?>"><?php echo esc_html($booking->nice_status); ?></span>
			</div>
			<?php
			if($booking->is_upcoming()){
				if(OsCustomerHelper::can_reschedule_booking($booking)){ ?>
					<a href="#" class="latepoint-btn latepoint-btn-white latepoint-request-booking-reschedule latepoint-btn-link" data-os-after-call="latepoint_init_reschedule" data-os-lightbox-classes="width-400 reschedule-calendar-wrapper" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'request_reschedule_calendar')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['key' => $key])); ?>" data-os-output-target="lightbox">
						<i class="latepoint-icon latepoint-icon-calendar"></i>
						<span><?php esc_html_e('Reschedule', 'latepoint'); ?></span>
					</a>
					<?php
				}
				if(OsCustomerHelper::can_cancel_booking($booking)){ ?>
					<a href="#" class="latepoint-btn latepoint-btn-white latepoint-btn-link"
					   data-os-prompt="<?php esc_attr_e('Are you sure you want to cancel this appointment?', 'latepoint'); ?>"
					   data-os-success-action="reload"
					   data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_booking_by_key', 'request_cancellation')); ?>"
					   data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['key' => $key])); ?>">
						<i class="latepoint-icon latepoint-icon-ui-24"></i>
						<span><?php esc_html_e('Cancel', 'latepoint'); ?></span>
					</a>
					<?php
				}
			}
		}?>
	</div>
	<div class="manage-booking-inner">
		<?php include(LATEPOINT_VIEWS_ABSPATH.'bookings/_full_summary.php'); ?>
	</div>
</div>
