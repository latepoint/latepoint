<?php

class Latepoint_Elementor_Widget_Calendar extends \Elementor\Widget_Base {

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
		return 'latepoint_calendar';
	}

	/**
	 * Get widget title
	 */
	public function get_title(): string {
		return esc_html__( 'Latepoint Calendar', 'latepoint' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon(): string {
		return 'eicon-calendar';
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

		# Form Settings Section
		$this->start_controls_section(
			'content_section_form_settings',
			[
				'label' => esc_html__( 'Latepoint Calendar Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'date',
			[
				'label' => esc_html__( 'Date', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => ['enableTime' => false],
				'label_block' => false
			]
		);

		$this->add_control(
			'show_agents',
			[
				'label' => esc_html__( 'Show Agents', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'multiple' => true,
				'options' => $this->widget_data['agents'],
			]
		);

		$this->add_control(
			'show_services',
			[
				'label' => esc_html__('Show Services', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['services'],
				'multiple' => true
			]
		);


		$this->add_control(
			'show_locations',
			[
				'label' => esc_html__( 'Show Locations', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'multiple' => true,
				'options' => $this->widget_data['locations'],
			]
		);

		$this->add_control(
			'view',
			[
				'label' => esc_html__( 'View', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'month',
				'options' => [
					'month' => esc_html__( 'Month', 'latepoint' ),
					'week' => esc_html__( 'Week', 'latepoint' ),
				],
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
			'date',
			'show_services',
			'show_agents',
			'show_locations',
			'view'
		];

		$params = OsBlockHelper::attributes_to_data_params($settings, $allowed_params);
		echo do_shortcode('[latepoint_calendar ' . $params . ']');
	}

}