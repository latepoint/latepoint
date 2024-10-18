<?php

class Latepoint_Elementor_Widget_List_Of_Resources extends \Elementor\Widget_Base {

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
		return 'latepoint_list_of_resources';
	}

	/**
	 * Get widget title
	 */
	public function get_title(): string {
		return esc_html__( 'List Of Resources', 'latepoint' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon(): string {
		return 'eicon-post-list';
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
				'label' => esc_html__( 'Booking Form Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'button_caption',
			[
				'label' => esc_html__( 'Button Caption', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Book Now', 'latepoint' ),
			]
		);

		$this->add_control(
			'hide_summary',
			[
				'label' => esc_html__( 'Hide Summary Panel', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'latepoint' ),
				'label_off' => esc_html__( 'Hide', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'hide_side_panel',
			[
				'label' => esc_html__( 'Hide Side Panel', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'latepoint' ),
				'label_off' => esc_html__( 'Hide', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);
		$this->end_controls_section();


		# Settings Section
		$this->start_controls_section(
			'content_section_form_settings',
			[
				'label' => esc_html__( 'Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'items',
			[
				'label' => esc_html__('Resource Type', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'services',
				'options' => [
					'services' => esc_html__('Services', 'latepoint'),
					'agents' => esc_html__('Agents', 'latepoint'),
					'locations' => esc_html__('Locations', 'latepoint'),
				]
			]
		);

		$this->add_control(
			'columns',
			[
				'label' => esc_html__('Number of columns', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => [
					'1' => esc_html__('One', 'latepoint'),
					'2' => esc_html__('Two', 'latepoint'),
					'3' => esc_html__('Three', 'latepoint'),
					'4' => esc_html__('Four', 'latepoint'),
					'5' => esc_html__('Five', 'latepoint'),
				]
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
				'default' => "",
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


		# Step Settings Section
		$this->start_controls_section(
			'content_section_items_settings',
			[
				'label' => esc_html__( 'Items Settings', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => esc_html__( 'Max Number of Items Shown', 'latepoint' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => "",
			]
		);

		$this->add_control(
			'item_ids_services',
			[
				'label' => esc_html__('Show Selected Services', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['services'],
				'multiple' => true,
				'condition' => [
					'items' => 'services',
				],
			]
		);

		$this->add_control(
			'item_ids_agents',
			[
				'label' => esc_html__('Show Selected Agents', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['agents'],
				'multiple' => true,
				'condition' => [
					'items' => 'agents',
				],
			]
		);

		$this->add_control(
			'item_ids_locations',
			[
				'label' => esc_html__('Show Selected Locations', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['locations'],
				'multiple' => true,
				'condition' => [
					'items' => 'locations',
				],
			]
		);

		$this->add_control(
			'services_categories_ids',
			[
				'label' => esc_html__('Show Services Categories', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['service_categories'],
				'multiple' => true,
				'condition' => [
					'items' => ['services'],
				],
			]
		);

		$this->add_control(
			'location_categories_ids',
			[
				'label' => esc_html__('Show Location Categories', 'latepoint'),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'options' => $this->widget_data['location_categories'],
				'multiple' => true,
				'condition' => [
					'items' => [ 'locations' ],
				],
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
				'multiple' => true,
				'condition' => [
					'items!' => 'services',
				],
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
				'condition' => [
					'items!' => 'services',
				],
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
				'condition' => [
					'items!' => 'agents',
				],
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
				'condition' => [
					'items!' => 'locations',
				],
			]
		);
		$this->end_controls_section();


		$this->start_controls_section(
			'content_section_button_appearance',
			[
				'label' => esc_html__( 'Button Appearance', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'is_inherit_btn',
			[
				'label' => esc_html__( 'Inherit From Theme', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'latepoint' ),
				'label_off' => esc_html__( 'No', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);
		# Divider
		$this->add_control( 'hr6', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_btn!' => 'yes'],
		]);

		# Typography
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .latepoint-book-button',
				'condition' => ['is_inherit_btn!' => 'yes'],
			]
		);

		#Text Shadow
		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .latepoint-book-button',
				'condition' => ['is_inherit_btn!' => 'yes'],
			]
		);


		#Tabs

		$this->start_controls_tabs('style_tabs', [
			'condition' => ['is_inherit_btn!' => 'yes']
		]);

		$this->start_controls_tab( 'style_normal_tab', [
				'label' => esc_html__( 'Normal', 'latepoint' ),
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Background', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .latepoint-book-button' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .latepoint-book-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();


		$this->start_controls_tab( 'style_hover_tab', [
				'label' => esc_html__( 'Hover', 'latepoint' ),
			]
		);
		$this->add_control(
			'bg_color_hover',
			[
				'label' => esc_html__( 'Background', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .latepoint-book-button:hover' => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'text_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .latepoint-book-button:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		# Divider
		$this->add_control( 'hr2', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_btn!' => 'yes'],
		]);

		# Border Controls
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'label' => esc_html__( 'Button Border', 'latepoint' ),
				'selector' => '{{WRAPPER}} .latepoint-book-button',
				'condition' => ['is_inherit_btn!' => 'yes']
			]
		);

		# Border Radius
		$this->add_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'latepoint' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .latepoint-book-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => ['is_inherit_btn!' => 'yes'],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .latepoint-book-button',
				'condition' => ['is_inherit_btn!' => 'yes'],
			]
		);


		# Divider
		$this->add_control( 'hr1', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_btn!' => 'yes'],
		]);


		# Padding Controls
		$this->add_control(
			'padding',
			[
				'label'      => esc_html__( 'Padding', 'latepoint' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .latepoint-book-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => ['is_inherit_btn!' => 'yes'],
			]
		);

		$this->end_controls_section();






		#card settings
		$this->start_controls_section(
			'content_section_box_appearance',
			[
				'label' => esc_html__( 'Box Appearance', 'latepoint' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_control(
			'is_inherit_card',
			[
				'label' => esc_html__( 'Inherit From Theme', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'latepoint' ),
				'label_off' => esc_html__( 'No', 'latepoint' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control( 'hr5', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_card!' => 'yes'],
		]);

		# Typography
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'box_typography',
				'selector' => '{{WRAPPER}} .resource-item h3',
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);

		#Text Shadow
		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'card_shadow',
				'selector' => '{{WRAPPER}} .resource-item',
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);


		#Tabs

		$this->start_controls_tabs('card_style_tabs', [
			'condition' => ['is_inherit_card!' => 'yes'],
		]);

		$this->start_controls_tab( 'card_style_normal_tab', [
				'label' => esc_html__( 'Normal', 'latepoint' ),
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label' => esc_html__( 'Background', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .resource-item' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'card_text_color',
			[
				'label' => esc_html__( 'Text Color', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .resource-item h3' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();


		$this->start_controls_tab( 'card_style_hover_tab', [
				'label' => esc_html__( 'Hover', 'latepoint' ),
			]
		);
		$this->add_control(
			'card_bg_color_hover',
			[
				'label' => esc_html__( 'Background', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .resource-item:hover' => 'background-color: {{VALUE}}',
				],
			]
		);
		$this->add_control(
			'card_text_color_hover',
			[
				'label' => esc_html__( 'Text Color', 'latepoint' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .resource-item:hover h3' => 'color: {{VALUE}}',
				],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		# Divider
		$this->add_control( 'hr3', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_card!' => 'yes'],
		]);

		# Border Controls
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'label' => esc_html__( 'Card Border', 'latepoint' ),
				'selector' => '{{WRAPPER}} .resource-item',
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);

		# Border Radius
		$this->add_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'latepoint' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .resource-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .resource-item',
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);


		# Divider
		$this->add_control( 'hr4', [
			'type' => \Elementor\Controls_Manager::DIVIDER,
			'condition' => ['is_inherit_card!' => 'yes'],
		]);


		# Padding Controls
		$this->add_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Box Padding', 'latepoint' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .resource-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => ['is_inherit_card!' => 'yes'],
			]
		);

		$this->end_controls_section();

	}


	/**
	 * Render widget output on the frontend
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$settings['item_ids'] = $settings['item_ids_services'] ?? $settings['item_ids_locations'] ?? $settings['item_ids_agents'] ?? [];
		$settings['group_ids'] = $settings['services_categories_ids'] ?? $settings['location_categories_ids'] ?? [];

		$allowed_params = [
			'items',
			'columns',
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
			'limit',
			'item_ids',
			'group_ids',
			'source_id',
			'calendar_start_date',
			'show_services',
			'show_service_categories',
			'show_agents',
			'show_locations',
			'button_caption',
			'btn_wrapper_classes',
			'btn_classes'
		];
		$settings['btn_wrapper_classes'] = 'elementor-button-wrapper';
		$settings['btn_classes'] = 'elementor-button elementor-button-link elementor-size-sm';

		$params = OsBlockHelper::attributes_to_data_params($settings, $allowed_params); ?>
		<?php echo do_shortcode('[latepoint_resources ' . $params . ']'); ?>
		<?php
	}

}