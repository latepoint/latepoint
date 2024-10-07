<?php
/**
 * @var $current_step_code string
 * @var $cart OsCartModel
 * @var $restrictions array
 * @var $presets array
 * @var $enabled_payment_processors array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-payment-processors-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">
	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');
	?>
	<div class="lp-payment-processors-w">
		<div class="lp-options lp-options-grid lp-options-grid-three">
			<?php foreach ($enabled_payment_processors as $pay_processor_code => $pay_processor) { ?>
				<?php
				$pay_processor['label'] = $pay_processor['front_name'] ?? $pay_processor['name'];
				$pay_processor['css_class'] = $pay_processor['css_class'] ?? 'lp-payment-trigger-payment-processor-selector';
				$pay_processor['attrs'] = $pay_processor['attrs'] ?? ' data-holder="cart[payment_processor]" data-value="' . esc_attr($pay_processor_code) . '" ';
				echo OsStepsHelper::output_list_option($pay_processor); ?>
			<?php } ?>
		</div>
	</div>
	<?php
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	?>
	<?php echo OsFormHelper::hidden_field('cart[payment_processor]', $cart->payment_processor, ['class' => 'latepoint_cart_payment_processor', 'skip_id' => true]); ?>
</div>