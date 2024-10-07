<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */
/* @var $price_breakdown_rows array */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>

<?php
do_action('latepoint_order_quick_form_price_before_subtotal', $order);
foreach($price_breakdown_rows['before_subtotal'] as $row){
	OsBookingHelper::output_price_breakdown_row_as_input_field($row, 'price_breakdown[before_subtotal]');
}
echo OsFormHelper::money_field('order[subtotal]', __('Sub Total', 'latepoint'), $order->get_subtotal() ?? 0, ['theme' => 'right-aligned'], [], ['class' => 'os-subtotal']);
foreach($price_breakdown_rows['after_subtotal'] as $row){
	OsBookingHelper::output_price_breakdown_row_as_input_field($row, 'price_breakdown[after_subtotal]');
}
echo OsFormHelper::money_field('order[total]', __('Total Price', 'latepoint'), $order->get_total() ?? 0, ['theme' => 'right-aligned', 'class' => 'os-affects-balance'], [], ['class' => 'os-total']);
do_action('latepoint_order_quick_form_price_after_total', $order);
