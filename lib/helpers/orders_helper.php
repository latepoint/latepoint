<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsOrdersHelper {

	/**
	 * @param $order_item_id
	 * @param $service_id
	 * @return OsBookingModel[]
	 */
	public static function get_bookings_for_order_item($order_item_id, $service_id = false, array $only_statuses = []): array {
		$bookings = new OsBookingModel();
		if($service_id) $bookings = $bookings->where(['service_id' => $service_id]);
		if(!empty($only_statuses)) $bookings = $bookings->where(['status' => $only_statuses]);
		$bookings = $bookings->where(['order_item_id' => $order_item_id])->order_by('start_datetime_utc asc')->get_results_as_models();
		return $bookings;
	}
	public static function quick_order_btn_html($order_id = false, $params = array()) {
		$html = '';
		if ($order_id) $params['id'] = $order_id;
		$route = OsRouterHelper::build_route_name('orders', 'quick_edit');

		$params_str = http_build_query($params);
		$html = 'data-os-params="' . esc_attr($params_str) . '" 
    data-os-action="' . esc_attr($route) . '" 
    data-os-output-target="side-panel"
    data-os-after-call="latepoint_init_quick_order_form"';
		return $html;
	}

	public static function get_order_statuses_list() {
		$statuses = [LATEPOINT_ORDER_STATUS_OPEN => __('Open', 'latepoint'),
			LATEPOINT_ORDER_STATUS_CANCELLED => __('Cancelled', 'latepoint'),
			LATEPOINT_ORDER_STATUS_COMPLETED => __('Completed', 'latepoint')
			];
		/**
		 * Get list of statuses for orders
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_statuses
		 *
		 * @param {array} $statuses array of order status codes
		 * @returns {array} The filtered array of status codes
		 */
		return apply_filters('latepoint_order_statuses', $statuses);
	}

	public static function bundle_services_and_booked_count_for_order_item_id(int $order_item_id): array {
		$order_item = new OsOrderItemModel($order_item_id);
		$bundle_services = [];
		if($order_item->is_bundle()){
			$bundle = $order_item->build_original_object_from_item_data();
			$bundle_services = $bundle->get_services($order_item_id);
		}
		return $bundle_services;
	}


	public static function get_items_for_order_id($order_id) {
		$order_items = new OsOrderItemModel();
		return $order_items->where(['order_id' => $order_id])->get_results_as_models();
	}

	public static function create_order_item_from_cart_item(OsCartItemModel $cart_item): OsOrderItemModel{
		$order_item = new OsOrderItemModel();
		$order_item->variant = $cart_item->variant;
		$order_item->item_data = $cart_item->item_data;
		$order_item->total = $cart_item->get_total();
		$order_item->subtotal = $cart_item->get_subtotal();
		$order_item->coupon_code = $cart_item->get_coupon_code();
		$order_item->coupon_discount = $cart_item->get_coupon_discount();
		$order_item->tax_total = $cart_item->get_tax_total();
		return $order_item;
	}


	public static function get_fulfillment_statuses_list(): array {
		$statuses = [LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED => __('Not Fulfilled', 'latepoint'),
			LATEPOINT_ORDER_FULFILLMENT_STATUS_FULFILLED => __('Fulfilled', 'latepoint'),
			LATEPOINT_ORDER_FULFILLMENT_STATUS_PARTIALLY_FULFILLED => __('Partially Fulfilled', 'latepoint')];
		/**
		 * Get list of fulfillment statuses for orders
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_fulfillment_statuses
		 *
		 * @param {array} $statuses array of order fulfillment status codes
		 * @returns {array} The filtered array of fulfillment status codes
		 */
		return apply_filters('latepoint_order_fulfillment_statuses', $statuses);
	}

	public static function get_order_payment_statuses_list(): array {
		$statuses = [LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID => __('Not Paid', 'latepoint'),
			LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_PAID => __('Partially Paid', 'latepoint'),
			LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID => __('Fully Paid', 'latepoint'),
			LATEPOINT_ORDER_PAYMENT_STATUS_PROCESSING => __('Processing', 'latepoint'),
			];
		/**
		 * Get list of fulfillment statuses for orders
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_payment_statuses
		 *
		 * @param {array} $statuses array of order payment status codes
		 * @returns {array} The filtered array of payment status codes
		 */
		return apply_filters('latepoint_order_payment_statuses', $statuses);
	}

	public static function get_default_order_status() : string{
		$status = LATEPOINT_ORDER_STATUS_OPEN;
		/**
		 * Get default order status
		 *
		 * @since 5.0.0
		 * @hook latepoint_get_default_order_status
		 *
		 * @param {string} $status a default order status
		 * @returns {string} filtered status
		 */
		return apply_filters('latepoint_get_default_order_status', $status);
	}


	public static function get_nice_order_payment_status_name($status) {
		$statuses_list = OsOrdersHelper::get_order_payment_statuses_list();
		if ($status && isset($statuses_list[$status])) {
			return $statuses_list[$status];
		} else {
			return __('Undefined Status', 'latepoint');
		}
	}

	public static function get_nice_order_status_name($status) {
		$statuses_list = OsOrdersHelper::get_order_statuses_list();
		if ($status && isset($statuses_list[$status])) {
			return $statuses_list[$status];
		} else {
			return __('Undefined Status', 'latepoint');
		}
	}

	public static function get_nice_order_fulfillment_status_name($status) {
		$statuses_list = OsOrdersHelper::get_fulfillment_statuses_list();
		if ($status && isset($statuses_list[$status])) {
			return $statuses_list[$status];
		} else {
			return __('Undefined Status', 'latepoint');
		}
	}


	public static function unfold_price_breakdown_row($rows){
		$readable_price_breakdown = [];
		foreach($rows as $row){
			if(empty($row)) continue;
			if(!empty($row['heading']) && !empty($row['items'])){
				$readable_price_breakdown = array_merge($readable_price_breakdown, self::unfold_price_breakdown_row($row['items']));
			}elseif(!empty($row['label'])){
				$label = $row['label'];
				if(!empty($row['note'])) $label.= ' '.$row['note'];
				if(!empty($row['badge'])) $label.= ' ('.$row['badge'].')';
				$readable_price_breakdown[$label] = $row['raw_value']*1;
			}else{
				$readable_price_breakdown = array_merge($readable_price_breakdown, self::unfold_price_breakdown_row($row));
			}
		}
		return $readable_price_breakdown;
	}


	public static function generate_direct_manage_order_url( OsOrderModel $order, string $for ): string {
		if ( ! in_array( $for, [ 'agent', 'customer' ] ) ) {
			return '';
		}
		$key = OsMetaHelper::get_order_meta_by_key( 'key_to_manage_for_' . $for, $order->id );
		if ( empty( $key ) ) {
			$key = bin2hex( random_bytes( 18 ) );
			OsMetaHelper::save_order_meta_by_key( 'key_to_manage_for_' . $for, $key, $order->id );
		}
		$url = OsRouterHelper::build_admin_post_link( [ 'manage_order_by_key', 'show' ], [ 'key' => $key ] );

		return $url;
	}

	public static function get_order_id_and_manage_ability_by_key( string $key ) {
		$order_id = OsMetaHelper::get_order_id_by_meta_value( "key_to_manage_for_agent", $key );
		if ( $order_id ) {
			return [ 'order_id' => $order_id, 'for' => 'agent' ];
		}

		$order_id = OsMetaHelper::get_order_id_by_meta_value( "key_to_manage_for_customer", $key );
		if ( $order_id ) {
			return [ 'order_id' => $order_id, 'for' => 'customer' ];
		}

		return false;
	}

	public static function create_booking_object_from_booking_data_form(array $params): OsBookingModel{
		$booking = new OsBookingModel();

		if(!empty($params['item_data'])){
			$item_data = json_decode(base64_decode($params['item_data']), true);
			$booking->set_data($item_data);
		}else{
			$filtered_params = $params;
			// input fields are formatted in customer preferred format, we need to convert that to database format Y-m-d
			$filtered_params['start_date'] = OsTimeHelper::reformat_date_string($params['start_date_formatted'], OsSettingsHelper::get_date_format(), 'Y-m-d');


			if(isset($params['start_time']['formatted_value'])){
	      $start_ampm = isset($params['start_time']['ampm']) ? $params['start_time']['ampm'] : false;
	      $filtered_params['start_time'] = OsTimeHelper::convert_time_to_minutes($params['start_time']['formatted_value'], $start_ampm);
	    }

			// set custom end time/date if it was passed in params
			if(isset($params['end_time']['formatted_value'])){
				$end_ampm = isset($params['end_time']['ampm']) ? $params['end_time']['ampm'] : false;
				$filtered_params['end_time'] = OsTimeHelper::convert_time_to_minutes($params['end_time']['formatted_value'], $end_ampm);
				if($filtered_params['end_time'] <= $filtered_params['start_time']){
					// it's next day
		      $date_obj = new OsWpDateTime($filtered_params['start_date']);
		      $filtered_params['end_date'] = $date_obj->modify('+1 day')->format('Y-m-d');
				}else{
					$filtered_params['end_date'] = $filtered_params['start_date'];
				}
			}
			$booking->set_data($filtered_params);
			$booking->set_utc_datetimes();
		}
		return $booking;
	}

	public static function loading_tile_for_order_item(string $order_item_id){
		return '<div class="order-item-temp-holder" data-order-item-id="'.$order_item_id.'"><div class="oit-avatar"></div><div class="oit-main-info"><div class="oit-title"></div><div class="oit-sub-title"></div></div></div>';
	}

	public static function booking_data_form_for_order_item_id(string $order_item_id, OsBookingModel $booking, string $order_item_variant = LATEPOINT_ITEM_VARIANT_BOOKING, bool $is_folded = true): string{
		$services = OsServiceHelper::get_allowed_active_services();
    $agents = OsAgentHelper::get_allowed_active_agents();

		$order_item_id = empty($order_item_id) ? OsUtilHelper::generate_form_id() : $order_item_id;

		$extra_css_class = $is_folded ? 'is-folded' : 'is-unfolded';
		$html = '';
		$html = '<div class="order-item-booking-data-form-wrapper order-item-booking-data-variant-'.$order_item_variant.' '.$extra_css_class.'" 
		data-order-item-variant="'.$order_item_variant.'" 
		data-order-item-id="'.$order_item_id.'" 
		data-booking-id="'.$booking->get_form_id().'">';
				$html.= self::loading_tile_for_order_item($order_item_id);
				switch($order_item_variant){
					case LATEPOINT_ITEM_VARIANT_BOOKING:
						$html.= OsOrdersHelper::generate_order_item_pill_for_booking($booking, $order_item_id);
						break;
					case LATEPOINT_ITEM_VARIANT_BUNDLE:
						$html.= OsOrdersHelper::generate_order_item_pill_for_bundle_booking($booking, $order_item_id);
						break;
				}
				$html.= '<div class="order-booking-data-heading">
									<div class="fold-order-item-wrapper fold-order-item-booking-data-form-btn">
										<div class="fold-order-item-icon"> 
											<i class="latepoint-icon latepoint-icon-chevron-up"></i>
										</div>
										<div class="ob-label">'.($booking->is_new_record() ? __('New Booking', 'latepoint') : __('Edit Booking', 'latepoint')).'</div>
									</div>
									
									<div class="remove-order-item-new-booking-btn remove-order-item-btn" data-os-prompt="'.__('Are you sure you want to remove this booking from the order?', 'latepoint').'">
										<i class="latepoint-icon latepoint-icon-trash1"></i>
									</div>
								</div>';
				$html.= '<div class="order-item-booking-data-form-inner">';
					$html.= OsFormHelper::hidden_field('order_items['.$order_item_id.'][variant]', $order_item_variant);
					ob_start();
					include(LATEPOINT_VIEWS_ABSPATH . 'bookings/_booking_data.php');
					$html.= ob_get_clean();
				$html.= '</div>';
		$html.= '</div>';
		return $html;
	}



	public static function get_orders_for_select() {
		$order = new OsOrderModel();
		$order         = $order->order_by( 'id desc' )->set_limit( 100 )->get_results_as_models();
		$order_options = [];
		foreach ( $order as $order ) {
			$name               = $order->customer->full_name . ' [' . $order->confirmation_code . ' : ' . $order->id . ']';
			$order_options[] = [ 'value' => $order->id, 'label' => esc_html( $name ) ];
		}

		return $order_options;
	}

	public static function generate_order_item_pill_for_bundle(OsBundleModel $bundle, $order_item_id = false, $preselected_booking_id = false): string {
		$html = '';
		$order_item_form_id = $order_item_id ? $order_item_id : OsUtilHelper::generate_form_id();
			$html.= '<div class="order-item-pill order-item-pill-variant-bundle" data-order-item-id="'.$order_item_form_id.'">';
			$html.= '<input name="order_items['.$order_item_form_id.'][id]" class="order_item_id" value="'.$order_item_form_id.'" type="hidden"/>';
			$html.= '<input name="order_items['.$order_item_form_id.'][variant]" value="'.LATEPOINT_ITEM_VARIANT_BUNDLE.'" type="hidden"/>';
			$html.= '<input name="order_items['.$order_item_form_id.'][item_data]" class="order_item_item_data" value="'.base64_encode(wp_json_encode($bundle->generate_params_for_booking_form())).'" type="hidden"/>';
			$html.= '<div class="order-item-pill-inner">';
				$html.= '<div class="order-item-remove-btn remove-order-item-btn" 
																	data-os-prompt="' . __('Are you sure you want to remove this item from the order? All associated appointments will be removed as well.', 'latepoint') . '"></div>';
				$html.= OsBundlesHelper::generate_order_summary_for_bundle($bundle, $order_item_form_id, $preselected_booking_id);
				$html.= '<div class="bundle-icon"><i class="latepoint-icon latepoint-icon-chevron-down"></i></div>';
			$html.= '</div>';
			$html.= '<div class="order-item-shadow"></div><div class="order-item-shadow"></div>';
		$html.= '</div>';
		return $html;
	}

	public static function generate_order_item_pill_for_booking(OsBookingModel $booking, $order_item_id = false): string {
		$html = '';
		$order_item_form_id = $order_item_id ? $order_item_id : OsUtilHelper::generate_form_id();
		$is_past = (!$booking->is_upcoming()) ? 'is-past' : '';
		$html.= '<div class="order-item-pill order-item-pill-variant-'.LATEPOINT_ITEM_VARIANT_BOOKING.' '.$is_past.' status-'.$booking->status.'" data-order-item-id="'.$order_item_form_id.'">';
			$html.= '<input name="order_items['.$order_item_form_id.'][id]" class="order_item_id" value="'.$order_item_form_id.'" type="hidden"/>';
			$html.= '<input name="order_items['.$order_item_form_id.'][variant]" value="'.LATEPOINT_ITEM_VARIANT_BOOKING.'" type="hidden"/>';
			$html.= '<div class="order-item-pill-inner">';
				$html.= '<div class="order-item-remove-btn remove-order-item-btn" 
																	data-os-prompt="' . __('Are you sure you want to remove this item from the order?', 'latepoint') . '"></div>';
				$html.= '<div class="booking-item-status-pill"></div>';
				$html.= OsBookingHelper::generate_summary_for_booking($booking);
				$html.= '<div class="os-avatar-w" style="background-image: url('.(($booking->agent->avatar_image_id) ? $booking->agent->get_avatar_url() : '').')">';
					if(!$booking->agent->avatar_image_id) $html.= '<div class="os-avatar"><span>'.$booking->agent->get_initials().'</span></div>';
				$html.= '</div>';
			$html.= '</div>';
		$html.= '</div>';
		return $html;
	}

	public static function generate_order_item_pill_for_bundle_booking(OsBookingModel $booking, $order_item_id): string {
		$is_past = (!$booking->is_upcoming()) ? 'is-past' : '';
		$html = '<div class="bundle-booking-item-pill '.$is_past.' status-'.$booking->status.'">';
			$html.= '<div class="bundle-booking-item-pill-inner">';
				$html.= '<div class="booking-item-status-pill"></div>';
				$html.= '<div class="bib-datetime">'.$booking->get_nice_start_datetime().'</div>';
			$html.= '</div>';
		$html.= '</div>';
		return $html;
	}

	public static function generate_booking_block_for_bundle_order_item(OsBookingModel $booking, $order_item_id, bool $is_booked = true, $is_preselected = false): string {
		$html = '';
		$is_booked_css = $is_booked ? 'is-booked' : '';
		$is_preselected_css = ($is_preselected ? 'is-preselected' : '');

		$html.= '<div class="order-item-variant-bundle-booking '.$is_booked_css.' '.$is_preselected_css.'"
		 data-order-item-variant="'.LATEPOINT_ITEM_VARIANT_BUNDLE.'"
		 data-order-item-id="'.$order_item_id.'"
		 data-booking-id="'.$booking->get_form_id().'">';
		$html.= '<div class="scheduled-bundle-booking">';
		$html.= self::loading_tile_for_order_item($order_item_id);
		if($is_booked){
			$html.= OsOrdersHelper::booking_data_form_for_order_item_id($order_item_id, $booking, LATEPOINT_ITEM_VARIANT_BUNDLE, !$is_preselected);
		}
		$html.= '</div>';
		$html.= '<div class="unscheduled-bundle-booking">
							<div class="booking-item-status-pill"></div>
							<div class="bib-label">'.__('Schedule now', 'latepoint').'</div>
							<input name="order_items['.$order_item_id.'][unscheduled_bookings]['.$booking->get_form_id().'][item_data]" class="unscheduled_booking_item_data" value="'.base64_encode(wp_json_encode($booking->generate_params_for_booking_form())).'" type="hidden"/>
						</div>';
		$html.= '</div>';

		return $html;
	}

	public static function generate_price_breakdown_from_params(array $price_breakdown_params) : array{
		$price_breakdown_rows = [];
		$allowed_keys         = [ 'before_subtotal', 'after_subtotal' ];
		foreach ( $allowed_keys as $key ) {
			if ( ! empty( $price_breakdown_params[ $key ] ) ) {
				foreach ( $price_breakdown_params[ $key ] as $row ) {
					if ( ! empty( $row['items'] ) ) {
						$group = [ 'heading' => '', 'items' => [], 'sub_items' => [] ];
						if ( ! empty( $row['heading'] ) ) {
							$group['heading'] = $row['heading'];
						}
						foreach ( $row['items'] as $item ) {
							if ( isset( $item['value'] ) ) {
								$item['raw_value'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format( $item['value'] );
							}
							$group['items'][] = $item;
						}
						if ( ! empty( $row['sub_items'] ) ) {
							foreach ( $row['sub_items'] as $sub_item ) {
								if ( isset( $sub_item['value'] ) ) {
									$sub_item['raw_value'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format( $sub_item['value'] );
								}
								$group['sub_items'][] = $sub_item;
							}
						}
						$price_breakdown_rows[ $key ][] = $group;
					} else {
						if ( isset( $row['value'] ) ) {
							$row['raw_value'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format( $row['value'] );
						}
						$price_breakdown_rows[ $key ][] = $row;
					}
				}
			}
		}
		return $price_breakdown_rows;
	}

	public static function create_order_item_object(array $order_items_param): OsOrderItemModel {
		$order_item_model = new OsOrderItemModel();
		$order_item_model->variant = $order_items_param['variant'];
		if($order_item_model->is_bundle()){
			$order_item_model->item_data = base64_decode($order_items_param['item_data']);
		}else{
			// it's a booking variant - so there is only one booking in the array, but still loop for ease of use
			foreach($order_items_param['bookings'] as $booking_params){
				$booking = OsOrdersHelper::create_booking_object_from_booking_data_form($booking_params);
				$order_item_model->item_data = $booking->generate_item_data();
			}
		}
		return $order_item_model;
	}

	public static function generate_transactions_breakdown_html( OsOrderModel $order ) : string {
		$html = '';
		$transactions = $order->get_transactions();
		if($transactions){
			$html.= '<div style="margin: 20px 0;">';
			foreach($transactions as $transaction) {
				$html.= '<div style="margin-bottom: 10px;">';
				$html.= '<div>' . esc_html__( 'Payment Portion: ', 'latepoint' ) . '<strong>' . $transaction->get_payment_portion_nice_name() . '</strong></div>';
				$html.= '<div>' . esc_html__( 'Payment Amount: ', 'latepoint' ) . '<strong>' . OsMoneyHelper::format_price($transaction->amount, true, false) . '</strong></div>';
				$html.= '</div>';
			}
			$html.= '</div>';
		}else{
			$html.= esc_html__('No transactions found.', 'latepoint');
		}
		return $html;
	}

	public static function generate_summary_breakdown_html( OsOrderModel $order ) : string {
	    ob_start();
		OsPriceBreakdownHelper::output_price_breakdown($order->generate_price_breakdown_rows(), true);
	    $html = ob_get_clean();
		return $html;
	}

	public static function generate_order_items_html( OsOrderModel $order ) {
		$html = '';
		$order_items = $order->get_items();
		$html.= '<table style="width: 100%;">';
		$total_items = count($order_items);
		$i = 0;
		foreach($order_items as $order_item){
			$i++;
			$html.= '<tr>';
				$data_style = ($i < $total_items) ? 'border-bottom: 1px solid #eee;' : '';
				$html.= '<td style="'.$data_style.' padding: 10px 0;">';
				if($order_item->is_bundle()){
					$bundle = $order_item->build_original_object_from_item_data();
					$html.= '<div>'.$bundle->name.'</div>';
					$bundle_services = $bundle->get_services();
					foreach ($bundle_services as $service) {
						$qty = $service->join_attributes['quantity'];
						$qty_html = $qty > 1 ? ' [' . $qty . ']' : '';
						$html.= '<div style="color: #999; font-size: 14px;">'.esc_html($service->name . $qty_html).'</div>';
					}
				}else{
					$booking = $order_item->build_original_object_from_item_data();
					$html.= '<div>'.$booking->service->name.'</div>';
					$html.= '<div>'.$booking->get_nice_start_datetime().'</div>';
					$html.= '<div>'.$booking->agent->get_full_name().'</div>';
				}
				$html.= '</td>';
			$html.= '</tr>';
		}
		$html.= '</table>';
		return $html;
	}

	public static function extract_agent_emails( OsOrderModel $order ) :string {
		$to_emails = [];
		$order_items = $order->get_items();
		foreach($order_items as $order_item){
			if($order_item->is_bundle()){
				$bundle_bookings = OsOrdersHelper::get_bookings_for_order_item($order_item->id);
				foreach($bundle_bookings as $booking){
					$to_emails[] = $booking->agent->get_full_name().' <'.$booking->agent->email.'>';
				}
			}else{
				// booking
				$booking = $order_item->build_original_object_from_item_data();
				$to_emails[] = $booking->agent->get_full_name().' <'.$booking->agent->email.'>';
			}
		}
		$to_emails = array_unique($to_emails);
		return implode(', ', $to_emails);
	}

	public static function extract_agent_full_names( OsOrderModel $order ) : string {
		$full_names = [];
		$order_items = $order->get_items();
		foreach($order_items as $order_item){
			if($order_item->is_bundle()){
				$bundle_bookings = OsOrdersHelper::get_bookings_for_order_item($order_item->id);
				foreach($bundle_bookings as $booking){
					$full_names[] = $booking->agent->get_full_name();
				}
			}else{
				// booking
				$booking = $order_item->build_original_object_from_item_data();
				$full_names[] = $booking->agent->get_full_name();
			}
		}
		$full_names = array_unique($full_names);
		return implode(', ', $full_names);
	}

}