<?php
/**
 * @var $booking OsBookingModel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="full-summary-wrapper">
	<?php
	/**
	 * Order Summary - before
	 *
	 * @since 5.0.0
	 * @hook latepoint_booking_full_summary_before
	 *
	 * @param {OsBookingModel} $booking instance of booking model
	 */
	do_action('latepoint_booking_full_summary_before', $booking); ?>
    <div class="booking-status-info-wrapper status-<?php echo esc_attr($booking->status); ?>">
        <div class="booking-status-icon"></div>
        <div class="booking-status-label"><?php echo $booking->get_nice_status(); ?></div>
        <div class="booking-confirmation"><?php echo sprintf(esc_html__('Order #%s', 'latepoint'), '<strong>'.$booking->order->confirmation_code.'</strong>'); ?></div>
    </div>
	<div class="full-summary-head-info">
	  <?php
		/**
		 * Order Summary Head Section - before
		 *
		 * @since 5.0.0
		 * @hook latepoint_booking_full_summary_head_info_before
		 *
		 * @param {OsBookingModel} $booking instance of booking model
		 */
	  do_action('latepoint_booking_full_summary_head_info_before', $booking); ?>
	  <?php
		/**
		 * Order Summary Head Section - after
		 *
		 * @since 5.0.0
		 * @hook latepoint_booking_full_summary_head_info_after
		 *
		 * @param {OsBookingModel} $booking instance of booking model
		 */
	  do_action('latepoint_booking_full_summary_head_info_after', $booking); ?>
	</div>
	<div class="full-summary-info-w">
	  <?php include(LATEPOINT_VIEWS_ABSPATH.'steps/partials/_booking_summary.php'); ?>
	</div>
</div>