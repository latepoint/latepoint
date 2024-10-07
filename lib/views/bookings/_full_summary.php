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
	  <div class="full-summary-number"><?php esc_html_e('Confirmation #', 'latepoint'); ?> <strong><?php echo $booking->booking_code; ?></strong></div>
	  <div class="booking-full-summary-actions">
		  <div class="add-to-calendar-wrapper">
		    <a href="#" class="open-calendar-types ical-download-btn"><i class="latepoint-icon latepoint-icon-calendar"></i><span><?php esc_html_e('Add to Calendar', 'latepoint'); ?></span></a>
			  <?php echo OsBookingHelper::generate_add_to_calendar_links($booking, $key ?? ''); ?>
		  </div>
	    <a href="<?php echo esc_url($booking->get_print_link($key ?? '')); ?>" class="print-booking-btn" target="_blank"><i class="latepoint-icon latepoint-icon-printer"></i><span><?php esc_html_e('Print', 'latepoint'); ?></span></a>
	  </div>
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