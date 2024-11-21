<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */
/* @var $order_item OsOrderItemModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
    <h2><?php esc_html_e( 'Order Summary', 'latepoint' ); ?></h2>
</div>
<div class="latepoint-lightbox-content">
    <div class="full-summary-info-w">
        <div class="summary-price-breakdown-wrapper">
            <div class="pb-heading">
                <div class="pbh-label"><?php esc_html_e( 'Cost Breakdown', 'latepoint' ); ?></div>
                <div class="pbh-line"></div>
            </div>
			<?php
			$price_breakdown_rows = $order->generate_price_breakdown_rows();
			OsPriceBreakdownHelper::output_price_breakdown( $price_breakdown_rows );
			?>
        </div>
    </div>
</div>
<?php if($order->get_total_balance_due() > 0){ ?>
<div class="latepoint-lightbox-footer">
    <a href="#"
       data-os-params="<?php echo esc_attr( http_build_query( [ 'order_id' => $order->id ] ) ); ?>"
       data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'orders', 'payment_form' ) ); ?>"
       data-os-output-target="lightbox"
       data-os-lightbox-classes="width-500"
       data-os-lightbox-inner-classes="latepoint-transaction-payment-form"
       data-os-lightbox-inner-tag="form"
       data-os-lightbox-no-close-button="yes"
       data-os-after-call="latepoint_init_transaction_payment_form"
       class="latepoint-btn latepoint-btn-block"><?php echo sprintf( __( 'Pay %s', 'latepoint' ), OsMoneyHelper::format_price( $order->get_total_balance_due(), true, false ) ); ?></a>
</div>
<?php } ?>