<?php
/* @var $bookings OsBookingModel[] */
/* @var $services OsServiceModel[] */
/* @var $agents OsAgentModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="latepoint-top-search-results">

<h3><i class="latepoint-icon latepoint-icon-calendar"></i><span><?php esc_html_e('Appointments', 'latepoint'); ?></span></h3>
<?php if(!empty($bookings)){ ?>
	<div class="latepoint-search-results-tiles-w">
		<?php foreach($bookings as $booking){ ?>
		<a href="#" <?php echo OsBookingHelper::quick_booking_btn_html($booking->id); ?> class="booking-result latepoint-search-result">
			<div class="avatar" style="background-image: url(<?php echo esc_url($booking->service->selection_image_url); ?>)"></div>
			<div class="name"><?php echo preg_replace("/($query)/i", "<strong>$1</strong>", esc_html($booking->booking_code)); ?></div>
		</a>
		<?php } ?>
	</div>
<?php }else{
	echo '<div class="search-no-results">'.esc_html__('No Matched Appointments found.', 'latepoint').' <a href="#" '.OsOrdersHelper::quick_order_btn_html().'>'.esc_html__('Add Appointment', 'latepoint').'</a></div>';
} ?>
<h3><i class="latepoint-icon latepoint-icon-users"></i><span><?php esc_html_e('Customers', 'latepoint'); ?></span></h3>
<?php if(!empty($customers)){ ?>
	<div class="latepoint-search-results-tiles-w">
		<?php foreach($customers as $customer){ ?>
		<a href="#" <?php echo OsCustomerHelper::quick_customer_btn_html($customer->id); ?> class="customer-result latepoint-search-result">
			<div class="avatar" style="background-image: url(<?php echo esc_url($customer->avatar_url); ?>)"></div>
			<div class="name"><?php echo preg_replace("/($query)/i", "<strong>$1</strong>", esc_html($customer->full_name)); ?></div>
		</a>
		<?php } ?>
	</div>
<?php }else{
	echo '<div class="search-no-results">'.esc_html__('No Matched Customers found.', 'latepoint').' <a href="#" '.OsCustomerHelper::quick_customer_btn_html().'>'.esc_html__('Add Customer', 'latepoint').'</a></div>';
} ?>
<?php if(OsRolesHelper::can_user('agent__view')){
	// This results are only for admins
	?>
	<h3><i class="latepoint-icon latepoint-icon-briefcase"></i><span><?php esc_html_e('Agents', 'latepoint'); ?></span></h3>
	<?php if(!empty($agents)){ ?>
		<div class="latepoint-search-results-tiles-w">
			<?php foreach($agents as $agent){ ?>
			<a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'edit_form'), array('id' => $agent->id) )); ?>" class="agent-result latepoint-search-result">
				<div class="avatar" style="background-image: url(<?php echo esc_url($agent->avatar_url); ?>)"></div>
				<div class="name"><?php echo preg_replace("/($query)/i", "<strong>$1</strong>", esc_html($agent->full_name)); ?></div>
			</a>
			<?php } ?>
		</div>
	<?php }else{
		echo '<div class="search-no-results">'.esc_html__('No Matched Agents found.', 'latepoint').' <a href="'.esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('agents', 'create'))).'">'.esc_html__('Add Agent', 'latepoint').'</a></div>';
	} ?>
<?php } ?>
<?php if(OsRolesHelper::can_user('service__view')){ ?>
	<h3><i class="latepoint-icon latepoint-icon-shopping-bag"></i><span><?php esc_html_e('Services', 'latepoint'); ?></span></h3>
	<?php if(!empty($services)){ ?>
		<div class="latepoint-search-results-tiles-w">
			<?php foreach($services as $service){ ?>
			<a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'edit_form'), array('id' => $service->id) )); ?>" class="service-result latepoint-search-result">
				<div class="avatar" style="background-image: url(<?php echo esc_url($service->selection_image_url); ?>)"></div>
				<div class="name"><?php echo preg_replace("/($query)/i", "<strong>$1</strong>", esc_html($service->name)); ?></div>
			</a>
			<?php } ?>
		</div>
	<?php }else{
		echo '<div class="search-no-results">'.esc_html__('No Matched Services found.', 'latepoint').' <a href="'.esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'create'))).'">'.esc_html__('Add Service', 'latepoint').'</a></div>';
	} ?>
<?php } ?>
</div>