<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsManageOrderByKeyController' ) ) :


	class OsManageOrderByKeyController extends OsController {
		private $order;
		private $key_for;
		private $key = '';

		private function set_order_by_key() {
			if ( empty( $this->params['key'] ) ) {
				return;
			}
			$key = sanitize_text_field( $this->params['key'] );
			$data = OsOrdersHelper::get_order_id_and_manage_ability_by_key( $key );
			if ( empty( $data ) ) {
				return;
			}
			$order = new OsOrderModel( $data['order_id'] );
			if ( $order->id ) {
				$this->key     = $key;
				$this->order   = $order;
				$this->key_for = $data['for'];
			}
		}

		function __construct() {
			parent::__construct();
			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'manage_order_by_key/';

			$this->action_access['public'] = array_merge( $this->action_access['public'], [
				'show',
				'print_order_info',
				'list_payments'
			] );

			$this->set_order_by_key();

		}

		function list_payments(){
			if ( empty( $this->order->id ) ) {
				return;
			}

			$transactions = $this->order->get_transactions();

			$this->vars['order'] = $this->order;
			$this->vars['transactions'] = $transactions;

				$this->set_layout( 'clean' );
			$this->format_render(__FUNCTION__);
		}


		function show() {
			if ( empty( $this->order->id ) ) {
				return;
			}


			$this->vars['key']     = $this->key;
			$this->vars['for']     = $this->key_for;
			$this->vars['order']   = $this->order;

			$this->vars['timezone_name'] = $this->key_for == 'agent' ? OsTimeHelper::get_wp_timezone_name() : $this->order->customer->get_selected_timezone_name();

			if ( $this->get_return_format() == 'json' ) {
				$this->set_layout( 'none' );
				$response_html = $this->format_render_return( __FUNCTION__ );
				$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ) );
			} else {
				$this->set_layout( 'clean' );
				$content = $this->format_render_return( __FUNCTION__ );
				echo $content;
			}
		}


		function print_order_info() {
			if ( empty( $this->order->id ) ) {
				return;
			}

			$this->vars['order']    = $this->order;
			$this->vars['customer'] = $this->order->customer;
			$this->set_layout( 'print' );
			$content = $this->format_render_return( __FUNCTION__, [], [], true );
			echo $content;
		}


	}
endif;