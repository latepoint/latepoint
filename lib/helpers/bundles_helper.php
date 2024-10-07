<?php

class OsBundlesHelper {

	public static function generate_order_summary_for_bundle(OsBundleModel $bundle, string $order_item_id, $preselected_booking_id = false): string{
		$html = '<div class="summary-box main-box">';

		$bundle_services = $bundle->get_services();
		$bundle_services_descriptions = [];
		$total_bookable_quantity = 0;
		foreach ($bundle_services as $service) {
			$qty = $service->join_attributes['quantity'];
			$qty_html = $qty > 1 ? ' [' . $qty . ']' : '';
			$bundle_services_descriptions[] = $service->name . $qty_html;
			$total_bookable_quantity+= $qty;
		}
		$html.= '<div class="summary-box-content is-removable">
			<div class="sbc-big-item">'.$bundle->name.'</div>
			<div class="sbc-subtle-item">
				'.implode(', ', $bundle_services_descriptions).'
			</div>
		</div>';


		$past_count = 0;
		$booked_count = 0;

        // translators: %s is the name of the bundle
		$html.= '<div class="hidden-bundle-items-notice"><div class="hidden-bundle-items-notice-message">'.sprintf(__('Part of a %s bundle.', 'latepoint'), '<strong>"'.$bundle->name.'"</strong>').'</div><div class="hidden-bundle-items-notice-link">'.__('Show Full Bundle', 'latepoint').'</div></div>';
		$html.= '<div class="bookable-items-breakdown">';
			foreach ($bundle_services as $service) {
				$bookings = (strpos($order_item_id, 'new_') === false) ? OsOrdersHelper::get_bookings_for_order_item($order_item_id, $service->id, OsBookingHelper::get_non_cancelled_booking_statuses()) : [];
				foreach($bookings as $booking){
					$booked_count++;
					if(!$booking->is_upcoming()) $past_count++;
				}
                // translators: %d is the number of sessions
				if(count($bundle_services) > 1) $html.= '<div class="bundle-service-info">'.$service->name.' ['.sprintf(__('%d sessions', 'latepoint'), $service->join_attributes['quantity']).']</div>';
				for($i = 0; $i < $service->join_attributes['quantity']; $i++){
					$html.= '<div class="order-item-variant-bundle-booking-wrapper">';
						$booking = isset($bookings[$i]) ? new OsBookingModel($bookings[$i]->id) : OsBookingHelper::prepare_new_from_params(['service_id' => $service->id]);
						$booking->service_id = $service->id;
						$is_preselected = (!$booking->is_new_record() && $preselected_booking_id == $booking->id);
						$html.= OsOrdersHelper::generate_booking_block_for_bundle_order_item($booking, $order_item_id, isset($bookings[$i]), $is_preselected);
					$html.= '</div>';
				}
                if(strpos($order_item_id, 'new_') === false){
                    // existing order, find cancelled bookings
                    $cancelled_bookings = OsOrdersHelper::get_bookings_for_order_item($order_item_id, $service->id, [LATEPOINT_BOOKING_STATUS_CANCELLED]);
                    if(!empty($cancelled_bookings)){
                        $html.= '<div class="order-item-cancelled-bookings-wrapper">';
                            // translators: %d is the number of cancelled appointments
                            $html.= '<div class="order-item-cancelled-bookings-heading">'.sprintf(_n('%d Cancelled Appointment', '%d Cancelled Appointments', count($cancelled_bookings), 'latepoint'), count($cancelled_bookings)).'</div>';
                            $html.= '<div class="order-item-cancelled-bookings-list">';
                            foreach($cancelled_bookings as $booking){
                                $html.= '<div class="order-item-variant-bundle-booking-wrapper">';
                                    $html.= OsOrdersHelper::generate_booking_block_for_bundle_order_item($booking, $order_item_id, true, ($preselected_booking_id == $booking->id));
                                $html.= '</div>';
                            }
                            $html.= '</div>';
                        $html.= '</div>';
                    }
                }
			}
		$html.= '</div>';
		$html.= '<div class="bookable-items">';
		for($i = 1; $i <= $total_bookable_quantity; $i++){
			$is_past = ($i <= $past_count) ? 'is-past' : '';
			$is_booked = ($i <= $booked_count) ? 'is-booked' : '';
			$html.= '<div class="bookable-item '.$is_past.' '.$is_booked.'"></div>';
		}
		$html.= '</div>';

		$html.= '</div>';

		return $html;
	}

