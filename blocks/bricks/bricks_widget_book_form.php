<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Latepoint_Bricks_Widget_Book_Form extends \Bricks\Element {

	public $category = 'latepoint';
	public $name = 'latepoint_book_form';
	public $icon = 'ti-layout-media-right-alt';
	public $scripts = ['init_booking_form'];


	public function get_label(): string {
		return esc_html__( 'Booking Form', 'latepoint' );
	}

	public function enqueue_scripts() {
		if ( bricks_is_builder() ) {
			wp_enqueue_script(
				'bricks_widget_book_form_script',
				LATEPOINT_PLUGIN_URL . 'blocks/assets/javascripts/bricks-widget-book-form.js',
				[ 'jquery' ],
				LATEPOINT_VERSION
			);
		}
	}

	public function set_control_groups() {
		$this->control_groups['general'] = array(
			'title' => esc_html__( 'Booking Form Settings', 'latepoint' ),
			'tab'   => 'content',
		);
		$this->control_groups['step_settings'] = array(
			'title' => esc_html__( 'Step Settings', 'latepoint' ),
			'tab'   => 'content',
		);
		$this->control_groups['other_settings'] = array(
			'title' => esc_html__( 'Other Settings', 'latepoint' ),
			'tab'   => 'content',
		);
	}

	// Set builder controls
	public function set_controls() {
		$this->controls['_width']['default']   = '100%';

		$this->controls['hide_summary'] = array(
			'tab'         => 'content',
			'group'       => 'general',
			'label'       => esc_html__( 'Hide Summary Panel', 'latepoint' ),
			'type'        => 'checkbox',
			'inline'      => true,
		);

		$this->controls['hide_side_panel'] = array(
			'tab'         => 'content',
			'group'       => 'general',
			'label'       => esc_html__( 'Hide Side Panel', 'latepoint' ),
			'type'        => 'checkbox',
			'inline'      => true,
		);


		#step settings group

		$this->controls['selected_agent'] = [
			'tab'         => 'content',
			'group'       => 'step_settings',
			'label'       => esc_html__( 'Preselected Agent', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('selected_agents'),
			'placeholder' => esc_html__( 'Preselected Agent', 'latepoint' ),
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['selected_service'] = [
			'tab'         => 'content',
			'group'       => 'step_settings',
			'label'       => esc_html__( 'Preselected Service', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('selected_services'),
			'placeholder' => esc_html__( 'Preselected Service', 'latepoint' ),
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['selected_service_category'] = [
			'tab'         => 'content',
			'group'       => 'step_settings',
			'label'       => esc_html__( 'Preselected Service Category', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('selected_service_categories'),
			'placeholder' => esc_html__( 'Preselected Service Category', 'latepoint' ),
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['selected_location'] = [
			'tab'         => 'content',
			'group'       => 'step_settings',
			'label'       => esc_html__( 'Preselected Location', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('selected_locations'),
			'placeholder' => esc_html__( 'Preselected Location', 'latepoint' ),
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['selected_start_date'] = [
			'tab'     => 'content',
			'group'   => 'step_settings',
			'label'   => esc_html__( 'Preselected Booking Start Date', 'latepoint' ),
			'type'    => 'datepicker',
			'inline'  => true,
			'options' => [
				'enableTime' => false,
				'time_24hr'  => true
			]
		];
		$this->controls['selected_start_time'] = [
			'tab'     => 'content',
			'group'   => 'step_settings',
			'label'   => esc_html__( 'Preselected Booking Start Time', 'latepoint' ),
			'type'    => 'datepicker',
			'inline'  => true,
			'options' => [
				'enableTime' => true,
				'time_24hr'  => true,
				'noCalendar' => true
			]
		];

		$this->controls['selected_duration'] = [
			'tab'    => 'content',
			'group'  => 'step_settings',
			'label'  => esc_html__( 'Preselected Duration', 'latepoint' ),
			'type'   => 'number',
			'min'    => 0,
			'inline' => true,
		];
		$this->controls['selected_total_attendees'] = [
			'tab'    => 'content',
			'group'  => 'step_settings',
			'label'  => esc_html__( 'Preselected Total Attendees', 'latepoint' ),
			'type'   => 'number',
			'min'    => 0,
			'inline' => true,
		];


		#other settings
		$this->controls['source_id'] = [
			'tab'    => 'content',
			'group'  => 'other_settings',
			'label'  => esc_html__( 'Source ID', 'latepoint' ),
			'type'   => 'number',
			'min'    => 0,
			'inline' => true,
		];
		$this->controls['calendar_start_date'] = [
			'tab'     => 'content',
			'group'   => 'other_settings',
			'label'   => esc_html__( 'Calendar Start Date', 'latepoint' ),
			'type'    => 'datepicker',
			'inline'  => true,
			'options' => [
				'enableTime' => false,
				'time_24hr'  => true
			]
		];
		$this->controls['show_services'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Services', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('services'),
			'placeholder' => esc_html__( 'Show Services', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['show_service_categories'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Service Categories', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('service_categories'),
			'placeholder' => esc_html__( 'Show Service Categories', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];
		$this->controls['show_agents'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Agents', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('agents'),
			'placeholder' => esc_html__( 'Show Agents', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];

		$this->controls['show_locations'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Locations', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('locations'),
			'placeholder' => esc_html__( 'Show Locations', 'latepoint' ),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
		];


	}


	// Render element HTML
	public function render() {

		$allowed_params = [
			'hide_summary',
			'hide_side_panel',
			'selected_agent',
			'selected_service',
			'selected_service_category',
			'selected_location',
			'selected_start_date',
			'selected_start_time',
			'selected_duration',
			'selected_total_attendees',
			'source_id',
			'calendar_start_date',
			'show_services',
			'show_service_categories',
			'show_agents',
			'show_locations',
		];

		$params = OsBlockHelper::attributes_to_data_params($this->settings, $allowed_params);
		$output = "<div {$this->render_attributes( '_root' )}>";
		$output .= do_shortcode('[latepoint_book_form ' . $params . ']');
		$output .= '</div>';
		echo $output;
	}
}