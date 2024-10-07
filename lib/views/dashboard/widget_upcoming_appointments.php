<?php
/* @var $booking OsBookingModel */
/* @var $upcoming_bookings OsBookingModel[] */
/* @var $locations OsLocationModel[] */
/* @var $agents OsAgentModel[] */
/* @var $services OsServiceModel[] */

/* @var $selected_location_id string|bool */
/* @var $selected_agent_id string|bool */
/* @var $selected_service_id string|bool */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="os-widget os-widget-animated os-widget-upcoming-appointments" data-os-reload-action="<?php echo esc_attr(OsRouterHelper::build_route_name('dashboard', 'widget_upcoming_appointments')); ?>">
	<div class="os-widget-header with-actions">
		<h3 class="os-widget-header-text"><?php esc_html_e('Upcoming', 'latepoint'); ?></h3>
		<div class="os-widget-header-actions-trigger"><i class="latepoint-icon latepoint-icon-more-horizontal"></i></div>
		<div class="os-widget-header-actions">
			<select name="location_id" class="os-trigger-reload-widget">
				<option value=""><?php esc_html_e('All locations', 'latepoint'); ?></option>
				<?php
				if($locations){
					foreach($locations as $location){ ?>
					<option value="<?php echo esc_attr($location->id); ?>" <?php if($location->id == $selected_location_id) echo 'selected="selected"' ?>><?php echo esc_html($location->name); ?></option>
					<?php }
				} ?>
			</select>
			<?php if(count($agents)>1){ ?>
			<select name="agent_id" id="" class="os-trigger-reload-widget">
				<option value=""><?php esc_html_e('All Agents', 'latepoint'); ?></option>
				<?php
				if($agents){
					foreach($agents as $agent){ ?>
					<option value="<?php echo esc_attr($agent->id); ?>" <?php if($agent->id == $selected_agent_id) echo 'selected="selected"' ?>><?php echo esc_html($agent->full_name); ?></option>
					<?php }
				} ?>
			</select>
			<?php } ?>
			<select name="service_id" id="" class="os-trigger-reload-widget">
				<option value=""><?php esc_html_e('All Services', 'latepoint'); ?></option>
				<?php
				if($services){
					foreach($services as $service){ ?>
					<option value="<?php echo esc_attr($service->id); ?>" <?php if($service->id == $selected_service_id) echo 'selected="selected"' ?>><?php echo esc_html($service->name); ?></option>
					<?php }
				} ?>
			</select>
		</div>		
	</div>
	<div class="os-widget-content no-padding">
		<div class="appointment-boxes-squared-w">
			<div class="appointment-boxes-caption" style="display:none;"><div><?php esc_html_e('Upcoming', 'latepoint'); ?></div></div>
			<?php if($upcoming_bookings){ ?>
			<?php foreach($upcoming_bookings as $booking){ 
				$max_capacity = OsServiceHelper::get_max_capacity($booking->service); ?>
				<div class="appointment-box-squared" <?php echo ($max_capacity > 1) ? OsBookingHelper::group_booking_btn_html($booking->id) : OsBookingHelper::quick_booking_btn_html($booking->id); ?>>
					<div class="appointment-main-info">
						<div class="appointment-color-elem" style="background-color: <?php echo esc_attr($booking->service->bg_color); ?>"></div>
						<div class="appointment-main-info-i">
				      <div class="avatar-w" style="background-image: url(<?php echo esc_url($booking->agent->get_avatar_url()); ?>);">
				        <div class="agent-info-tooltip"><?php echo esc_html($booking->agent->full_name); ?></div>
				      </div>
							<div class="appointment-date-w">
								<div class="appointment-time-left">
                                    <?php
                                    // translators: %s is time left
                                    echo sprintf(esc_html__('in %s', 'latepoint'), $booking->time_left); ?>
                                </div>
								<div class="appointment-service-name"><?php echo esc_html($booking->service->name); ?></div>
								<div class="appointment-date-i">
									<div class="appointment-date"><?php echo esc_html($booking->get_nice_start_date(true).', '); ?></div>
									<div class="appointment-time"><?php echo esc_html($booking->get_nice_start_time()); ?></div>
								</div>
							</div>
						</div>
						<div class="appointment-link">
							<i class="latepoint-icon latepoint-icon-arrow-right"></i>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php }else{ ?>
		  <div class="no-results-w">
		    <div class="icon-w"><i class="latepoint-icon latepoint-icon-inbox"></i></div>
		    <div class="count-label""><?php esc_html_e('No Upcoming Appointments', 'latepoint'); ?></div class=count-label"">
		    <a href="#" <?php echo OsOrdersHelper::quick_order_btn_html(); ?> class="latepoint-btn latepoint-btn-link"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('Add Appointment', 'latepoint'); ?></span></a>
		  </div>
		<?php } ?>
		</div>
	</div>
</div>