<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/**
 * @var $order OsOrderModel
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
	 * @param {OsOrderModel} $order instance of order model
	 *
	 * @since 5.0.0
	 * @hook latepoint_order_full_summary_before
	 *
	 */
	do_action( 'latepoint_order_full_summary_before', $order ); ?>
    <div class="full-summary-head-info">
		<?php
		/**
		 * Order Summary Head Section - before
		 *
		 * @param {OsOrderModel} $order instance of order model
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_full_summary_head_info_before
		 *
		 */
		do_action( 'latepoint_order_full_summary_head_info_before', $order ); ?>
        <div class="full-summary-order-info-wrapper">
            <div class="fsoi-main-wrapper">
                <div class="fsoi-main">
                    <span><?php esc_html_e( 'Order #', 'latepoint' ); ?></span>
                    <strong><?php esc_html_e($order->confirmation_code); ?></strong>
                </div>
                <div class="order-full-summary-actions">
                    <a href="<?php echo esc_url(OsRouterHelper::build_admin_post_link( [ 'customer_cabinet', 'print_order_info'], [ 'latepoint_order_id' => $order->id ] )); ?>" class="print-booking-btn" target="_blank"><i class="latepoint-icon latepoint-icon-printer"></i><span><?php esc_html_e( 'Print', 'latepoint' ); ?></span></a>
                </div>
            </div>
            <div class="full-summary-order-info-elements">
                <div class="fsoi-element">
                    <span><?php esc_html_e( 'Created:', 'latepoint' ); ?></span>
                    <strong><?php esc_html_e(esc_html( OsTimeHelper::get_readable_date( new OsWpDateTime( $order->created_at)))); ?></strong>
                </div>
                <div class="fsoi-element">
                    <span><?php esc_html_e( 'Status:', 'latepoint' ); ?></span>
                    <strong><?php esc_html_e($order->get_nice_status_name()); ?></strong>
                </div>
                <div class="fsoi-element">
                    <span><?php esc_html_e( 'Payment:', 'latepoint' ); ?></span>
                    <strong><?php esc_html_e($order->get_nice_payment_status_name()); ?></strong>
                </div>
            </div>
        </div>
		<?php
		/**
		 * Order Summary Head Section - after
		 *
		 * @param {OsOrderModel} $order instance of order model
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_full_summary_head_info_after
		 *
		 */
		do_action( 'latepoint_order_full_summary_head_info_after', $order ); ?>
    </div>
    <div class="full-summary-info-w">
        <div class="order-summary-items-heading">
            <?php esc_html_e( 'Order Items', 'latepoint' ); ?>
            <div class="osih-line"></div>
        </div>
		<?php include( LATEPOINT_VIEWS_ABSPATH . 'steps/partials/_order_summary.php' ); ?>
    </div>
</div>