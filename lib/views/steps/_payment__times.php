<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/**
 * @var $current_step_code string
 * @var $restrictions array
 * @var $presets array
 * @var $cart OsCartModel
 * @var $enabled_payment_times array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-payment-times-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">

	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');
	?>

		<div class="lp-payment-times-w">
			<div class="latepoint-step-content-text-centered">
				<h4><?php esc_html_e('When would you like to pay for the service?', 'latepoint'); ?></h4>
				<div><?php esc_html_e('You can either pay now or pay locally on arrival. You will be able to select payment method in the next step.', 'latepoint'); ?></div>
			</div>
			<div class="lp-options lp-options-grid lp-options-grid-three">
				<?php foreach ($enabled_payment_times as $pay_time_name => $pay_time_methods) {
					$option = reset($pay_time_methods);
					$option['label'] = ($pay_time_name == 'now') ? __('Pay Now', 'latepoint') : __('Pay Later', 'latepoint');
					$option['image_url'] = ($pay_time_name == 'now') ? LATEPOINT_IMAGES_URL . 'payment_now.png' : LATEPOINT_IMAGES_URL . 'payment_later.png';
					$option['css_class'] = 'lp-payment-trigger-payment-time-selector';
					$option['attrs'] = 'data-value="' . esc_attr($pay_time_name) . '" data-holder="cart[payment_time]"';
					echo OsStepsHelper::output_list_option($option);
				} ?>
			</div>
		</div>
	<?php
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	?>
	<?php echo OsFormHelper::hidden_field('cart[payment_time]', '', ['class' => 'latepoint_cart_payment_time', 'skip_id' => true]); ?>
</div>