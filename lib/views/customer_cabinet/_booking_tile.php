<?php
/**
 * @var $booking OsBookingModel
 * @var $is_upcoming_booking bool
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="customer-booking status-<?php echo esc_attr($booking->status); ?>" data-id="<?php echo esc_attr($booking->id); ?>" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'reload_booking_tile')); ?>">
	<h6 class="customer-booking-service-name"><?php echo esc_html($booking->service->name); ?></h6>
	<div class="customer-booking-datetime">
  <?php
  if($booking->start_date){
	  $booking_start_datetime = $booking->get_nice_start_datetime();
	  $booking_start_datetime = apply_filters('latepoint_booking_summary_formatted_booking_start_datetime', $booking_start_datetime, $booking);
		echo $booking_start_datetime;
  }
	?>
	</div>

	<?php
	if($booking->is_part_of_bundle()){ ?>
	<div class="part-of-bundle-message"><?php esc_html_e('This booking is part of a bundle.', 'latepoint'); ?> <a href="#" <?php echo OsCustomerHelper::generate_bundle_scheduling_btn($booking->order_item_id); ?>><?php esc_html_e('Show Details', 'latepoint'); ?></a></div>
	<?php
	}
	?>

	<?php if($is_upcoming_booking){ ?>
		<div class="customer-booking-buttons">
			<?php if(OsCustomerHelper::can_reschedule_booking($booking)){ ?>
				<a href="#" class="latepoint-btn latepoint-btn-primary latepoint-request-booking-reschedule latepoint-btn-link" data-os-after-call="latepoint_init_reschedule" data-os-lightbox-classes="width-400 reschedule-calendar-wrapper" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'request_reschedule_calendar')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['booking_id' => $booking->id])); ?>" data-os-output-target="lightbox">
					<span><?php esc_html_e('Reschedule', 'latepoint'); ?></span>
				</a>
			<?php } ?>
			<?php if(OsCustomerHelper::can_cancel_booking($booking)){ ?>
				<a href="#" class="latepoint-btn latepoint-btn-danger latepoint-btn-link"
				   data-os-prompt="<?php esc_attr_e('Are you sure you want to cancel this appointment?', 'latepoint'); ?>"
					   data-os-success-action="reload"
					   data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'request_cancellation')); ?>"
					   data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['id' => $booking->id])); ?>"
					<i class="latepoint-icon latepoint-icon-ui-24"></i>
					<span><?php esc_html_e('Cancel', 'latepoint'); ?></span>
				</a>
			<?php } ?>
		</div>
	<?php } ?>
		<div class="customer-booking-service-color"></div>

	<div class="customer-booking-info">
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php esc_html_e('Agent', 'latepoint'); ?></span>
			<span class="booking-info-value"><?php echo esc_html($booking->agent->full_name); ?></span>
		</div>
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php esc_html_e('Status', 'latepoint'); ?></span>
			<span class="booking-info-value status-<?php echo esc_attr($booking->status); ?>"><?php echo esc_html($booking->nice_status); ?></span>
		</div>
		<?php do_action('latepoint_customer_dashboard_after_booking_info_tile', $booking); ?>
	</div>
	<div class="customer-booking-bottom-actions">
		<?php if($is_upcoming_booking){ ?>
			<div class="add-to-calendar-wrapper">
				<a href="#" class="open-calendar-types latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block">
					<i class="latepoint-icon latepoint-icon-plus-circle"></i>
					<span><?php esc_html_e('Add to Calendar', 'latepoint'); ?></span>
				</a>
				<?php echo OsBookingHelper::generate_add_to_calendar_links($booking); ?>
			</div>
		<?php } ?>
		<div class="load-booking-summary-btn-w">
			<a href="#"
			   class="latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block"
			   <?php echo OsCustomerHelper::generate_booking_summary_preview_btn($booking->id); ?>>
				<i class="latepoint-icon latepoint-icon-list"></i>
				<span><?php esc_html_e('View Summary', 'latepoint'); ?></span>
			</a>
		</div>
	</div>
    <?php if(false && !$booking->is_part_of_bundle() && $booking->order->get_total_balance_due() > 0){ ?>
    <div class="bt-payment-actions">
        <div class="bt-balance-info">
            <div class="bt-balance-label"><?php esc_html_e('Balance Due:', 'latepoint'); ?></div>
            <div class="bt-balance-amount"><?php echo OsMoneyHelper::format_price($booking->order->get_total_balance_due(), true, false); ?></div>
        </div>
        <div class="bt-balance-pay-link"><a href="#"
                  data-os-params="<?php echo esc_attr(http_build_query( [ 'order_id' => $booking->order->id ] )); ?>"
                  data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'orders', 'summary_before_payment' )); ?>"
                  data-os-output-target="lightbox"
                  data-os-lightbox-classes="width-500"
            ><?php esc_html_e('Make Payment', 'latepoint'); ?></a></div>
    </div>
    <?php } ?>
</div>