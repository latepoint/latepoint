<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $transactions OsTransactionModel[] */
/* @var $customer_name_query string */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ($transactions) {
	foreach ($transactions as $transaction) { ?>
		<tr>
			<td class="text-center os-column-faded"><?php echo esc_html($transaction->id); ?></td>
			<td><?php echo esc_html($transaction->token); ?></td>
			<td>
				<?php if ($transaction->order_id) { ?>
					<?php echo '<a href="#" class="in-table-link" ' . OsOrdersHelper::quick_order_btn_html($transaction->order_id) . '><span>' . esc_html($transaction->order_id) . '</span><span>'.esc_html__('View', 'latepoint').'</span><i class="latepoint-icon latepoint-icon-external-link"></i></a>'; ?>
				<?php } else {
					echo 'n/a';
				} ?>
			</td>
			<td>

				<?php if ($transaction->order_id) { ?>

        <a class="os-with-avatar" target="_blank" <?php echo OsCustomerHelper::quick_customer_btn_html($transaction->customer->id); ?> href="#">
          <span class="os-avatar" style="background-image: url(<?php echo esc_url($transaction->customer->get_avatar_url()); ?>)"></span>
          <span class="os-name"><?php esc_html($transaction->customer->full_name); ?></span>
	        <i class="latepoint-icon latepoint-icon-external-link"></i>
        </a>
				<?php } else {
					echo 'n/a';
				} ?>
			</td>
			<td>
				<div class="lp-processor-logo lp-processor-logo-<?php echo esc_attr($transaction->processor); ?>"><?php echo esc_html(OsPaymentsHelper::get_nice_payment_processor_name($transaction->processor)); ?></div>
			</td>
			<td>
				<div class="lp-method-logo lp-method-logo-<?php echo esc_attr($transaction->payment_method); ?>"><?php echo esc_html(OsPaymentsHelper::get_nice_payment_method_name($transaction->payment_method)); ?></div>
			</td>
			<td><?php echo esc_html(OsMoneyHelper::format_price($transaction->amount, true, false)); ?></td>
			<td><span class="lp-transaction-status lp-transaction-status-<?php echo esc_attr($transaction->status); ?>"><?php echo esc_html(OsPaymentsHelper::get_nice_transaction_status_name($transaction->status)); ?></span>
			<td><span class="lp-transaction-status lp-transaction-funds-status-<?php echo esc_attr($transaction->kind); ?>"><?php echo esc_html(OsPaymentsHelper::get_nice_transaction_kind_name($transaction->kind)); ?></span>
			</td>
			<td><?php echo esc_html($transaction->created_at); ?></td>
		</tr>
		<?php
	}
} ?>