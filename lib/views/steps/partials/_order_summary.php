<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="order-summary-main-section <?php echo (count($order->get_items()) > 1) ? 'multi-item' : 'single-item'; ?>">
<?php
	$order_bookings = $order->get_bookings_from_order_items();
	$order_bundles = $order->get_bundles_from_order_items();

		?>

        <div class="order-summary-items-heading">
            <?php esc_html_e( 'Order Items', 'latepoint' ); ?>
            <div class="osih-line"></div>
        </div>
		<?php

	if ($order_bundles) {
		foreach ($order_bundles as $order_item_id => $bundle) {
			echo '<div class="summary-box-wrapper">';
                echo '<div class="order-item-bundle-info-wrapper">';
                    echo '<div class="bundle-icon"><i class="latepoint-icon latepoint-icon-layers"></i></div>';
                    echo OsBundlesHelper::generate_summary_for_bundle($bundle);
                echo '</div>';
                echo '<div class="schedule-bundle-booking-btn-wrapper">';
                echo '<div class="schedule-bundle-booking-btn" '.OsCustomerHelper::generate_bundle_scheduling_btn($order_item_id).'><span>'.esc_html__('Start Scheduling', 'latepoint').'</span><i class="latepoint-icon latepoint-icon-arrow-2-right"></i></div>';
                echo '</div>';
			echo '</div>';
		}
	}

	if ($order_bookings) {

		$same_location = OsBookingHelper::bookings_have_same_location($order_bookings);
		$same_agent = OsBookingHelper::bookings_have_same_agent($order_bookings);

		foreach ($order_bookings as $order_item_id => $order_booking) {
			echo '<div class="summary-box-wrapper">';
			echo OsBookingHelper::generate_summary_for_booking($order_booking, false);
            OsBookingHelper::generate_summary_actions_for_booking($order_booking);
			if (!$same_agent || !$same_location) {
				echo '<div class="booking-summary-info-w">';
				echo '<div class="summary-boxes-columns">';
				if (!$same_agent  && (OsAgentHelper::count_agents() > 1)) OsAgentHelper::generate_summary_for_agent($order_booking);
				if (!$same_location) OsLocationHelper::generate_summary_for_location($order_booking);
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
		}
	}


	if ($order_bookings) {
		echo '<div class="booking-summary-info-w">';
		echo '<div class="summary-boxes-columns">';
		if ($same_agent  && (OsAgentHelper::count_agents() > 1)) OsAgentHelper::generate_summary_for_agent(reset($order_bookings));
		if ($same_location) OsLocationHelper::generate_summary_for_location(reset($order_bookings));
		OsCustomerHelper::generate_summary_for_customer($order->customer);
		echo '</div>';
		echo '</div>';
	}else{
		echo '<div class="booking-summary-info-w">';
			echo '<div class="summary-boxes-columns">';
				OsCustomerHelper::generate_summary_for_customer($order->customer);
			echo '</div>';
		echo '</div>';
	}

do_action('latepoint_order_summary_before_price_breakdown', $order);
?>
</div>
<?php if( $order->get_subtotal() > 0 || OsSettingsHelper::is_off('hide_breakdown_if_subtotal_zero')){ ?>
<div class="summary-price-breakdown-wrapper">
	<div class="pb-heading">
		<div class="pbh-label"><?php esc_html_e('Cost Breakdown', 'latepoint'); ?></div>
		<div class="pbh-line"></div>
	</div>
	<?php
	$price_breakdown_rows = $order->generate_price_breakdown_rows();
	OsPriceBreakdownHelper::output_price_breakdown($price_breakdown_rows);
	?>
</div>
<?php } ?>