	public static function generate_summary_for_bundle(OsBundleModel $bundle, $cart_item_id = false, $order_item_id = false, $user_type = LATEPOINT_USER_TYPE_ADMIN){
    ob_start();
		?>
		<div class="summary-box main-box" <?php if($cart_item_id) echo 'data-cart-item-id="'.esc_attr($cart_item_id).'"'; ?>>
			<?php
			$bundle_headings = [];
			$bundle_headings = apply_filters('latepoint_order_summary_bundle_headings', $bundle_headings, $bundle);
			if ($bundle_headings) {
				echo '<div class="summary-box-heading">';
				foreach ($bundle_headings as $heading) {
					echo '<div class="sbh-item">' . esc_html($heading) . '</div>';
				}
				echo '<div class="sbh-line"></div>';
				echo '</div>';
			}
			$bundle_services = $bundle->get_services();
			$bundle_services_descriptions = [];
			$total_bookable_quantity = 0;
			foreach ($bundle_services as $service) {
				$qty = $service->join_attributes['quantity'];
				$qty_html = $qty > 1 ? ' [' . $qty . ']' : '';
				$bundle_services_descriptions[] = $service->name . $qty_html;
				$total_bookable_quantity+= $qty;
			}
			?>
			<div class="summary-box-content <?php if($cart_item_id) echo 'os-cart-item'; ?> is-removable">
				<?php if($cart_item_id && OsCartsHelper::can_checkout_multiple_items()){ ?>
				<div class="os-remove-item-from-cart" role="button"
                     tabindex="0"
				     data-confirm-text="<?php esc_attr_e('Are you sure you want to remove this item from your cart?', 'latepoint'); ?>"
				     data-cart-item-id="<?php echo esc_attr($cart_item_id); ?>"
				     data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('carts', 'remove_item_from_cart')); ?>"><i
						class="latepoint-icon latepoint-icon-minus"></i></div>
				<?php } ?>
				<div class="sbc-big-item"><?php echo esc_html($bundle->name); ?></div>
				<div class="sbc-subtle-item">
					<?php echo esc_html(implode(', ', $bundle_services_descriptions)); ?>
				</div>
			</div>
			<?php if($order_item_id){
				$past_count = 0;
				$booked_count = 0;
				if($user_type == LATEPOINT_USER_TYPE_CUSTOMER){
					$order_item = new OsOrderItemModel($order_item_id);
					$order = new OsOrderModel($order_item->order_id);
                    // translators: %s is the order confirmation code
					echo '<div class="bundle-order-small-info">'.sprintf(esc_html__('Order %s', 'latepoint'), '<a href="#" '.OsCustomerHelper::generate_order_summary_btn($order->id).'>#'.esc_html($order->confirmation_code).'</a>').'</div>';
				}
				echo '<div class="bookable-items-breakdown">';
					foreach ($bundle_services as $service) {
						$bookings = OsOrdersHelper::get_bookings_for_order_item($order_item_id, $service->id, OsBookingHelper::get_non_cancelled_booking_statuses());
						foreach($bookings as $booking){
							$booked_count++;
							if(!$booking->is_upcoming()) $past_count++;
						}
                        // translators: %d is the number of sessions
						if(count($bundle_services) > 1) echo '<div class="bundle-service-info">'.esc_html($service->name.' ['.sprintf(__('%d sessions', 'latepoint'), $service->join_attributes['quantity']).']').'</div>';
						for($i = 0; $i < $service->join_attributes['quantity']; $i++){
							if(isset($bookings[$i])){
								$is_past = (!$bookings[$i]->is_upcoming()) ? 'is-past' : '';
								$trigger_html = ($user_type == LATEPOINT_USER_TYPE_CUSTOMER) ? OsCustomerHelper::generate_booking_summary_preview_btn($bookings[$i]->id) : OsBookingHelper::quick_booking_btn_html($bookings[$i]->id);
								echo '<div class="order-item-variant-bundle-booking is-booked bundle-booking-status-'.esc_attr($bookings[$i]->status).' '.$is_past.'" '.$trigger_html.'>
												<div class="booking-item-status-pill"></div>
												<div class="bib-datetime">'.esc_html($bookings[$i]->get_nice_start_datetime()).'</div>
												<div class="bib-icon"><i class="latepoint-icon latepoint-icon-arrow-right"></i></div>
											</div>';
							}else{
								if($user_type == LATEPOINT_USER_TYPE_CUSTOMER){
									echo '<div class="order-item-variant-bundle-booking os_trigger_booking" data-hide-side-panel="yes" data-hide-summary="yes" data-order-item-id="'.esc_attr($order_item_id).'" data-selected-service="'.esc_attr($service->id).'"><div class="booking-item-status-pill"></div><div class="bib-label">'.esc_html__('Schedule now', 'latepoint').'</div></div>';
								}else{
									echo '<div class="order-item-variant-bundle-booking" '.OsOrdersHelper::quick_order_btn_html(false, ['order_item_id' => $order_item_id, 'service_id' => $service->id]).'><div class="booking-item-status-pill"></div><div class="bib-label">'.esc_html__('Schedule now', 'latepoint').'</div></div>';
								}
							}
						}
                        $cancelled_bookings = OsOrdersHelper::get_bookings_for_order_item($order_item_id, $service->id, [LATEPOINT_BOOKING_STATUS_CANCELLED]);
                        if(!empty($cancelled_bookings)){
                            echo '<div class="order-item-cancelled-bookings-wrapper">';
                                // translators: %d is the number of cancelled appointments
                                echo '<div class="order-item-cancelled-bookings-heading">'.esc_html(sprintf(_n('%d Cancelled Appointment', '%d Cancelled Appointments', count($cancelled_bookings), 'latepoint'), count($cancelled_bookings))).'</div>';
                                echo '<div class="order-item-cancelled-bookings-list">';
                                foreach($cancelled_bookings as $cancelled_booking){
                                    echo '<div class="order-item-variant-bundle-booking-wrapper">';
                                        $is_past = (!$cancelled_booking->is_upcoming()) ? 'is-past' : '';
                                        $trigger_html = ($user_type == LATEPOINT_USER_TYPE_CUSTOMER) ? OsCustomerHelper::generate_booking_summary_preview_btn($cancelled_booking->id) : OsBookingHelper::quick_booking_btn_html($cancelled_booking->id);
                                        echo '<div class="order-item-variant-bundle-booking is-booked bundle-booking-status-'.esc_attr($cancelled_booking->status).' '.$is_past.'" '.$trigger_html.'>
                                                        <div class="booking-item-status-pill"></div>
                                                        <div class="bib-datetime">'.esc_html($cancelled_booking->get_nice_start_datetime()).'</div>
                                                        <div class="bib-icon"><i class="latepoint-icon latepoint-icon-arrow-right"></i></div>
                                                    </div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            echo '</div>';
                        }
					}
				echo '</div>';
				echo '<div class="bookable-items">';
				for($i = 1; $i <= $total_bookable_quantity; $i++){
					$is_past = ($i <= $past_count) ? 'is-past' : '';
					$is_booked = ($i <= $booked_count) ? 'is-booked' : '';
					echo '<div class="bookable-item '.esc_attr($is_past).' '.esc_attr($is_booked).'"></div>';
				}
				echo '</div>';

			}
			?>
		</div>
		<?php
    $response_html = ob_get_clean();
		return $response_html;
	}

