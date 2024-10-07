<?php
/* @var $booking OsBookingModel */
/* @var $current_step_code string */
/* @var $restrictions array */
/* @var $presets array */
/* @var $active_cart_item OsCartItemModel */
/* @var $cart OsCartModel */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-order-form-params-w">
	<?php 
	$add_string_to_id = '_'.OsUtilHelper::random_text('hexdec', 8);

	echo '<div class="latepoint-restrictions">';
		// restrictions
		echo OsFormHelper::hidden_field('restrictions[show_locations]', $restrictions['show_locations'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_agents]', $restrictions['show_agents'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_services]', $restrictions['show_services'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_service_categories]', $restrictions['show_service_categories'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[calendar_start_date]', $restrictions['calendar_start_date'], ['skip_id' => true]);

	echo '</div>';
	echo '<div class="latepoint-presets">';
		// presets
		echo OsFormHelper::hidden_field('presets[selected_bundle]', $presets['selected_bundle'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_service]', $presets['selected_service'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_service_category]', $presets['selected_service_category'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_duration]', $presets['selected_duration'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_total_attendees]', $presets['selected_total_attendees'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_location]', $presets['selected_location'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_agent]', $presets['selected_agent'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_start_date]', $presets['selected_start_date'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[selected_start_time]', $presets['selected_start_time'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[source_id]', $presets['source_id'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('presets[order_item_id]', $presets['order_item_id'], ['skip_id' => true]);
	echo '</div>';

	echo OsFormHelper::hidden_field('current_step_code', $current_step_code, ['class' => 'latepoint_current_step_code', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('step_direction', 'next', ['class' => 'latepoint_step_direction', 'skip_id' => true]);

	echo OsFormHelper::hidden_field('active_cart_item[id]', $active_cart_item->id ?? '', ['class' => 'latepoint_active_cart_item_id', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('active_cart_item[variant]', $active_cart_item->variant, ['class' => 'latepoint_active_cart_item_variant', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('active_cart_item[item_data]', $active_cart_item->item_data, ['class' => 'latepoint_active_cart_item_item_data', 'skip_id' => true]);

	/**
	 * Fired after booking form parameters
	 *
	 * @since 5.0.0
	 * @hook latepoint_booking_form_params
	 *
	 * @param {OsBookingModel} $booking Booking object
	 * @param {array} $restrictions array of restrictions
	 * @param {array} $presets array of presets
	 * @param {string} $current_step_code current step code
	 * @param {string} $add_string_to_id hex string to be added to ID of form fields
	 */
	do_action('latepoint_booking_form_params', $booking, $restrictions, $presets, $current_step_code, $add_string_to_id);
	?>
</div>