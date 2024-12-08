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
        <div class="summary-status-wrapper summary-status-<?php echo esc_attr($booking->status); ?>">
            <div class="summary-status-inner">
                <div class="ss-icon"></div>
                <div class="ss-title"><?php esc_html_e($booking->get_readable_status_title_for_summary()); ?></div>
                <div class="ss-confirmation-number"><span><?php esc_html_e('#', 'latepoint'); ?></span><strong><?php echo esc_html($booking->booking_code); ?></strong></div>
            </div>
        <?php
        if($booking->get_readable_status_description_for_summary()){
            echo '<div class="summary-status-description">'.esc_html($booking->get_readable_status_description_for_summary()).'</div>';
        }
        ?>
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