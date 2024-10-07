<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsCartItemModel extends OsModel {

	var $id,
		$updated_at,
		$created_at,
		$cart_id,
		$subtotal = 0,
		$total = 0,
		$coupon_code = '',
		$coupon_discount = 0,
		$tax_total = 0,
		$variant,
		$item_data;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_CART_ITEMS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	public function is_bundle(): bool {
		return ( $this->variant == LATEPOINT_ITEM_VARIANT_BUNDLE );
	}

	public function is_booking(): bool {
		return ( $this->variant == LATEPOINT_ITEM_VARIANT_BOOKING );
	}

	public function get_item_image_url() {
		$image_url = '';
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				$image_url     = $original_item->service->get_selection_image_url();
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$image_url = '';
				break;
		}

		/**
		 * Returns an image url for a cart item
		 *
		 * @param {string} Image URL of a cart item
		 * @param {OsCartItemModel} Cart item object
		 *
		 * @returns {string} Filtered image url for a cart item
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_image_url
		 *
		 */
		return apply_filters( 'latepoint_cart_item_get_image_url', $image_url, $this );
	}

	public function get_item_display_name() {
		$display_name = '';
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				$display_name  = $original_item->service->name;
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$original_item = OsBundlesHelper::build_bundle_model_from_item_data( json_decode( $this->item_data, true ) );
				$display_name  = $original_item->name;
				break;
		}

		/**
		 * Returns a display name of a cart item
		 *
		 * @param {string} Display name of a cart item
		 * @param {OsCartItemModel} Cart item object
		 *
		 * @returns {string} Filtered display name for a cart item
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_item_display_name
		 *
		 */
		return apply_filters( 'latepoint_cart_item_get_item_display_name', $display_name, $this );
	}



	public function get_coupon_code() {
		/**
		 * Get coupon code applied to a cart item
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_coupon_code
		 *
		 * @param {string} $coupon_code Coupon code
		 * @param {OsCartItemModel} $cart_item Cart Item that coupon code is requested for
		 * @returns {string} The filtered "coupon code" value
		 */
		return apply_filters('latepoint_cart_item_get_coupon_code', $this->coupon_code, $this);
	}

	public function get_tax_total() {

		/**
		 * Get Total Tax amount of a cart item
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_tax_total
		 *
		 * @param {float} $tax_total Total amount of tax for a cart in database format 1999.0000
		 * @param {OsCartItemModel} $cart_item Cart item that tax total is requested for
		 * @returns {float} The filtered "tax_total" amount
		 */
		$amount = apply_filters( 'latepoint_cart_item_get_tax_total', $this->tax_total, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}

	public function get_coupon_discount() {

		/**
		 * Get coupon discount of a cart item
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_coupon_discount
		 *
		 * @param {float} $discount_amount Coupon discount amount in database format 1999.0000
		 * @param {OsCartItemModel} $cart_item Cart Item that coupon discount is assessed on
		 * @returns {float} The filtered "coupon discount" amount
		 */
		$amount = apply_filters('latepoint_cart_item_get_coupon_discount', $this->coupon_discount, $this);
		return OsMoneyHelper::pad_to_db_format($amount);
	}

	public function get_total(){

		/**
		 * Get total of a cart item
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_total
		 *
		 * @param {float} $total Total amount in database format 1999.0000
		 * @param {OsCartItemModel} $cart_item Cart Item that total is assessed on
		 * @returns {float} The filtered "total" amount
		 */
		$amount = apply_filters( 'latepoint_cart_item_get_total', $this->total, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}


	public function get_subtotal(){

		/**
		 * Get subtotal of a cart item
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_get_subtotal
		 *
		 * @param {float} $subtotal Subtotal amount in database format 1999.0000
		 * @param {OsCartItemModel} $cart_item Cart Item that subtotal is assessed on
		 * @returns {float} The filtered "subtotal" amount
		 */
		$amount = apply_filters( 'latepoint_cart_item_get_subtotal', $this->subtotal, $this );
		return OsMoneyHelper::pad_to_db_format($amount);
	}


	public function get_item_data_value_by_key( string $key, $default = '' ) {
		$data = json_decode( $this->item_data, true );

		return $data[ $key ] ?? $default;
	}

	/**
	 * @return OsBookingModel|OsBundleModel
	 */
	public function build_original_object_from_item_data() {
		$original_item = false;
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$original_item = OsBundlesHelper::build_bundle_model_from_item_data( json_decode( $this->item_data, true ) );
				break;
		}

		/**
		 * Returns an original object for a cart item
		 *
		 * @param {OsModel} Original object for a cart item
		 * @param {OsCartItemModel} Cart item object
		 *
		 * @returns {OsModel} Filtered original object
		 * @since 5.0.0
		 * @hook latepoint_cart_item_original_object
		 *
		 */
		return apply_filters( 'latepoint_cart_item_original_object', $original_item, $this );
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
		 * Filter full amount to charge on the cart item object
		 *
		 * @param {float} $amount The amount to charge on the cart
		 * @param {OsCartItemModel} $cart_item Cart item object that full amount is calculated on
		 * @returns {float} The filtered amount to charge on the item cart
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_full_amount_to_charge
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_item_full_amount_to_charge', $amount, $this );
		return $amount;
	}

	public function deposit_amount_to_charge(  ) {
		$amount = 0;
		switch ( $this->variant ) {
			case LATEPOINT_ITEM_VARIANT_BOOKING:
				$original_item = OsBookingHelper::build_booking_model_from_item_data( json_decode( $this->item_data, true ) );
				$amount        = $original_item->deposit_amount_to_charge();
				break;
			case LATEPOINT_ITEM_VARIANT_BUNDLE:
				$original_item = OsBundlesHelper::build_bundle_model_from_item_data( json_decode( $this->item_data, true ) );
				$amount        = $original_item->deposit_amount_to_charge();
				break;
		}

		/**
		 * Filter deposit amount to charge on the cart item object
		 *
		 * @param {float} $amount The amount to charge on the cart
		 * @param {OsCartItemModel} $cart_item Cart item object that deposit amount is calculated on
		 * @returns {float} The filtered amount to charge on the item cart
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_item_deposit_amount_to_charge
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_item_deposit_amount_to_charge', $amount, $this );
		return $amount;
	}

	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'id',
			'cart_id',
			'variant',
			'item_data',
			'updated_at',
			'created_at'
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'id',
			'cart_id',
			'variant',
			'item_data',
			'updated_at',
			'created_at'
		);

		return $params_to_save;
	}
}