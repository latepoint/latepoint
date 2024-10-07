<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $cart OsCartModel */
/* @var $customer OsCustomerModel */
/* @var $booking OsBookingModel */
/* @var $active_cart_item OsCartItemModel */
/* @var $output_target string [summary_panel, step_verify] */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

    <div class="cart-summary-main-section">
		<?php
		if ( ! $cart->is_empty() ) {
			$cart_bookings = $cart->get_bookings_from_cart_items();
			$cart_bundles  = $cart->get_bundles_from_cart_items();


			$count_class = ( ( count( $cart_bookings ) + count( $cart_bundles ) ) == 1 ) ? 'single-item' : 'multi-item';

			if ( $cart_bookings && $cart_bundles && $output_target != 'summary_panel' ) {
				?>
                <div class="summary-heading summary-variant-heading">
                    <div class="pb-heading">
                        <div class="pbh-label"><?php esc_html_e( 'Service Bundles', 'latepoint' ); ?></div>
                        <div class="pbh-line"></div>
                    </div>
                </div>
				<?php
			}

			if ( $cart_bundles ) {
				foreach ( $cart_bundles as $cart_item_id => $bundle ) { ?>
                    <div class="cart-item-wrapper <?php echo esc_attr($count_class); ?>">
                        <div class="summary-box main-box" data-cart-item-id="<?php echo esc_attr($cart_item_id); ?>">
							<?php
							$bundle_headings = [];
							$bundle_headings = apply_filters( 'latepoint_cart_summary_bundle_headings', $bundle_headings, $bundle );
							if ( $bundle_headings ) {
								echo '<div class="summary-box-heading">';
								foreach ( $bundle_headings as $heading ) {
									echo '<div class="sbh-item">' . esc_html($heading) . '</div>';
								}
								echo '<div class="sbh-line"></div>';
								echo '</div>';
							}
							$bundle_services              = $bundle->get_services();
							$bundle_services_descriptions = [];
							foreach ( $bundle_services as $service ) {
								$qty                            = $bundle->quantity_for_service( $service->id );
								$qty_html                       = $qty > 1 ? ' [' . $qty . ']' : '';
								$bundle_services_descriptions[] = $service->name . $qty_html;
							}
							?>
                            <div class="summary-box-content os-cart-item is-removable">
								<?php if ( OsCartsHelper::can_checkout_multiple_items() ) { ?>
                                    <div class="os-remove-item-from-cart"
                                         role="button"
                                         tabindex="0"
                                         data-confirm-text="<?php esc_attr_e( 'Are you sure you want to remove this item from your cart?', 'latepoint' ); ?>"
                                         data-cart-item-id="<?php echo esc_attr($cart_item_id); ?>"
                                         data-route="<?php echo esc_attr(OsRouterHelper::build_route_name( 'carts', 'remove_item_from_cart' )); ?>">
                                        <i class="latepoint-icon latepoint-icon-minus"></i></div>
								<?php } ?>
                                <div class="sbc-big-item"><?php echo esc_html($bundle->name); ?></div>
                                <div class="sbc-highlighted-item">
									<?php echo esc_html(implode( ', ', $bundle_services_descriptions )); ?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}

			if ( $cart_bookings && $cart_bundles && $output_target != 'summary_panel' ) {
				?>
                <div class="summary-heading summary-variant-heading">
                    <div class="pb-heading">
                        <div class="pbh-label"><?php esc_html_e( 'Individual Services', 'latepoint' ); ?></div>
                        <div class="pbh-line"></div>
                    </div>
                </div>
				<?php
			}
			if ( $cart_bookings ) {

				$same_location = OsBookingHelper::bookings_have_same_location( $cart_bookings );
				$same_agent    = OsBookingHelper::bookings_have_same_agent( $cart_bookings );

				foreach ( $cart_bookings as $cart_item_id => $cart_booking ) {
					echo '<div class="cart-item-wrapper ' . esc_attr($count_class) . '">';
					echo OsBookingHelper::generate_summary_for_booking( $cart_booking, $cart_item_id );
					if ( ! $same_agent || ! $same_location ) {
						echo '<div class="booking-summary-info-w">';
						echo '<div class="summary-boxes-columns">';
						if ( ! $same_agent && ( OsAgentHelper::count_agents() > 1 ) ) {
							OsAgentHelper::generate_summary_for_agent( $cart_booking );
						}
						if ( ! $same_location ) {
							OsLocationHelper::generate_summary_for_location( $cart_booking );
						}
						echo '</div>';
						echo '</div>';
					}
					echo '</div>';
				}
			}


			if ( $cart_bookings ) {
				echo '<div class="booking-summary-info-w">';
				echo '<div class="summary-boxes-columns">';
				if ( $same_agent && ( OsAgentHelper::count_agents() > 1 ) ) {
					OsAgentHelper::generate_summary_for_agent( reset( $cart_bookings ) );
				}
				if ( $same_location ) {
					OsLocationHelper::generate_summary_for_location( reset( $cart_bookings ) );
				}
				if ( $customer ) {
					OsCustomerHelper::generate_summary_for_customer( $customer );
				}
				echo '</div>';
				echo '</div>';
			} else {
				if ( $customer ) {
					echo '<div class="booking-summary-info-w">';
					echo '<div class="summary-boxes-columns">';
					OsCustomerHelper::generate_summary_for_customer( $customer );
					echo '</div>';
					echo '</div>';
				}
			}
		} else {
			// no cart items, check if building a booking
			if ( $booking ) {
				if ( $customer ) {
					echo '<div class="booking-summary-info-w">';
					echo '<div class="summary-boxes-columns">';
					OsCustomerHelper::generate_summary_for_customer( $customer );
					echo '</div>';
					echo '</div>';
				}
			}
		}
		if ( OsCartsHelper::can_checkout_multiple_items() ) {
			echo '<div class="latepoint-add-another-item-trigger-wrapper on-summary">
					<div class="latepoint-add-another-item-trigger" tabindex="0" data-step="' . esc_attr(OsStepsHelper::get_first_step_code( 'booking' )) . '">
						<i class="latepoint-icon latepoint-icon-plus"></i>
						<span>' . esc_html__( 'Add More', 'latepoint' ) . '</span>
					</div>
				</div>';
		}

		do_action( 'latepoint_cart_summary_before_price_breakdown', $cart );
		?>
    </div>
<?php if ( ! $cart->is_empty() || ( $booking && ! empty( $active_cart_item ) ) ) {

	if ( $cart->is_empty() ) {
		// cart is empty - temporary add active cart item to cart to get a breakdown
		$cart->add_item( $active_cart_item, false );
        $just_added = true;
	}else{
        $just_added = false;
    }
	$price_breakdown_rows = $cart->generate_price_breakdown_rows( [ 'payments', 'balance' ] );
    $extra_css_classes = [];
    $extra_css_classes[] = ( count( $cart->get_items() ) > 1 || ( !$just_added && $booking->is_ready_for_summary() && !$cart->is_empty() && isset( $active_cart_item ) && $active_cart_item->is_new_record() ) ) ? 'compact-summary' : 'full-summary';
    $extra_css_classes[] = ( count( $cart->get_items() ) > 1 ) ? 'multi-item' : 'single-item';
	?>
    <div class="summary-price-breakdown-wrapper <?php echo esc_attr(implode(' ', $extra_css_classes)); ?>">
        <?php if( $cart->get_subtotal() > 0 || OsSettingsHelper::is_off('hide_breakdown_if_subtotal_zero')){ ?>
        <div class="summary-price-breakdown-inner">
            <div class="price-breakdown-unfold">
                <i class="latepoint-icon latepoint-icon-chevron-right"></i>
                <span><?php esc_html_e( 'Breakdown', 'latepoint' ); ?></span>
            </div>
            <div class="pb-heading">
                <div class="pbh-label"><?php esc_html_e( 'Cost Breakdown', 'latepoint' ); ?></div>
                <div class="pbh-line"></div>
            </div>
			<?php
			OsPriceBreakdownHelper::output_price_breakdown( $price_breakdown_rows ); ?>
        </div>
        <?php } ?>
		<?php
		if ( OsCartsHelper::can_checkout_multiple_items() ) {
			echo '<div class="latepoint-add-another-item-trigger-wrapper on-verify">
					<div class="latepoint-add-another-item-trigger" tabindex="0" data-step="' . esc_attr(OsStepsHelper::get_first_step_code( 'booking' )) . '">
						<i class="latepoint-icon latepoint-icon-plus"></i>
						<span>' . esc_html__( 'Add more items to this order', 'latepoint' ) . '</span>
					</div>
				</div>';
		}
		?>
    </div>
<?php } ?>