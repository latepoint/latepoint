<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsCartsHelper {
	public static $cart;

	public static function reset_cart(){
		unset($_COOKIE[LATEPOINT_CART_COOKIE]);
		self::$cart = self::create_cart();
	}

	public static function get_cart_uuid(){
		if(isset($_COOKIE[LATEPOINT_CART_COOKIE])){
			return sanitize_text_field( wp_unslash($_COOKIE[LATEPOINT_CART_COOKIE]));
		}else{
			return false;
		}
	}

	public static function can_checkout_multiple_items() : bool{
		$can = apply_filters('latepoint_can_checkout_multiple_items', false);
		if($can){
			$force_disabled = OsSettingsHelper::is_on('disable_checkout_multiple_items');
			if($force_disabled){
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}

	public static function get_cart_id(){
		$cart = self::get_or_create_cart();
		return $cart->id;
	}

	public static function create_cart($persist = false){
		$cart = new OsCartModel();
		if(self::get_cart_uuid()){
			$cart->uuid = self::get_cart_uuid();
		}else{
			$cart->uuid = OsUtilHelper::generate_uuid();
			OsSessionsHelper::setcookie(LATEPOINT_CART_COOKIE, $cart->uuid);
			$_COOKIE[LATEPOINT_CART_COOKIE] = $cart->uuid;
		}
		if(!$persist) return $cart;
		if($cart->save()){
			return $cart;
		}else{
			return null;
		}
	}

	public static function clear_current_cart(){
		$cart = self::get_or_create_cart();
	}

	public static function is_current_cart_empty(){
		$cart = self::get_or_create_cart();
		$cart_items = $cart->get_items();
		return empty($cart_items);
	}

	public static function get_or_create_cart($persist = false){
		// no cart in cookie and not in database when asked for persistent one - create one
		if(!isset(self::$cart) || ($persist && empty(self::$cart->id))){
			if(empty(self::get_cart_uuid())){
				self::$cart = self::create_cart($persist);
			}else{
				// cookie is set, try to retrieve from DB
				$cart = new OsCartModel();
				$cart = $cart->where(['uuid' => self::get_cart_uuid()])->set_limit(1)->get_results_as_models();
				if(!empty($cart) && !empty($cart->id)){
					self::$cart = $cart;
				}else{
					self::$cart = self::create_cart($persist);
				}
			}
		}
		return self::$cart;
	}

	public static function add_item_to_cart(OsCartItemModel $item){
		$cart = self::get_or_create_cart(true);
		if($cart){
			$cart->add_item($item);
		}
		return false;
	}

	public static function add_bundle_to_cart(OsBundleModel $bundle) {
		$item = new OsCartItemModel();
		$item->variant = LATEPOINT_ITEM_VARIANT_BUNDLE;
		$item->item_data = wp_json_encode($bundle->generate_params_for_booking_form());
		self::add_item_to_cart($item);
	}

	public static function add_booking_to_cart(OsBookingModel $booking) {
		$item = new OsCartItemModel();
		$item->variant = LATEPOINT_ITEM_VARIANT_BOOKING;
		$item->item_data = wp_json_encode($booking->generate_params_for_booking_form());
		return self::add_item_to_cart($item);
	}

	public static function get_items_for_cart_id($cart_id) {
		$cart_items = new OsCartItemModel();
		return $cart_items->where(['cart_id' => $cart_id])->get_results_as_models();
	}



	public static function get_default_payment_portion_type($cart) {
		$regular_price = $cart->get_total();
		$deposit_price = $cart->deposit_amount_to_charge(['apply_coupons' => false]);
		if (($regular_price == 0) && ($deposit_price > 0)) {
			return LATEPOINT_PAYMENT_PORTION_DEPOSIT;
		} else {
			return LATEPOINT_PAYMENT_PORTION_FULL;
		}
	}


	public static function can_checkout(): bool {
		$cart = self::get_or_create_cart();
		return (count($cart->get_items()) > 0);
	}


	public static function create_cart_item_from_item_data(array $cart_item_data): OsCartItemModel {
		$cart_item = new OsCartItemModel();
		$cart_item->variant = $cart_item_data['variant'];
		$cart_item->item_data = wp_json_encode($cart_item_data['item_data']);

		$cart_item->subtotal = $cart_item_data['subtotal'];
		$cart_item->total = $cart_item_data['total'];
		$cart_item->coupon_discount = $cart_item_data['coupon_discount'];
		$cart_item->coupon_code = $cart_item_data['coupon_code'];
		$cart_item->tax_total = $cart_item_data['tax_total'];
		return $cart_item;
	}

}