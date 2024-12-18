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
  <h3><?php esc_html_e('Balance & Payments', 'latepoint'); ?></h3>
  <div class="os-form-sub-header-actions">
    <?php echo OsFormHelper::select_field('order[payment_status]', false, OsOrdersHelper::get_order_payment_statuses_list(), $order->payment_status, ['class' => 'size-small']) ?>
  </div>
</div>
<div class="balance-payment-info" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('orders', 'reload_balance_and_payments')); ?>">
  <div class="payment-info-values">
    <?php
    $total_paid = $order->get_total_amount_paid_from_transactions();
    $total_balance = $order->get_total_balance_due();

		?>
    <div class="pi-smaller">
      <?php echo esc_html(OsMoneyHelper::format_price($total_paid, true, false)); ?>
    </div>
    <div class="pi-balance-due <?php if($total_balance > 0) echo 'pi-red'; ?>">
      <?php echo esc_html(OsMoneyHelper::format_price($total_balance, true, false)); ?>
    </div>
  </div>
  <div class="payment-info-labels">
    <div><?php esc_html_e('Total Payments', 'latepoint') ?></div>
    <div><?php esc_html_e('Balance Due', 'latepoint') ?></div>
  </div>
</div>