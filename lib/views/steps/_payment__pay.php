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
<div class="step-payment-pay-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">

	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');

    $order_intent = $cart->get_order_intent();
    if(!$order_intent->is_new_record()) echo '<div class="lp-payment-charge-amount">'.sprintf(esc_html__('Charge Amount: %s', 'latepoint'), '<strong>'.OsMoneyHelper::format_price($order_intent->charge_amount, true, false).'</strong>').'</div>';

	do_action('latepoint_step_payment__pay_content', $cart);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	if(empty($cart->payment_time)){
		$cart->set_payment_time();
		echo OsFormHelper::hidden_field('cart[payment_time]', $cart->payment_time);
	}
	if(empty($cart->payment_portion)){
		$cart->set_payment_portion();
		echo OsFormHelper::hidden_field('cart[payment_portion]', $cart->payment_portion);
	}
	if(empty($cart->payment_method)){
		$cart->set_payment_method();
		echo OsFormHelper::hidden_field('cart[payment_method]', $cart->payment_method);
	}
	if(empty($cart->payment_processor)){
		$cart->set_payment_processor();
		echo OsFormHelper::hidden_field('cart[payment_processor]', $cart->payment_processor);
	}
	echo OsFormHelper::hidden_field('cart[payment_token]', '');
	?>
</div>