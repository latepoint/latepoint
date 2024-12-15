<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsCartModel extends OsModel {
	public $items; // should NOT be set by default, it means they are not loaded, to avoid queries to DB

	public $id,
		$uuid,
		$order_id,
		$coupon_code = '',
		$order_intent_id,
		$payment_method,
		$payment_portion,
		$payment_time,
		$payment_token,
		$payment_processor,
		$source_id = '',
		$order_forced_customer_id = false, // only used for when you creating a cart from an order
		$subtotal = 0,
		$total = 0,
		$coupon_discount = 0,
		$tax_total = 0,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_CARTS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}


	public function get_total() {

		/**
		 * Get total of a cart
		 *
		 * @param {float} $total Total amount in database format 1999.0000
		 * @param {OsCartModel} $cart Cart that total is assessed on
		 * @returns {float} The filtered "total" amount
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_get_total
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_get_total', $this->total, $this );

		return OsMoneyHelper::pad_to_db_format( $amount );
	}

	public function get_order_intent() : OsOrderIntentModel {
		return new OsOrderIntentModel($this->order_intent_id);
	}


	public function get_subtotal() {

		/**
		 * Get subtotal of a cart
		 *
		 * @param {float} $subtotal Subtotal amount in database format 1999.0000
		 * @param {OsCartModel} $cart Cart that subtotal is assessed on
		 * @returns {float} The filtered "subtotal" amount
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_get_subtotal
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_get_subtotal', $this->subtotal, $this );

		return OsMoneyHelper::pad_to_db_format( $amount );
	}

	public function get_coupon_discount() {

		/**
		 * Get coupon discount of a cart
		 *
		 * @param {float} $discount_amount Coupon discount amount in database format 1999.0000
		 * @param {OsCartModel} $cart Cart that coupon discount is assessed on
		 * @returns {float} The filtered "coupon discount" amount
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_get_coupon_discount
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_get_coupon_discount', $this->coupon_discount, $this );

		return OsMoneyHelper::pad_to_db_format( $amount );
	}


	public function get_tax_total() {

		/**
		 * Get Total Tax amount of a cart
		 *
		 * @param {float} $tax_total Total amount of tax for a cart in database format 1999.0000
		 * @param {OsCartModel} $cart Cart that tax total is requested for
		 * @returns {float} The filtered "tax_total" amount
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_get_tax_total
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_get_tax_total', $this->tax_total, $this );

		return OsMoneyHelper::pad_to_db_format( $amount );
	}

	public function get_coupon_code() {
		/**
		 * Get coupon code of a cart
		 *
		 * @param {string} $coupon_code Coupon code
		 * @param {OsCartItemModel} $cart Cart Item that coupon code is requested for
		 * @returns {string} The filtered "coupon code" value
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_get_coupon_code
		 *
		 */
		return apply_filters( 'latepoint_cart_get_coupon_code', $this->coupon_code, $this );
	}


	public function set_coupon_code( string $coupon_code ) {
		$this->coupon_code = $coupon_code;
		if ( ! $this->is_new_record() ) {
			$this->update_attributes( [ 'coupon_code' => $coupon_code ] );
		}
	}


	public function clear_coupon_code() {
		$this->coupon_code = '';
		if ( ! $this->is_new_record() ) {
			$this->update_attributes( [ 'coupon_code' => '' ] );
		}
	}

	/**
	 * @return OsBookingModel[]
	 */
	public function get_bookings_from_cart_items(): array {
		$cart_bookings = [];
		foreach ( $this->get_items() as $cart_item ) {
			if ( $cart_item->is_booking() ) {
				$cart_bookings[ $cart_item->id ] = $cart_item->build_original_object_from_item_data();
			}
		}

		return $cart_bookings;
	}


	/**
	 * @return OsBundleModel[]
	 */
	public function get_bundles_from_cart_items(): array {
		$cart_bundles = [];
		foreach ( $this->get_items() as $cart_item ) {
			if ( $cart_item->is_bundle() ) {
				$cart_bundles[ $cart_item->id ] = $cart_item->build_original_object_from_item_data();
			}
		}

		return $cart_bundles;
	}

	public function is_empty(): bool {
		return ! $this->get_items();
	}


	public function delete_meta_by_key( $meta_key ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsCartMetaModel();

		return $meta->delete_by_key( $meta_key, $this->id );
	}

	public function get_meta_by_key( $meta_key, $default = false ) {
		if ( $this->is_new_record() ) {
			return $default;
		}

		$meta = new OsCartMetaModel();

		return $meta->get_by_key( $meta_key, $this->id, $default );
	}

	public function save_meta_by_key( $meta_key, $meta_value ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsCartMetaModel();

		return $meta->save_by_key( $meta_key, $meta_value, $this->id );
	}


	public function clear(): void {
		// remove current cart items
		foreach ( $this->get_items() as $cart_item ) {
			$cart_item->delete();
		}
		unset( $this->items ); // important to unset, to avoid db queries
	}

	/** ?
	 *
	 * @return OsCartItemModel[]
	 */
	public function get_items(): array {
		// only call DB when needed
		if ( ! isset( $this->items ) && ! empty( $this->id ) ) {
			$this->items = OsCartsHelper::get_items_for_cart_id( $this->id );
		}

		if ( empty( $this->items ) ) {
			$this->items = [];
		}

		return $this->items;
	}

	/**
	 * @param array $rows_to_hide
	 *
	 * @return array[]
	 */
	public function generate_price_breakdown_rows( array $rows_to_hide = [] ): array {
		$rows = [
			'before_subtotal' => [],
			'subtotal'        => [],
			'after_subtotal'  => [],
			'total'           => [],
			'balance'         => []
		];

		$items = $this->get_items();


		// payments and balance have to always be recalculated, even if requested for existing booking
		if ( ! in_array( 'balance', $rows_to_hide ) ) {
			$balance_due_amount = $this->total;
			$rows['balance']    = [
				'label'     => __( 'Balance Due', 'latepoint' ),
				'raw_value' => OsMoneyHelper::pad_to_db_format( $balance_due_amount ),
				'value'     => OsMoneyHelper::format_price( $balance_due_amount, true, false ),
				'style'     => 'total'
			];
		}

		foreach ( $items as $item ) {
			switch ( $item->variant ) {
				case LATEPOINT_ITEM_VARIANT_BOOKING:
					$booking = $item->build_original_object_from_item_data();

					// recalculations are below this point
					$service_row               = [
						'heading' => __( 'Service', 'latepoint' ),
						'items'   => []
					];
					$item_subtotal             = OsBookingHelper::calculate_full_amount_for_service( $booking );
					$service_row_item          = [
						'label'     => $booking->service->name,
						'raw_value' => OsMoneyHelper::pad_to_db_format( $item_subtotal ),
						'value'     => OsMoneyHelper::format_price( $item_subtotal, true, false )
					];
					$service_row['items'][]    = $service_row_item;
					$service_row               = apply_filters( 'latepoint_price_breakdown_service_row_for_booking', $service_row, $booking );
					$rows['before_subtotal'][] = $service_row;
					break;
				case LATEPOINT_ITEM_VARIANT_BUNDLE:
					// TODO Merge somehow this case with the booking case as they are reusing a lot of code
					// recalculations are below this point
					$bundle                    = $item->build_original_object_from_item_data();
					$service_row               = [
						'heading' => __( 'Bundle', 'latepoint' ),
						'items'   => []
					];
					$item_subtotal             = OsBundlesHelper::calculate_full_amount_for_bundle( $bundle );
					$service_row_item          = [
						'label'     => $bundle->name,
						'raw_value' => OsMoneyHelper::pad_to_db_format( $item_subtotal ),
						'value'     => OsMoneyHelper::format_price( $item_subtotal, true, false )
					];
					$service_row['items'][]    = $service_row_item;
					$service_row               = apply_filters( 'latepoint_price_breakdown_service_row_for_bundle', $service_row, $bundle );
					$rows['before_subtotal'][] = $service_row;
					break;
			}
		}


		if ( ! in_array( 'subtotal', $rows_to_hide ) ) {
			$subtotal_amount  = $this->subtotal;
			$rows['subtotal'] = [
				'label'     => __( 'Sub Total', 'latepoint' ),
				'style'     => 'strong',
				'raw_value' => OsMoneyHelper::pad_to_db_format( $subtotal_amount ),
				'value'     => OsMoneyHelper::format_price( $subtotal_amount, true, false )
			];
		}

		if ( ! in_array( 'total', $rows_to_hide ) ) {
			$total_amount  = $this->total;
			$rows['total'] = [
				'label'     => __( 'Total Price', 'latepoint' ),
				'style'     => in_array( 'balance', $rows_to_hide ) ? 'total' : 'strong',
				'raw_value' => OsMoneyHelper::pad_to_db_format( $total_amount ),
				'value'     => OsMoneyHelper::format_price( $total_amount, true, false )
			];
		}

		// filter only applies when recalculating rows, do not apply it to the existing data, since it has already ran
		return apply_filters( 'latepoint_cart_price_breakdown_rows', $rows, $this, $rows_to_hide );
	}


	/**
	 * @param array $options
	 *
	 * @return mixed|void
	 *
	 * Returns amount to charge depending on a portion set in database format 1999.0000
	 *
	 */
	public function amount_to_charge( array $options = [] ) {
		$amount = ( $this->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT ) ? $this->deposit_amount_to_charge( $options ) : $this->full_amount_to_charge( $options );

		return apply_filters( 'latepoint_cart_amount_to_charge', $amount, $this, $options );
	}


	/**
	 * @param array $options
	 *
	 * @return mixed|void
	 *
	 * Returns deposit amount to charge in database format 1999.0000
	 *
	 */
	public function deposit_amount_to_charge( array $options = [] ) {
		$default_options = [ 'apply_coupons' => false, 'apply_taxes' => false ];
		$options         = array_merge( $default_options, $options );
		$amount          = 0;
		$items           = $this->get_items();
		if ( empty( $items ) ) {
			return $amount;
		}
		foreach ( $items as $item ) {
			$amount += $item->deposit_amount_to_charge( $options );
		}

		/**
		 * Filter deposit amount to charge on the cart object
		 *
		 * @param {float} $amount The amount to charge on the cart
		 * @param {OsCartModel} $cart Cart object that deposit amount is calculated on
		 * @param {array} $options Array of options that determine if taxes and coupons should be applied
		 * @returns {float} The filtered amount to charge on the cart
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_deposit_amount_to_charge
		 *
		 */
		return apply_filters( 'latepoint_cart_deposit_amount_to_charge', $amount, $this, $options );
	}

	public function deposit_amount_to_charge_formatted( array $options = [] ) {
		$amount = $this->deposit_amount_to_charge( $options );

		return OsMoneyHelper::format_price( $amount, true, false );
	}

	/**
	 * @param array $options
	 *
	 * @return mixed|void
	 *
	 * Returns full amount to charge in database format 1999.0000
	 *
	 */
	public function full_amount_to_charge( array $options = [] ) {
		/**
		 * Get full amount to charge
		 *
		 * @param {float} $total Full amount to charge database format 1999.0000
		 * @param {OsCartModel} $cart Cart that total is assessed on
		 * @returns {float} The filtered full amount to charge
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_full_amount_to_charge
		 *
		 */
		$amount = apply_filters( 'latepoint_cart_full_amount_to_charge', $this->get_total(), $this, $options );

		return OsMoneyHelper::pad_to_db_format( $amount );
	}


	public function specs_calculate_amount_to_charge() {
		if ( $this->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT ) {
			return $this->specs_calculate_deposit_amount_to_charge();
		} else {
			return $this->specs_calculate_full_amount_to_charge();
		}
	}

	public function specs_calculate_full_amount_to_charge() {
		return OsPaymentsHelper::convert_charge_amount_to_requirements( $this->get_total(), $this );
	}

	public function specs_calculate_deposit_amount_to_charge() {
		return OsPaymentsHelper::convert_charge_amount_to_requirements( $this->deposit_amount_to_charge(), $this );
	}

	public function get_total_formatted() {
		return OsMoneyHelper::format_price( $this->get_total(), true, false );
	}

	public function set_payment_portion() {
		if ( ! empty( $this->payment_time ) ) {
			if ( $this->payment_time == LATEPOINT_PAYMENT_TIME_LATER ) {
				$this->payment_portion = LATEPOINT_PAYMENT_PORTION_FULL;
			} else {
				$deposit_amount        = $this->deposit_amount_to_charge();
				$this->payment_portion = ( $deposit_amount > 0 ) ? LATEPOINT_PAYMENT_PORTION_DEPOSIT : LATEPOINT_PAYMENT_PORTION_FULL;
			}
		}
	}

	public function set_payment_processor() {
		if ( empty( $this->payment_processor ) && ! empty( $this->payment_time ) && ! empty( $this->payment_method ) ) {
			$enabled_processors = OsPaymentsHelper::get_enabled_payment_processors_for_payment_time_and_method( $this->payment_time, $this->payment_method );
			if ( count( $enabled_processors ) == 1 ) {
				$this->payment_processor = array_key_first( $enabled_processors );
			}
		}
	}

	public function set_payment_time() {
		if ( empty( $this->payment_time ) ) {
			$enabled_payment_times = OsPaymentsHelper::get_enabled_payment_times();
			if ( count( $enabled_payment_times ) == 1 ) {
				$this->payment_time = array_key_first( $enabled_payment_times );
			}
		}
	}

	public function set_payment_method() {
		if ( ! empty( $this->payment_time ) ) {
			$enabled_payment_methods = OsPaymentsHelper::get_enabled_payment_methods_for_payment_time( $this->payment_time );
			if ( count( $enabled_payment_methods ) == 1 ) {
				$this->payment_method = array_key_first( $enabled_payment_methods );
			}
		}
	}

	public function set_singular_payment_attributes() {
		$this->set_payment_time();
		$this->set_payment_portion();
		$this->set_payment_method();
		$this->set_payment_processor();
	}

	public function remove_item( OsCartItemModel $item ) {
		if ( $item->id && $this->id == $item->cart_id ) {
			$item->delete();
			$this->items = OsCartsHelper::get_items_for_cart_id( $this->id );
		}
		$this->calculate_prices();

		return true;
	}

	public function add_item( OsCartItemModel $item, bool $permanent = true, bool $calculate_prices = true ) {
		if ( $permanent ) {
			// save cart itself if not saved yet, since it's a permanent addition to cart
			if ( empty( $this->id ) ) {
				$this->save();
			}
			$item->cart_id = $this->id;
			if ( $item->save() ) {
				// we are doing this - to modify a copy of $items, to avoid modifying the getter's return value
				$items       = $this->get_items();
				$items[]     = $item;
				$this->items = $items;
			}
		} else {
			// we are doing this - to modify a copy of $items, to avoid modifying the getter's return value
			$items       = $this->get_items();
			$items[]     = $item;
			$this->items = $items;
		}
		if ( $calculate_prices ) {
			$this->calculate_prices();
		}

		return true;
	}

	public function calculate_prices() {

		// calculate subtotal for all items
		foreach ( $this->get_items() as $item ) {
			$item->subtotal = $item->full_amount_to_charge();
			$item->total    = $item->subtotal;
		}


		// do cart subtotal
		$this->subtotal = 0;
		foreach ( $this->get_items() as $item ) {
			$this->subtotal = $this->subtotal += $item->subtotal;
		}
		// do cart total
		$this->total = 0;
		foreach ( $this->get_items() as $item ) {
			$this->total = $this->total += $item->total;
		}


		/**
		 * Triggers when cart prices are being calculated
		 *
		 * @param {OsCartModel} $cart Cart model for which prices are being generated
		 *
		 * @since 5.0.0
		 * @hook latepoint_cart_calculate_prices
		 *
		 */
		do_action( 'latepoint_cart_calculate_prices', $this );

	}


	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'payment_method',
			'payment_portion',
			'payment_processor',
			'payment_time',
			'coupon_code',
			'payment_token',
			'source_id'
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'id',
			'uuid',
			'order_intent_id',
			'order_id',
			'coupon_code',
			'updated_at',
			'created_at'
		);

		return $params_to_save;
	}
}