	/**
	 * @param array $item_data
	 * @return OsBundleModel
	 */
	public static function build_bundle_model_from_item_data(array $item_data): OsBundleModel{
    $bundle = new OsBundleModel();
		if(!empty($item_data['bundle_id'])) $bundle = $bundle->load_by_id($item_data['bundle_id']);
		return $bundle;
	}


	/**
	 * @param OsBundleModel $bundle
	 * @return mixed|void
	 *
	 * Returns full amount to charge in database format 1999.0000
	 *
	 */
	public static function calculate_full_amount_for_bundle(OsBundleModel $bundle) {
		$amount = $bundle->charge_amount;
		$amount = apply_filters('latepoint_full_amount_for_bundle', $amount, $bundle);
		$amount = OsMoneyHelper::pad_to_db_format($amount);
		return $amount;
	}


	/**
	 * @param OsBundleModel $bundle
	 * @param array $options
	 * @return mixed|void
	 *
	 * Returns deposit amount to charge in database format 1999.0000
	 *
	 */
	public static function calculate_deposit_amount_for_bundle(OsBundleModel $bundle) {
		$amount = $bundle->deposit_amount;
		$amount = apply_filters('latepoint_deposit_amount_for_bundle', $amount, $bundle);
		$amount = OsMoneyHelper::pad_to_db_format($amount);
		return $amount;
	}
}