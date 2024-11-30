<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $in_lightbox bool */
/* @var $order OsOrderModel */
/* @var $invoice OsInvoiceModel */
/* @var $order_item OsOrderItemModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php if(!$in_lightbox){ ?><div class="clean-layout-content-wrapper"><?php } ?>
    <?php if($in_lightbox) echo '<div class="latepoint-lightbox-heading"><h2>'.esc_html__('Balance Details', 'latepoint').'</h2></div>'; ?>
	<div class="invoice-payment-form-wrapper <?php echo $in_lightbox ? 'latepoint-lightbox-content' : 'clean-layout-content-body'; ?> is-dotted">
        <div class="invoice-due-amount-wrapper">
            <div class="invoice-due-amount-inner">
                <div class="id-amount"><?php echo OsMoneyHelper::format_price($invoice->charge_amount, true, false); ?></div>
                <div class="id-sub-info">
                    <?php esc_html_e('Order:', 'latepoint-pro-features'); ?>
                    <a href="#"><span><?php echo $order->confirmation_code; ?></span><i class="latepoint-icon latepoint-icon-external-link"></i></a>
                </div>
            </div>
            <?php if($order->get_total_balance_due() > 0){ ?>
                <a href="#"
                   data-os-params="<?php echo esc_attr( http_build_query( [ 'key' => $invoice->access_key ] ) ); ?>"
                   data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'invoices', 'payment_form' ) ); ?>"
                   data-os-lightbox-inner-classes="latepoint-transaction-payment-form"
                   data-os-after-call="latepoint_init_transaction_payment_form"
                   data-os-output-target=".invoice-payment-form-wrapper"
                   class="latepoint-btn latepoint-btn-block invoice-make-payment-btn">
                    <span><?php echo sprintf( __( 'Pay Now', 'latepoint-pro-features' ), OsMoneyHelper::format_price( $order->get_total_balance_due(), true, false ) ); ?></span>
                    <i class="latepoint-icon latepoint-icon-arrow-right1"></i>
                </a>
            <?php } ?>
        </div>
        <div class="full-summary-info-w">
            <div class="summary-price-breakdown-wrapper">
                <div class="pb-heading">
                    <div class="pbh-label"><?php esc_html_e( 'Order Breakdown', 'latepoint-pro-features' ); ?></div>
                    <div class="pbh-line"></div>
                </div>
                <?php
                $price_breakdown_rows = $order->generate_price_breakdown_rows();
                OsPriceBreakdownHelper::output_price_breakdown( $price_breakdown_rows );
                ?>
            </div>
        </div>
	</div>
<?php if(!$in_lightbox){ ?></div><?php } ?>
