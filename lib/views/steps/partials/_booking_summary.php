<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */
/* @var $booking OsBookingModel */
/* @var $key ?string */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="booking-summary-main-section">
<?php
echo '<div class="summary-box-wrapper">';
	echo OsBookingHelper::generate_summary_for_booking($booking, false);
	OsBookingHelper::generate_summary_actions_for_booking($booking);

    ?>

    <?php
	ob_start();
	OsAgentHelper::generate_summary_for_agent($booking);
	OsLocationHelper::generate_summary_for_location($booking);
	OsCustomerHelper::generate_summary_for_customer($booking->customer);
  $other_summary_html = ob_get_clean();
	if(!empty($other_summary_html)){
		echo '<div class="booking-summary-info-w">';
			echo '<div class="summary-boxes-columns">';
				echo $other_summary_html;
			echo '</div>';
		echo '</div>';
	}
echo '</div>';
do_action('latepoint_booking_summary_before_price_breakdown', $booking->order);
?>
</div>
<?php
// single item in order, show price breakdown here
if(count($booking->order->get_items()) == 1){
	if($booking->is_part_of_bundle()){
        if(!empty($order_manage_key)){
            $order_link_html = '<a href="'.OsRouterHelper::build_admin_post_link( [ 'manage_order_by_key', 'show' ], [ 'key' => $order_manage_key ] ).'">'.__('Manage Order', 'latepoint').'</a>';
        }else{
            $order_link_html = '<a href="#" '.OsCustomerHelper::generate_bundle_scheduling_btn($booking->order_item_id).'>'.__('Show Details', 'latepoint').'</a>';
        }
        echo '<div class="part-of-bundle-message">'.esc_html__('This booking is part of a bundle.', 'latepoint').' '.$order_link_html.'</div>';
	}else{
        if( $booking->order->get_subtotal() > 0 || OsSettingsHelper::is_off('hide_breakdown_if_subtotal_zero')){ ?>
		<div class="summary-price-breakdown-wrapper">
			<div class="pb-heading">
				<div class="pbh-label"><?php esc_html_e('Cost Breakdown', 'latepoint'); ?></div>
				<div class="pbh-line"></div>
			</div>
			<?php
			$price_breakdown_rows = $booking->order->generate_price_breakdown_rows();
			OsPriceBreakdownHelper::output_price_breakdown($price_breakdown_rows);
			?>
		</div>
		<?php
        }
	}
	?>
	<?php
}else{
    if(!empty($order_manage_key)){
        $order_link_html = '<a href="'.OsRouterHelper::build_admin_post_link( [ 'manage_order_by_key', 'show' ], [ 'key' => $order_manage_key ] ).'">'.__('Manage Order', 'latepoint').'</a>';
    }else{
        $order_link_html = '<a href="#" '.OsCustomerHelper::generate_order_summary_btn($booking->order->id).'>'.__('Show Order', 'latepoint').'</a>';
    }
	echo '<div class="part-of-bundle-message">'.esc_html__('This booking is part of a bigger order.', 'latepoint').' '.$order_link_html.'</div>';
}
?>