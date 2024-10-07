<?php
/* @var $booking OsBookingModel */
/* @var $cart OsCartModel */
/* @var $logged_in_customer OsCustomerModel */
/* @var $active_cart_item OsCartItemModel */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="summary-header <?php echo OsCartsHelper::can_checkout() ? 'can-checkout' :''; ?>">
	<div class="summary-header-inner">
	  <span class="summary-header-label"><?php esc_html_e('Summary', 'latepoint'); ?></span>
		<?php
		if(OsCartsHelper::can_checkout() && OsCartsHelper::can_checkout_multiple_items()){
			echo '<div class="checkout-from-summary-panel-btn-wrapper">';
				echo '<div class="checkout-from-summary-panel-btn" role="button" data-step="verify" tabindex="0"><span>'.esc_html__('Checkout', 'latepoint').'</span><i class="latepoint-icon latepoint-icon-arrow-2-right"></i></div>';
			echo '</div>';
		}
		?>
	  <a href="#" class="latepoint-lightbox-summary-trigger"><i class="latepoint-icon-common-01"></i></a>
	</div>
</div>
<?php
	if(OsCartsHelper::can_checkout() && OsCartsHelper::can_checkout_multiple_items()){
		echo '<div class="checkout-from-summary-panel-btn-wrapper os-mobile-only">';
			echo '<div class="checkout-from-summary-panel-btn" data-step="verify"><span>'.esc_html__('Checkout', 'latepoint').'</span><i class="latepoint-icon latepoint-icon-arrow-2-right"></i></div>';
		echo '</div>';
	}
?>
<div class="os-summary-contents-inner <?php echo OsCartsHelper::can_checkout() ? 'can-checkout' :''; ?> <?php echo (count($cart->get_items()) > 1) ? 'has-multiple-cart-items' : '' ?>">
	<div class="os-summary-contents-inner-top">
		<?php
		$customer = $logged_in_customer;
			if($booking->is_ready_for_summary() && $active_cart_item->is_new_record()){
//				if(!$cart->is_empty()){
//					echo '<div class="pb-heading">
//									<div class="pbh-label">' . __('To be added', 'latepoint') . '</div>
//									<div class="pbh-line"></div>
//								</div>';
//				}
				echo '<div class="summary-panel-items-wrapper">';
					$cart_item_id = $active_cart_item->id;
					echo '<div class="active-cart-item-wrapper '.($cart->is_empty() ? '' : 'is-separated') .'">';
						echo OsBookingHelper::generate_summary_for_booking($booking, $cart_item_id);
						if (OsAgentHelper::count_agents() > 1) OsAgentHelper::generate_summary_for_agent($booking);
					echo '</div>';
				echo '</div>';
			}
			if($booking->is_ready_for_summary() && !$cart->is_empty() && $active_cart_item->is_new_record()){
				echo '<div class="pb-heading">
								<div class="pbh-label">' . esc_html__('In cart', 'latepoint') . '</div>
								<div class="pbh-line"></div>
							</div>';

		}?>
	</div>
	<?php
	$customer = $logged_in_customer;
	$output_target = 'summary_panel';
	include '_cart_summary.php';
	?>
</div>