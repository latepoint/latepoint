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
<div class="customer-order status-<?php echo esc_attr( $order->status ); ?>" data-id="<?php echo esc_attr( $order->id ); ?>"
     data-route-name="<?php echo esc_attr( OsRouterHelper::build_route_name( 'customer_cabinet', 'reload_order_tile' ) ); ?>">
    <div class="customer-order-confirmation">
		<?php echo esc_html( $order->confirmation_code ); ?>
    </div>
    <div class="customer-order-datetime">
		<?php echo esc_html( OsTimeHelper::get_readable_date( new OsWpDateTime( $order->created_at ) ) ); ?>
    </div>
	<?php OsPriceBreakdownHelper::output_price_breakdown( $order->generate_price_breakdown_rows() ); ?>
    <div class="customer-order-bottom-actions">
        <div class="load-booking-summary-btn-w">
            <a href="#"
               class="latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block latepoint-btn-sm"
               data-os-after-call="latepoint_init_order_summary_lightbox"
               data-os-params="<?php echo esc_attr( OsUtilHelper::build_os_params( [ 'order_id' => $order->id ] ) ); ?>"
               data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'customer_cabinet', 'view_order_summary_in_lightbox' ) ); ?>"
               data-os-output-target="lightbox"
               data-os-lightbox-classes="width-500 customer-dashboard-booking-summary-lightbox">
                <i class="latepoint-icon latepoint-icon-list"></i>
                <span><?php esc_html_e( 'Summary', 'latepoint' ); ?></span>
            </a>
        </div>
		<?php
		if ( OsPaymentsHelper::is_accepting_payments() ) {
			$unpaid_invoices = $order->get_invoices( [ LATEPOINT_INVOICE_STATUS_NOT_PAID ] );
			if ( $unpaid_invoices ) {
				foreach ( $unpaid_invoices as $invoice ) { ?>
                    <a href="#" class="latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block latepoint-btn-sm"
                       data-os-params="<?php echo esc_attr( http_build_query( [ 'key' => $invoice->access_key, 'layout' => 'lightbox' ] ) ); ?>"
                       data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'invoices', 'summary_before_payment' ) ); ?>"
                       data-os-output-target="lightbox"
                       data-os-lightbox-classes="width-500">
                        <i class="latepoint-icon latepoint-icon-credit-card"></i>
                        <span><?php esc_html_e( 'Pay Balance', 'latepoint' ); ?></span>
                    </a>
					<?php
				}
			}
		} ?>
    </div>
</div>