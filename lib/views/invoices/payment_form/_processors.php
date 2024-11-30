<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $enabled_payment_processors array */
/* @var $form_prev_button string */
/* @var $form_next_button string */
?>
<?php
foreach ( $enabled_payment_processors as $pay_processor_code => $pay_processor ) {
	$pay_processor['label']     = $pay_processor['front_name'] ?? $pay_processor['name'];
	$pay_processor['css_class'] = $pay_processor['css_class'] ?? 'lp-payment-trigger-payment-processor-selector';
	$pay_processor['attrs']     = $pay_processor['attrs'] ?? ' data-holder="payment_processor" data-value="' . esc_attr( $pay_processor_code ) . '" ';
	$form_content               = OsStepsHelper::output_list_option( $pay_processor );
}
?>