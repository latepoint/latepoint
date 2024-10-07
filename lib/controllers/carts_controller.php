<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCartsController' ) ) :


  class OsCartsController extends OsController {

    function __construct(){
      parent::__construct();
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'carts/';

			$this->action_access['public'] = array_merge($this->action_access['public'], ['remove_item_from_cart']);
    }

		public function remove_item_from_cart(){
			$cart_item_id = $this->params['cart_item_id'];
			$cart_item = new OsCartItemModel($cart_item_id);
			$current_cart = OsCartsHelper::get_or_create_cart();
			if($current_cart->remove_item($cart_item)){
				$status = LATEPOINT_STATUS_SUCCESS;
				$response_html = __('Booking removed from your cart', 'latepoint');
			}else{
				$status = LATEPOINT_STATUS_ERROR;
				$response_html = __('Not Allowed', 'latepoint');
			}
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }

		}


  }


endif;