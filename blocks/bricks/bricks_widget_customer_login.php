<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Latepoint_Bricks_Widget_Customer_Login extends \Bricks\Element {

	public $category = 'latepoint';
	public $name = 'latepoint_customer_login';
	public $icon = 'ti-lock';

	public function set_controls() {
		$this->controls['_width']['default'] = '100%';
	}

		public function get_label(): string {
		return esc_html__( 'Customer Login', 'latepoint' );
	}

	// Render element HTML
	public function render() {
		echo do_shortcode('[latepoint_customer_login]');
	}
}