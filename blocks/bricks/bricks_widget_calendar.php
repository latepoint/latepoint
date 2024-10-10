<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Latepoint_Bricks_Widget_Calendar extends \Bricks\Element {

	public $category = 'latepoint';
	public $name = 'latepoint_calendar';
	public $icon = 'ti-calendar';


	public function get_label(): string {
		return esc_html__( 'Latepoint Calendar', 'latepoint' );
	}

	public function set_controls() {
		$this->controls['_width']['default']   = '100%';

		$this->controls['date'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Date', 'latepoint' ),
			'type'    => 'datepicker',
			'inline'  => true,
			'options' => [
				'enableTime' => false,
				'time_24hr'  => true
			]
		];

		$this->controls['show_agents'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Show Agents', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('agents'),
			'placeholder' => esc_html__( 'Select Agents', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];

		$this->controls['show_services'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Show Services', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('services'),
			'placeholder' => esc_html__( 'Select Services', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];

		$this->controls['show_locations'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Show Locations', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('locations'),
			'placeholder' => esc_html__( 'Select Locations', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];

		$this->controls['view'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'View', 'latepoint' ),
			'type'        => 'select',
			'options' => [
				'month' => esc_html__( 'Month', 'latepoint' ),
				'week' => esc_html__( 'Week', 'latepoint' ),
			],
			'placeholder' => esc_html__( 'Month', 'latepoint' ),
			'default' => 'month',
		];
	}


	// Render element HTML
	public function render() {
		$allowed_params = [
			'date',
			'show_services',
			'show_agents',
			'show_locations',
			'view'
		];

		$params = OsBlockHelper::attributes_to_data_params($this->settings, $allowed_params);
		echo do_shortcode('[latepoint_calendar ' . $params . ']');
	}
}