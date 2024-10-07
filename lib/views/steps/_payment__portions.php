<?php
/**
 * @var $current_step_code string
 * @var $cart OsCartModel
 * @var $restrictions array
 * @var $presets array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-payment-portions-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">

	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');
	?>
	<div class="lp-payment-portions-w">
		<div class="lp-options lp-options-grid lp-options-grid-two">
			<div class="lp-option lp-payment-trigger-payment-portion-selector"
                 tabindex="0"
			     data-holder="cart[payment_portion]"
			     data-value="<?php echo esc_attr(LATEPOINT_PAYMENT_PORTION_FULL); ?>">
				<div class="lp-option-amount-w">
					<div class="lp-option-amount lp-amount-full">
						<div class="lp-amount-value"><?php echo esc_html($cart->get_total_formatted()); ?></div>
					</div>
				</div>
				<div class="lp-option-label"><?php esc_html_e('Full Amount', 'latepoint'); ?></div>
			</div>
			<div class="lp-option lp-payment-trigger-payment-portion-selector"
                 tabindex="0"
			     data-holder="cart[payment_portion]"
			     data-value="<?php echo esc_attr(LATEPOINT_PAYMENT_PORTION_DEPOSIT); ?>">
				<div class="lp-option-amount-w">
					<div class="lp-option-amount lp-amount-deposit">
						<div class="lp-slice"></div>
						<div class="lp-amount-value"><?php echo esc_html($cart->deposit_amount_to_charge_formatted()); ?></div>
					</div>
				</div>
				<div class="lp-option-label"><?php esc_html_e('Deposit Only', 'latepoint'); ?></div>
			</div>
		</div>
	</div>
	<?php
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	?>
	<?php
	echo OsFormHelper::hidden_field('cart[payment_portion]', '', ['class' => 'latepoint_cart_payment_portion', 'skip_id' => true]);
	?>
</div>