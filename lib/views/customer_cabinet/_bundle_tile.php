<?php
/** @var $bundle OsBundleModel */
/** @var $order_item OsOrderItemModel */
/** @var $bundle_services_with_booked_count OsServiceModel[] */
/** @var $order OsOrderModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="customer-bundle-tile">
	<div class="customer-bundle-tile-inner">
		<div class="bundle-main-info-wrapper">
			<div class="bundle-main-info">
				<h6 class="bundle-name"><?php echo esc_html($bundle->name); ?></h6>
				<div class="bundle-order-info"><?php echo esc_html__('Order', 'latepoint').' <a href="#" '.OsCustomerHelper::generate_order_summary_btn($order->id).'>#'.esc_html($order->confirmation_code).'</a>'; ?></div>
			</div>
			<div class="bundle-icon">
				<i class="latepoint-icon latepoint-icon-layers"></i>
			</div>
		</div>
		<div class="bundle-services">
			<?php foreach($bundle_services_with_booked_count as $service){ ?>
				<div class="bundle-included-service-wrapper">
					<div class="bundle-included-service-name"><?php echo esc_html($service->name); ?></div>
					<?php
                        // translators: %1$d number of scheduled appointments, %2$d is total available
						$scheduled_text = $service->join_attributes['total_scheduled_bookings'] ? sprintf(__('%1$d of %2$d Scheduled', 'latepoint'), $service->join_attributes['total_scheduled_bookings'],  $service->join_attributes['quantity']) : __('Not Scheduled', 'latepoint');
						echo '<div class="bundle-included-service-quantity">'.esc_html($scheduled_text).'</div>';
						?>
				</div>
			<?php } ?>
		</div>
		<div class="customer-bundle-bottom-actions">
			<a href="#" class="latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block" <?php echo OsCustomerHelper::generate_bundle_scheduling_btn($order_item->id); ?>>
				<span><?php esc_html_e('Start Scheduling', 'latepoint'); ?></span>
				<i class="latepoint-icon latepoint-icon-arrow-right1"></i>
			</a>
		</div>
	</div>
	<div class="customer-bundle-tile-shadow"></div>
	<div class="customer-bundle-tile-shadow"></div>
</div>