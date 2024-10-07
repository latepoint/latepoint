<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsOrderItemModel extends OsModel {

	var $id,
		$order_id,
		$variant,
		$item_data,
		$subtotal = 0,
		$total = 0,
		$coupon_code,
		$coupon_discount = 0,
		$tax_total = 0,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_ORDER_ITEMS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	protected function params_to_sanitize() {
		return [
			'subtotal'        => 'money',
			'total'           => 'money',
			'coupon_discount' => 'money',
			'tax_total'       => 'money',
		];
	}

	public function get_coupon_code() {
		/**
		 * Get coupon code of a order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_get_coupon_code
		 *
		 * @param {string} $coupon_code Coupon code
		 * @param {OsOrderItemModel} $order_item Order Item that coupon code is requested for
		 * @returns {string} The filtered "coupon code" value
		 */
		return apply_filters('latepoint_order_item_get_coupon_code', $this->coupon_code, $this);
	}

	public function get_tax_total() {

		/**
		 * Get Total Tax amount of a order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_get_tax_total
		 *
		 * @param {float} $tax_total Total amount of tax for a order in database format 1999.0000
		 * @param {OsOrderItemModel} $order_item Order item that tax total is requested for
		 * @returns {float} The filtered "tax_total" amount
		 */
		$amount = apply_filters( 'latepoint_order_item_get_tax_total', $this->tax_total, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}

	public function get_coupon_discount() {

		/**
		 * Get coupon discount of a order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_get_coupon_discount
		 *
		 * @param {float} $discount_amount Coupon discount amount in database format 1999.0000
		 * @param {OsOrderItemModel} $order_item Order Item that coupon discount is assessed on
		 * @returns {float} The filtered "coupon discount" amount
		 */
		$amount = apply_filters('latepoint_order_item_get_coupon_discount', $this->coupon_discount, $this);
		return OsMoneyHelper::pad_to_db_format($amount);
	}

	public function get_total(){

		/**
		 * Get total of a order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_get_total
		 *
		 * @param {float} $total Total amount in database format 1999.0000
		 * @param {OsOrderItemModel} $order_item Order Item that total is assessed on
		 * @returns {float} The filtered "total" amount
		 */
		$amount = apply_filters( 'latepoint_order_item_get_total', $this->total, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}


	public function get_subtotal(){

		/**
		 * Get subtotal of a order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_get_subtotal
		 *
		 * @param {float} $subtotal Subtotal amount in database format 1999.0000
		 * @param {OsOrderItemModel} $order_item Order Item that subtotal is assessed on
		 * @returns {float} The filtered "subtotal" amount
		 */
		$amount = apply_filters( 'latepoint_order_item_get_subtotal', $this->subtotal, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}

	public function recalculate_prices(){
		$this->total = $this->full_amount_to_charge();
		$this->subtotal = $this->total;


		/**
		 * Recalculating prices for order item
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_recalculate_prices
		 *
		 * @param {OsOrderItemModel} $order_item Order item for which prices are being recalculated
		 */
		do_action('latepoint_order_item_recalculate_prices', $this);
	}


	public function full_amount_to_charge() {
		$amount = 0;
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				$amount        = OsBookingHelper::calculate_full_amount_for_booking( $original_item );
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$original_item = OsBundlesHelper::build_bundle_model_from_item_data( json_decode( $this->item_data, true ) );
				$amount        = OsBundlesHelper::calculate_full_amount_for_bundle( $original_item );
				break;
		}


		/**
		 * Filter full amount to charge on the order item object
		 *
		 * @param {float} $amount The amount to charge on the order
		 * @param {OsOrderItemModel} $order_item Order item object that full amount is calculated on
		 * @returns {float} The filtered amount to charge on the item order
		 *
		 * @since 5.0.0
		 * @hook latepoint_order_item_full_amount_to_charge
		 *
		 */
		$amount = apply_filters( 'latepoint_order_item_full_amount_to_charge', $amount, $this );
		return $amount;
	}

	public function is_bundle(): bool {
		return ( $this->variant == LATEPOINT_ITEM_VARIANT_BUNDLE );
	}

	public function get_order(): OsOrderModel {
		return new OsOrderModel( $this->order_id );
	}

	public function is_booking(): bool {
		return ( $this->variant == LATEPOINT_ITEM_VARIANT_BOOKING );
	}

	public function generate_data_vars(): array {
		$vars = [
			'id' => $this->id,
			'variant' => $this->variant,
			'subtotal' => $this->get_subtotal(),
			'total' => $this->get_total(),
		];
		if($this->is_booking()){
			$booking = $this->build_original_object_from_item_data();
			$vars['item_data'] = $booking->get_first_level_data_vars();
		}
		if($this->is_bundle()){
			$bundle = $this->build_original_object_from_item_data();
			$vars['item_data'] = $bundle->get_data_vars();
		}
		return $vars;
	}

	public function delete( $id = false ) {
		if ( ! $id && isset( $this->id ) ) {
			$id = $this->id;
		}

		$bookings = new OsBookingModel();
		$bookings->delete_where( [ 'order_item_id' => $id ] );

		return parent::delete( $id );
	}

	/**
	 * @return OsBookingModel|OsBundleModel
	 */
	public function build_original_object_from_item_data( string $bundle_booking_id = '' ) {
		$original_item = false;
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				if ( $bundle_booking_id ) {
					$item_data     = json_decode( $this->item_data, true );
					$original_item = ! empty( $item_data['bookings'][ $bundle_booking_id ] ) ? OsBookingHelper::build_booking_model_from_item_data( $item_data['bookings'][ $bundle_booking_id ] ) : new OsBookingModel();
				} else {
					$original_item = OsBundlesHelper::build_bundle_model_from_item_data( json_decode( $this->item_data, true ) );
				}
				break;
		}

		return apply_filters( 'latepoint_order_item_original_object', $original_item, $this );
	}

	public function view_as_cart_item(): OsCartItemModel {
		$cart_item                  = new OsCartItemModel();
		$cart_item->variant         = $this->variant;
		$cart_item->item_data       = $this->item_data;
		$cart_item->subtotal        = $this->get_subtotal();
		$cart_item->total           = $this->get_total();
		$cart_item->coupon_discount = $this->get_coupon_discount();
		$cart_item->coupon_code     = $this->get_coupon_code();
		$cart_item->tax_total       = $this->get_tax_total();

		return $cart_item;
	}

	public function retrieve_original_object() {
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$bookings        = new OsBookingModel();
				$original_object = $bookings->where( [ 'order_item_id' => $this->id ] )->set_limit( 1 )->get_results_as_models();
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$bundle_id       = $this->get_item_data_value_by_key( 'bundle_id' );
				$original_object = new OsBundleModel( $bundle_id );
				break;
		}

		return $original_object;
	}

	public function get_item_data_value_by_key( string $key, $default = '' ) {
		$data = json_decode( $this->item_data, true );

		return $data[ $key ] ?? $default;
	}


	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'id',
			'order_id',
			'price',
			'variant',
			'item_data',
			'subtotal',
			'total',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'updated_at',
			'created_at'
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'id',
			'order_id',
			'price',
			'variant',
			'item_data',
			'subtotal',
			'total',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'updated_at',
			'created_at'
		);

		return $params_to_save;
	}
}