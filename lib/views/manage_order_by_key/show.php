<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/**
 * @var $order OsOrderModel
 * @var $for string
 * @var $key string
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="manage-order-wrapper" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('manage_order_by_key', 'show')); ?>" data-key="<?php echo esc_attr($key); ?>">
		<?php
        if($for == 'agent'){
            echo '<div class="manage-booking-controls status-'.esc_attr($order->status).'">';
                echo '<div class="change-booking-status-trigger-wrapper" data-route-name="'.esc_attr(OsRouterHelper::build_route_name('manage_order_by_key', 'change_status')).'">';
                echo OsFormHelper::select_field('booking[status]', __('Status:', 'latepoint'), OsBookingHelper::get_statuses_list(), $order->status, ['id' => 'booking_status_'.$order->id, 'class' => 'change-booking-status-trigger']);
                echo '</div>';
			echo '</div>';
		}else{
            if($order->status == LATEPOINT_ORDER_STATUS_CANCELLED){ ?>
            <div class="manage-booking-controls status-<?php echo esc_attr($order->status); ?>">
                <div class="manage-status-info">
                    <span class="status-info-value status-<?php echo esc_attr($order->status); ?>"><?php echo esc_html($order->get_nice_status_name()); ?></span>
                </div>
			</div>
			<?php
            }
		}?>
	<div class="manage-booking-inner">
		<?php include(LATEPOINT_VIEWS_ABSPATH.'orders/_full_summary.php'); ?>
	</div>
</div>
