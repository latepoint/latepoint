<?php
/* @var $transactions OsTransactionModel[] */
/* @var $order OsOrderModel */
?>
<div class="clean-layout-content-wrapper">
    <div class="order-payments-form-wrapper clean-layout-content-body is-dotted">
        <div class="total-order-payments-info">
            <div class="topi-amount"><?php echo OsMoneyHelper::format_price($order->get_total_amount_paid_from_transactions(), true, false); ?></div>
            <div class="topi-sub-info">
                <?php esc_html_e('Total payments for order:', 'latepoint'); ?>
                <a href="<?php echo $order->manage_by_key_url('customer') ?>" target="_blank"><span><?php echo $order->confirmation_code; ?></span><i class="latepoint-icon latepoint-icon-external-link"></i></a>
            </div>
        </div>
	<?php
	if($transactions){
        echo '<div class="topi-heading"><div>'.__('Payments', 'latepoint').'</div><div class="topih-line"></div></div>';
		foreach($transactions as $transaction){ ?>
            <div class="topi-transaction">
                <div class="topit-amount"><?php echo OsMoneyHelper::format_price($transaction->amount, true, false); ?></div>
                <div class="topit-sub-info">
                    <div class="topit-confirmation-number"><?php echo $transaction->token; ?></div>
                    <div class="topit-date"><?php echo esc_html(OsTimeHelper::get_readable_date(new OsWpDateTime($transaction->created_at))); ?></div>
                </div>
                <div class="topit-transaction-status topit-transaction-status-<?php echo esc_attr($transaction->status); ?>"><?php echo esc_html(OsPaymentsHelper::get_nice_transaction_status_name($transaction->status)); ?></div>
            </div>
			<?php
		}
	}else{
		echo 'No Transactions';
	}?>
    </div>
</div>