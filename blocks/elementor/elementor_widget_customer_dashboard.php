<?php

class Latepoint_Elementor_Widget_Customer_Dashboard extends \Elementor\Widget_Base {

	protected $widget_data;
	/**
	 * Widget base constructor
	 */
	public function __construct( $data = [], $args = null ) {
		$this->widget_data = $args;
		parent::__construct( $data, $args );
	}


	/**
	 * Get widget name
	 */
	public function get_name(): string {
		return 'latepoint_customer_dashboard';
	}

	/**
	 * Get widget title
	 */
	public function get_title(): string {
		return esc_html__( 'Customer Dashboard', 'latepoint' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon(): string {
		return 'eicon-archive';
	}

	/**
	 * Get widget categories
	 */
	public function get_categories(): array {
		return [ OsElementorHelper::$category ];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls(): void {

		# Booking Form Settings Section
		$this->start_controls_section(
			'content_section_booking_form_settings',
			[
				'label' => esc_html__( 'Content', 'latepoint' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'hide_new_appointment_ui',
			[
				'label'        => esc_html__( 'Hide New Appointment UI', 'latepoint' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'latepoint' ),
				'label_off'    => esc_html__( 'No', 'latepoint' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$allowed_params = [
			'hide_new_appointment_ui'
		];

		$params = OsBlockHelper::attributes_to_data_params($settings, $allowed_params);
		echo do_shortcode('[latepoint_customer_dashboard ' . $params . ']');
	}

}