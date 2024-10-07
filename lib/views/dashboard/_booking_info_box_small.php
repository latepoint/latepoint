<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="appointment-box-small" <?php echo OsBookingHelper::quick_booking_btn_html($booking->id); ?>>
	<div class="appointment-info">
		<div class="appointment-color-elem" style="background-color: <?php echo esc_attr($booking->service->bg_color); ?>"></div>
		<div class="appointment-service-name"><?php echo esc_html($booking->service->name); ?></div>
		<div class="appointment-time">
			<div class="at-date"><?php echo esc_html($booking->nice_start_date); ?></div>
			<div class="at-time"><?php echo esc_html(implode('-', array($booking->nice_start_time, $booking->nice_end_time))); ?></div>
		</div>
	</div>
	<?php if(!isset($hide_customer_info)){ ?>
	<div class="customer-info-w">
		<div class="avatar-w" style="background-image: url(<?php echo esc_url($booking->customer->get_avatar_url()); ?>);"></div>
		<div class="customer-info">
			<div class="customer-name"><?php echo esc_html($booking->customer->full_name); ?></div>
			<div class="customer-property">
				<span class="label"><?php esc_html_e('Phone: ', 'latepoint'); ?></span>
				<span class="value"><?php echo esc_html($booking->customer->phone); ?></span>
			</div>
			<div class="customer-property">
				<span class="label"><?php esc_html_e('Email: ', 'latepoint'); ?></span>
				<span class="value"><?php echo esc_html($booking->customer->email); ?></span>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php if(!isset($hide_agent_info)){ ?>
    <div class="agent-info-w">
      <div class="avatar-w" style="background-image: url(<?php echo esc_url($booking->agent->get_avatar_url()); ?>);"></div>
      <div class="agent-info">
        <div class="agent-name"><?php echo esc_html($booking->agent->full_name); ?></div>
        <div class="agent-property">
          <span class="label"><?php esc_html_e('Phone: ', 'latepoint'); ?></span>
          <span class="value"><?php echo esc_html($booking->agent->phone); ?></span>
        </div>
        <div class="agent-property">
          <span class="label"><?php esc_html_e('Email: ', 'latepoint'); ?></span>
          <span class="value"><?php echo esc_html($booking->agent->email); ?></span>
        </div>
      </div>
    </div>
	<?php } ?>
	<?php $max_capacity = OsServiceHelper::get_max_capacity($booking->service); ?>
	<?php if($max_capacity > 1){
		$total_attendees_in_group = (isset($total_attendees_in_group)) ? $total_attendees_in_group + $booking->total_attendees : $booking->total_attendees;
		$css_width = min(((max($total_attendees_in_group, 1) / $max_capacity) * 100), 100); ?>
		<div class="appointment-capacity-info">
			<div class="appointment-capacity-info-label">
				<strong><?php echo esc_html(max($total_attendees_in_group, 1).' '.__('of', 'latepoint').' '.$max_capacity); ?></strong>
				<span><?php esc_html_e('Slots Booked', 'latepoint'); ?></span>
			</div>
			<div class="appointment-capacity-progress-w">
				<div class="appointment-capacity-progress" style="width: <?php echo esc_attr($css_width); ?>%;"></div>
			</div>
		</div>
	<?php } ?>
</div>