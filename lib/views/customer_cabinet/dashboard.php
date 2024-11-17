<?php
/** @var $customer OsCustomerModel */
/** @var $future_bookings OsBookingModel[] */
/** @var $past_bookings OsBookingModel[] */
/** @var $cancelled_bookings OsBookingModel[] */
/** @var $not_scheduled_bundles OsBundleModel[] */
/** @var $orders OsOrderModel[] */
/** @var $cart_not_empty bool */
/** @var $hide_new_appointment_ui bool */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-w">
	<a href="<?php echo esc_url(OsRouterHelper::build_admin_post_link(['customer_cabinet', 'logout'] )); ?>"><?php esc_html_e('Logout', 'latepoint'); ?></a>
	<h4><?php
        // translators: %s is user first name
        printf( esc_html__('Welcome %s', 'latepoint'), esc_html($customer->full_name)); ?></h4>
	<div class="latepoint-tabs-w">
		<div class="latepoint-tab-triggers customer-dashboard-tabs">
			<a href="#" data-tab-target=".tab-content-customer-bookings" class="active latepoint-tab-trigger"><?php esc_html_e('Appointments', 'latepoint'); ?></a>
			<a href="#" data-tab-target=".tab-content-customer-orders" class="latepoint-tab-trigger"><?php esc_html_e('Orders', 'latepoint'); ?></a>
			<a href="#" data-tab-target=".tab-content-customer-info-form" class="latepoint-tab-trigger"><?php esc_html_e('Profile', 'latepoint'); ?></a>
			<?php if (!$hide_new_appointment_ui) { ?>
                <a href="#" data-tab-target=".tab-content-customer-new-appointment-form" class="latepoint-tab-trigger"><?php esc_html_e('New Appointment', 'latepoint'); ?></a>
            <?php } ?>
			<?php do_action('latepoint_customer_dashboard_after_tabs', $customer); ?>
		</div>
		<div class="latepoint-tab-content tab-content-customer-bookings active">
	    <?php do_action('latepoint_customer_dashboard_before_appointments', $customer); ?>
			<?php if($future_bookings || $past_bookings || $cancelled_bookings || $not_scheduled_bundles){ ?>
				<?php if($future_bookings){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php esc_html_e('Upcoming', 'latepoint'); ?></h5>
					<div class="heading-extra">
                        <?php
                        // translators: %d is number of appointments
                        echo sprintf( esc_html__('%d Appointments', 'latepoint'), count($future_bookings)); ?>
                    </div>
				</div>
				<div class="customer-bookings-tiles">
					<?php 
					foreach($future_bookings as $booking){
						$is_upcoming_booking = true;
						include('_booking_tile.php');
					} ?>
					<?php if (!$hide_new_appointment_ui) { ?>
					    <a href="#" class="new-booking-tile os_trigger_booking" <?php echo OsSettingsHelper::get_settings_value('customer_dashboard_book_button_attributes', ''); ?>><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('New Appointment', 'latepoint'); ?></span></a>
                    <?php } ?>
				</div>
				<?php } ?>
				<?php
				if($not_scheduled_bundles){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php esc_html_e('Bundles', 'latepoint'); ?></h5>
					<div class="heading-extra">
                        <?php
                        // translators: %d is number of bundles
                        echo sprintf( esc_html__('%d Bundles', 'latepoint'), count($not_scheduled_bundles)); ?>
                    </div>
				</div>
				<div class="customer-bookings-tiles">
					<?php
						foreach($not_scheduled_bundles as $order_item_id => $bundle){
							$order_item = new OsOrderItemModel($order_item_id);
							$order = new OsOrderModel($order_item->order_id);
							$bundle_services_with_booked_count = OsOrdersHelper::bundle_services_and_booked_count_for_order_item_id($order_item_id);
							include('_bundle_tile.php');
					} ?>
				</div>
				<?php } ?>
				<?php
				if($past_bookings){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php esc_html_e('Past', 'latepoint'); ?></h5>
					<div class="heading-extra">
                        <?php
                        // translators: %d is number of appointments
                        echo sprintf( esc_html__('%d Appointments', 'latepoint'), count($past_bookings)); ?>
                    </div>
				</div>
				<div class="customer-bookings-tiles">
					<?php 
						foreach($past_bookings as $booking){
							$is_upcoming_booking = false;
							include('_booking_tile.php');
					} ?>
					<?php if (!$hide_new_appointment_ui) { ?>
					    <a href="#" class="new-booking-tile os_trigger_booking"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('New Appointment', 'latepoint'); ?></span></a>
                    <?php } ?>
				</div>
				<?php } ?>
				<?php
				if($cancelled_bookings){ ?>
				<div class="latepoint-section-heading-w">
					<h5 class="latepoint-section-heading"><?php esc_html_e('Cancelled', 'latepoint'); ?></h5>
					<div class="heading-extra">
                        <?php
                        // translators: %d is number of appointments
                        echo sprintf( esc_html__('%d Appointments', 'latepoint'), count($cancelled_bookings)); ?>
                    </div>
				</div>
				<div class="customer-bookings-tiles">
					<?php
						foreach($cancelled_bookings as $booking){
							$is_upcoming_booking = false;
							include('_booking_tile.php');
					} ?>
					<?php if (!$hide_new_appointment_ui) { ?>
					    <a href="#" class="new-booking-tile os_trigger_booking"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('New Appointment', 'latepoint'); ?></span></a>
                    <?php } ?>
				</div>
				<?php } ?>
			<?php }else{ 
				echo '<div class="latepoint-message-info latepoint-message">'.esc_html__('No appointments found', 'latepoint').'</div>';
			}
			?>
		</div>
		<div class="latepoint-tab-content tab-content-customer-orders">
			<?php
			if($orders){
				echo '<div class="customer-orders-tiles">';
				foreach($orders as $order){
					include('_order_tile.php');
				}
				echo '</div>';
			}
			?>
		</div>
		<div class="latepoint-tab-content tab-content-customer-info-form">
			<form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'update')); ?>">
			  <div class="os-row">
			    <?php echo OsFormHelper::text_field('customer[first_name]', __('Your First Name', 'latepoint'), $customer->first_name, array('class' => 'required'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::text_field('customer[last_name]', __('Your Last Name', 'latepoint'), $customer->last_name, array('class' => 'required'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::phone_number_field('customer[phone]', __('Your Phone Number', 'latepoint'), $customer->phone, [], array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::text_field('customer[email]', __('Your Email Address', 'latepoint'), $customer->email, array('class' => 'required'), array('class' => 'os-col-6')); ?>
			    <?php echo OsFormHelper::hidden_field('customer[id]', $customer->id); ?>
				</div>
				<?php do_action('latepoint_customer_dashboard_information_form_after', $customer); ?>
				<?php echo OsFormHelper::button('submit', __('Save Changes', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
			</form>
			<div class="customer-password-form-w">
				<form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'change_password')); ?>">
			    <h5><?php esc_html_e('Set New Password', 'latepoint'); ?></h5>
				  <div class="os-row">
				    <?php echo OsFormHelper::password_field('password', __('New Password', 'latepoint'), '', [], array('class' => 'os-col-6')); ?>
				    <?php echo OsFormHelper::password_field('password_confirmation', __('Confirm New Password', 'latepoint'), '', [], array('class' => 'os-col-6')); ?>
					</div>
					<?php echo OsFormHelper::button('submit', __('Set New Password', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
				</form>
			</div>
		</div>
		<div class="latepoint-tab-content tab-content-customer-new-appointment-form">
			<?php echo do_shortcode(OsSettingsHelper::get_settings_value('customer_dashboard_book_shortcode', '[latepoint_book_button]')); ?>
		</div>
		<?php do_action('latepoint_customer_dashboard_after_tab_contents', $customer); ?>
	</div>
</div>