<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $transaction OsTransactionModel */


?>
<a href="#" class="latepoint-lightbox-close" tabindex="0"><i class="latepoint-icon latepoint-icon-x"></i></a>
<div class="payment-confirmation-wrapper">
	<div class="icon-w a-rotate-scale">
		<i class="latepoint-icon latepoint-icon-check"></i>
	</div>
	<h2 class="a-up-20 a-delay-1"><?php echo esc_html__('Thank you for your payment', 'latepoint'); ?></h2>
	<div class="payment-info a-up-20 a-delay-3">
        <div class="payment-info-row">
            <div class="info-label"><?php esc_html_e('Date:', 'latepoint'); ?></div>
            <div class="info-value">
                <?php echo esc_html(OsTimeHelper::get_readable_date(new OsWpDateTime($transaction->created_at))); ?>
            </div>
        </div>
        <div class="payment-info-row">
            <div class="info-label"><?php esc_html_e('Amount:', 'latepoint'); ?></div>
            <div class="info-value">
                <?php echo esc_html(OsMoneyHelper::format_price($transaction->amount, true, false)); ?>
            </div>
        </div>
        <div class="payment-info-row">
            <div class="info-label"><?php esc_html_e('Confirmation:', 'latepoint'); ?></div>
            <div class="info-value">
                <?php echo esc_html($transaction->token); ?>
            </div>
        </div>
	</div>
</div>
<?php
/**
 * Content for order payment - confirmation step
 *
 * @param {OsTransactionModel} transaction model
 *
 * @since 5.0.15
 * @hook latepoint_order_payment__confirmation_content
 *
 */
do_action( 'latepoint_order_payment__confirmation_content', $transaction );