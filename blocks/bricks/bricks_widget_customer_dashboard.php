<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Latepoint_Bricks_Widget_Customer_Dashboard extends \Bricks\Element {

	public $category = 'latepoint';
	public $name = 'latepoint_customer_dashboard';
	public $icon = 'ti-blackboard';


	public function get_label(): string {
		return esc_html__( 'Customer Dashboard', 'latepoint' );
	}

	public function set_controls() {
		$this->controls['_width']['default']   = '100%';

		$this->controls['hide_new_appointment_ui'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Hide New Appointment UI', 'latepoint' ),
			'type'        => 'checkbox',
			'inline'      => true,
		);
	}


	// Render element HTML
	public function render() {
		$allowed_params = [
			'hide_new_appointment_ui'
		];

		$params = OsBlockHelper::attributes_to_data_params($this->settings, $allowed_params);

		echo do_shortcode('[latepoint_customer_dashboard ' . $params . ']');
	}
}