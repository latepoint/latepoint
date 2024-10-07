<?php
/**
 * @var $current_step_code string
 * @var $booking OsBookingModel
 * @var $restrictions array
 * @var $cart OsCartModel
 * @var $cart_items OsCartItemModel[]
 * @var $price_breakdown_rows array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-verify-w latepoint-step-content" data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">
	<?php do_action('latepoint_before_step_content', $current_step_code); ?>
	<?php echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before'); ?>
	<div class="latepoint-cart-items">
		<div class="confirmation-info-w">
			<?php
			if($booking->order_item_id){
				// scheduling a purchased bundle from existing order
				$order_item = new OsOrderItemModel($booking->order_item_id);
				$order = new OsOrderModel($order_item->order_id);
				echo '<div class="scheduling-bundle-booking-summary">';
					// scheduling an already ordered item
					echo OsBookingHelper::generate_summary_for_booking($booking);
                    // translators: %s is confirmation code
					echo '<div class="part-of-bundle-message">'.esc_html(sprintf(__('Scheduling this booking will use a slot from a bundle which is part of order #%s.', 'latepoint'), $order->confirmation_code)).'</div>';
					echo '<div class="booking-summary-info-w">';
						echo '<div class="summary-boxes-columns">';
						if (OsAgentHelper::count_agents() > 1) OsAgentHelper::generate_summary_for_agent($booking);
						OsLocationHelper::generate_summary_for_location($booking);
						OsCustomerHelper::generate_summary_for_customer($booking->customer);
						echo '</div>';
					echo '</div>';
				echo '</div>';
			}else{
				// new order
				$output_target = 'step_verify';
				include('partials/_cart_summary.php');
			}
			 ?>
		</div>
	</div>
	<?php echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after'); ?>
	<?php do_action('latepoint_after_step_content', $current_step_code); ?>
	<?php
	if(empty($booking->order_item_id)){
		// regular booking
		do_action('latepoint_after_verify_step_content', $current_step_code);
	}else{
		// scheduling a purchased bundle
	} ?>
</div>