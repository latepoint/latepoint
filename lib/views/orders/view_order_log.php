<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $activities OsActivityModel[] */
/* @var $order OsOrderModel */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="booking-activity-log-panel-w side-sub-panel-wrapper">
	<div class="side-sub-panel-header os-form-header">
		<h2><?php esc_html_e('Activity Log', 'latepoint'); ?></h2>
		<a href="#" class="booking-activity-log-panel-close  latepoint-side-sub-panel-close latepoint-side-sub-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
	</div>
	<div class="side-sub-panel-content booking-activity-log-panel-i">
		<div class="booking-activities-list">
			<div class="quick-booking-info">
				<?php if($order->ip_address) echo '<span>'.esc_html__('IP Address: ', 'latepoint').'</span><strong>'.esc_html($order->ip_address).'</strong>'; ?>
				<?php if($order->source_id) echo '<span>'.esc_html__('Source ID: ', 'latepoint').'</span><strong>'.esc_html($order->source_id).'</strong>'; ?>
				<?php echo '<a href="'.esc_url($order->source_url).'" target="_blank"><i class="latepoint-icon latepoint-icon-external-link"></i>'.esc_html__('Booking Page', 'latepoint').'</a>'; ?>
			</div>
			<?php
			foreach ($activities as $activity) {
				echo '<div class="booking-activity-row">';
					echo '<div class="booking-activity-name">' . esc_html($activity->name) . '</div>';
					echo '<div class="spacer"></div>';
					echo '<div class="booking-activity-date">' . esc_html($activity->nice_created_at) . '</div>';
					echo $activity->get_link_to_object('<i class="latepoint-icon latepoint-icon-file-text"></i>');
				echo '</div>';
			}
			?>
		</div>
	</div>
</div>
