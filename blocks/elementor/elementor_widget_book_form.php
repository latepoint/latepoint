<?php

class Latepoint_Elementor_Widget_Book_Form extends \Elementor\Widget_Base {

	protected $widget_data;
	/**
	 * Widget base constructor
	 */
	public function __construct( $data = [], $args = null ) {
		$this->widget_data = $args;
		parent::__construct( $data, $args );

		wp_register_script(
			'elementor_widget_book_form_script',
			LATEPOINT_PLUGIN_URL . 'blocks/assets/javascripts/elementor-widget-book-form.js',
			['jquery'],
			LATEPOINT_VERSION
		);
	}


	/**
	 * Get widget name
	 */
	public function get_name(): string {
		return 'latepoint_book_form';
	}

	/**
	 * Get widget title
	 */
	public function get_title(): string {
		return esc_html__( 'Booking Form', 'latepoint' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories
	 */
	public function get_categories(): array {
		return [ OsElementorHelper::$category ];
	}

	public function get_script_depends() {
		if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
			return ['elementor_widget_book_form_script'];
		}
		return [];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls(): void {

		# Booking Form Settings Section
		$this->start_controls_section(
			'content_section_booking_form_settings',
			[
				'label' => esc_html__( 'Booking Form Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'hide_summary',
			[
				'label' => esc_html__( 'Hide Summary Panel', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'latepoint' ),
				'label_off' => esc_html__( 'No', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'hide_side_panel',
			[
				'label' => esc_html__( 'Hide Side Panel', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'latepoint' ),
				'label_off' => esc_html__( 'No', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);
		$this->end_controls_section();


		# Step Settings Section
		$this->start_controls_section(
			'content_section_step_settings',
			[
				'label' => esc_html__( 'Step Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'selected_agent',
			[
				'label' => esc_html__('Preselected Agent', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->widget_data['selected_agents_options'],
			]
		);

		$this->add_control(
			'selected_service',
			[
				'label' => esc_html__('Preselected Service', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->widget_data['selected_services_options'],
			]
		);
		$this->add_control(
			'selected_service_category',
			[
				'label' => esc_html__('Preselected Service Category', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->widget_data['selected_service_categories_options'],
			]
		);

		$this->add_control(
			'selected_location',
			[
				'label' => esc_html__('Preselected Location', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->widget_data['selected_locations_options'],
			]
		);

		$this->add_control(
			'selected_start_date',
			[
				'label' => esc_html__( 'Preselected Booking Start Date', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => ['enableTime' => false],
				'label_block' => false
			]
		);

		$this->add_control(
			'selected_start_time',
			[
				'label' => esc_html__( 'Preselected Booking Start Time', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'time',
				'placeholder' => esc_html__( 'HH:MM', 'latepoint' ),
				'description' => esc_html__( 'Choose a time (format HH:MM)', 'latepoint' ),
			]
		);

		$this->add_control(
			'selected_duration',
			[
				'label'   => esc_html__( 'Preselected Duration', 'latepoint' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => 0,
				'description' => esc_html__( 'Minutes', 'latepoint' ),
			]
		);

		$this->add_control(
			'selected_total_attendees',
			[
				'label'   => esc_html__( 'Preselected Total Attendees', 'latepoint' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => "",
			]
		);
		$this->end_controls_section();


		# Other Settings Section
		$this->start_controls_section(
			'content_section_other_settings',
			[
				'label' => esc_html__( 'Other Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_id',
			[
				'label'   => esc_html__( 'Source ID', 'latepoint' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => "",
			]
		);

		$this->add_control(
			'calendar_start_date',
			[
				'label' => esc_html__( 'Calendar Start Date', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'picker_options' => ['enableTime' => false],
				'label_block' => false
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
			'show_service_categories',
			[
				'label' => esc_html__( 'Show Service Categories', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'multiple' => true,
				'options' => $this->widget_data['service_categories'],
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
			'show_locations',
			[
				'label' => esc_html__( 'Show Locations', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'multiple' => true,
				'options' => $this->widget_data['locations'],
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

		$params = OsBlockHelper::attributes_to_data_params($settings, $allowed_params);
		echo do_shortcode('[latepoint_book_form ' . $params . ']');
	}

}