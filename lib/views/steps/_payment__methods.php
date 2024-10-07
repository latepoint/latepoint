<?php
/**
 * @var $current_step_code string
 * @var $cart OsCartModel
 * @var $restrictions array
 * @var $presets array
 * @var $enabled_payment_methods array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-payment-methods-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">
	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');
	?>
	<div class="lp-payment-methods-w">
		<div class="lp-options lp-options-grid lp-options-grid-three">
			<?php foreach ($enabled_payment_methods as $pay_method_code => $pay_method_processors) {
				$pay_method_info = reset($pay_method_processors);
				$pay_method_info['css_class'] = $pay_method_info['css_class'] ?? 'lp-payment-trigger-payment-method-selector';
				$pay_method_info['attrs'] = $pay_method_info['attrs'] ?? ' data-holder="cart[payment_method]" data-value="' . esc_attr($pay_method_code) . '" ';
				echo OsStepsHelper::output_list_option($pay_method_info); ?>
			<?php } ?>
		</div>
	</div>
	<?php
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	?>
	<?php echo OsFormHelper::hidden_field('cart[payment_method]', $cart->payment_method, ['class' => 'latepoint_cart_payment_method', 'skip_id' => true]); ?>
</div>