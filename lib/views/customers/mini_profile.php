<?php
/** @var $customer OsCustomerModel */
/** @var $upcoming_booking OsBookingModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-mini-customer-profile-w">
	<a href="#" class="os-floating-popup-close"><i class="latepoint-icon latepoint-icon-x"></i></a>
	<div class="os-mc-info-w">
		<div class="os-mc-avatar" style="background-image: url(<?php echo esc_url($customer->get_avatar_url()); ?>)"></div>
		<div class="os-mc-info">
			<div class="mc-name"><?php echo esc_html($customer->full_name); ?><a target="_blank" <?php echo OsCustomerHelper::quick_customer_btn_html($customer->id); ?> href="#"><i class="latepoint-icon latepoint-icon-external-link"></i></a></div>
			<?php if (!empty($customer->email)) { ?><div class="mc-info-list-item"><span><?php esc_html_e('email:', 'latepoint'); ?></span><strong><?php echo esc_html($customer->email); ?></strong></div><?php } ?>
			<?php if (!empty($customer->phone)) { ?><div class="mc-info-list-item"><span><?php esc_html_e('phone:', 'latepoint'); ?></span><strong><?php echo esc_html($customer->phone); ?></strong></div><?php } ?>
		</div>
	</div>
	<div class="os-mc-sub-info">
		<div class="os-mc-chart">
			<?php if(isset($pie_chart_data) && !empty($pie_chart_data['values'])){ ?>
				<div class="os-mc-heading"><?php esc_html_e('Total', 'latepoint'); ?></div>
				<div class="os-mc-chart-i">
					<div class="os-mc-totals"><?php echo esc_html($customer->get_total_bookings_count(true)); ?></div>
					<canvas class="os-customer-donut-chart" width="90" height="90"  
						data-chart-labels="<?php echo esc_attr(implode(',', $pie_chart_data['labels'])); ?>"
						data-chart-colors="<?php echo esc_attr(implode(',', $pie_chart_data['colors'])); ?>"
						data-chart-values="<?php echo esc_attr(implode(',', $pie_chart_data['values'])); ?>"></canvas>
					</div>
			<?php } ?>
		</div>
		<div class="os-mc-upcoming-appointments-w">
			<div class="os-mc-heading"><?php esc_html_e('Next Appointment', 'latepoint'); ?></div>
			<div class="os-mc-upcoming-appointments">
				<?php if($upcoming_booking){ ?>
					<div class="os-upcoming-appointment" <?php echo OsBookingHelper::quick_booking_btn_html($upcoming_booking->id); ?>>
						<div class="appointment-color-elem" style="background-color: <?php echo esc_attr($upcoming_booking->service->bg_color); ?>"></div>
						<div class="appointment-service-name"><?php echo esc_html($upcoming_booking->service->name); ?></div>
						<div class="appointment-date-w">
							<div class="appointment-date-i">
								<div class="appointment-date"><?php echo esc_html($upcoming_booking->nice_start_date); ?></div>
								<div class="appointment-time"><?php echo esc_html(implode('-', array($upcoming_booking->nice_start_time, $upcoming_booking->nice_end_time))); ?></div>
							</div>
				      <div class="avatar-w" style="background-image: url(<?php echo esc_url($upcoming_booking->agent->get_avatar_url()); ?>);">
				      	<div class="agent-info-tooltip"><?php echo esc_html($upcoming_booking->agent->full_name); ?></div>
				      </div>
						</div>
					</div>
					<?php
				}else{
					echo '<div class="os-nothing">'.esc_html__('No Upcoming Appointments', 'latepoint').'</div>';
				} ?>
			</div>
		</div>
	</div>
</div>