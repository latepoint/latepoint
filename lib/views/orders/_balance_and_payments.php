<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>

    <div class="os-form-sub-header">
        <h3><?php esc_html_e( 'Balance & Payments', 'latepoint' ); ?></h3>
        <div class="os-form-sub-header-actions">
			<?php echo OsFormHelper::select_field( 'order[payment_status]', false, OsOrdersHelper::get_order_payment_statuses_list(), $order->payment_status, [ 'class' => 'size-small' ] ) ?>
        </div>
    </div>
    <div class="balance-payment-info" data-route="<?php echo esc_attr( OsRouterHelper::build_route_name( 'orders', 'reload_balance_and_payments' ) ); ?>">
        <div class="payment-info-values">
			<?php
			$total_paid     = $order->get_total_amount_paid_from_transactions();
			$total_balance  = $order->get_total_balance_due();
            ray($order->get_items());
			$deposit_amount = $order->get_deposit_amount_to_charge();

			?>
            <div class="pi-smaller">
				<?php echo esc_html( OsMoneyHelper::format_price( $total_paid, true, false ) ); ?>
            </div>
            <div class="pi-balance-due <?php if ( $total_balance > 0 ) {
				echo 'pi-red';
			} ?>">
				<?php echo esc_html( OsMoneyHelper::format_price( $total_balance, true, false ) ); ?>
            </div>
        </div>
        <div class="payment-info-labels">
            <div><?php esc_html_e( 'Total Payments', 'latepoint' ) ?></div>
            <div><?php esc_html_e( 'Total Balance Due', 'latepoint' ) ?></div>
        </div>
    </div>

<?php if ( $order->is_new_record() ) { ?>
    <div class="initial-payment-data-wrapper">
        <div class="initial-payment-data-toggler-wrapper">
            <?php echo OsFormHelper::toggler_field('create_payment_request', esc_html__( 'Create a Payment Request', 'latepoint' ), false, 'payNowPortionInfo'); ?>
            <a href="#"
               data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params( [ 'topic' => 'payment_request' ] )); ?>"
			   data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'support_topics', 'view' )); ?>"
			   data-os-output-target="lightbox"
               class="latepoint-element-info-trigger"><i class="latepoint-icon latepoint-icon-info"></i></a>
        </div>
        <div id="payNowPortionInfo" style="display: none;">
            <div class="payment-request-row">
            <?php
            $payment_portions = [];
            if($total_balance > 0) $payment_portions[LATEPOINT_PAYMENT_PORTION_FULL] = sprintf( __( 'Full Price [%s]' ), OsMoneyHelper::format_price( $total_balance, true, false ) );
            if($deposit_amount > 0) $payment_portions[LATEPOINT_PAYMENT_PORTION_DEPOSIT] = sprintf( __( 'Deposit Only [%s]', 'latepoint' ), OsMoneyHelper::format_price( $deposit_amount, true, false ) );
            $payment_portions[LATEPOINT_PAYMENT_PORTION_CUSTOM] = __( 'Custom', 'latepoint' );

            $selected_portion = array_key_first($payment_portions);
            echo '<div class="label-for-select">'.esc_html__('Amount:', 'latepoint').'</div>';
            echo OsFormHelper::select_field( 'payment_request[portion]', false, $payment_portions, $selected_portion, [ 'class' => 'size-small', 'theme' => 'simple' ] );
            echo '<div class="custom-charge-amount-wrapper" style="'.(($selected_portion != LATEPOINT_PAYMENT_PORTION_CUSTOM) ? 'display: none;' : '').'">';
            echo OsFormHelper::money_field( 'payment_request[charge_amount_custom]', false, $total_balance, [ 'class' => 'size-small', 'theme' => 'simple' ] );
            echo OsFormHelper::hidden_field('payment_request[charge_amount_full]', $total_balance);
            echo OsFormHelper::hidden_field('payment_request[charge_amount_deposit]', $deposit_amount);
            echo '</div>';
            ?>
            </div>
            <div class="payment-request-row">
                <?php
                    echo '<div class="label-for-select">'.esc_html__('Due Date:', 'latepoint').'</div>';
                    echo OsFormHelper::date_picker_field( 'payment_request[due_at]', OsTimeHelper::get_readable_date_from_string(OsTimeHelper::today_date('Y-m-d')), OsTimeHelper::today_date('Y-m-d'), [ 'class' => 'size-small', 'theme' => 'simple' ] );
                ?>
            </div>
        </div>

    </div>
<?php } ?>