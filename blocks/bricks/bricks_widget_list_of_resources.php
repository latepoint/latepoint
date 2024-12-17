<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Latepoint_Bricks_Widget_List_Of_Resources extends \Bricks\Element {

	public $category = 'latepoint';
	public $name = 'list_of_resources';
	public $icon = 'ti-layout-grid2';


	public function get_label(): string {
		return esc_html__( 'List Of Resources', 'latepoint' );
	}

	public function set_control_groups() {
		$this->control_groups['general'] = array(
			'title' => esc_html__( 'Booking Form Settings', 'latepoint' ),
			'tab'   => 'content',
		);

		$this->control_groups['main_settings'] = array(
			'title' => esc_html__( 'Settings', 'latepoint' ),
			'tab'   => 'content',
		);

		$this->control_groups['step_settings'] = array(
			'title' => esc_html__( 'Step Settings', 'latepoint' ),
			'tab'   => 'content',
		);

		$this->control_groups['items_settings'] = array(
			'title' => esc_html__( 'Items Settings', 'latepoint' ),
			'tab'   => 'content',
		);

		$this->control_groups['other_settings'] = array(
			'title' => esc_html__( 'Other Settings', 'latepoint' ),
			'tab'   => 'content',
		);

		$this->control_groups['card_styling'] = array(
			'title' => esc_html__( 'Card', 'latepoint' ),
			'tab'   => 'style',
		);

		$this->control_groups['button_styling'] = array(
			'title' => esc_html__( 'Button', 'latepoint' ),
			'tab'   => 'style',
		);

		unset( $this->control_groups['_typography'] );
		unset( $this->control_groups['_transform'] );
	}

	// Set builder controls
	public function set_controls() {
		$this->controls['_width']['default']   = '100%';

		$this->controls['button_caption'] = array(
			'label'       => esc_html__( 'Button Caption', 'latepoint' ),
			'tab'         => 'content',
			'group'       => 'general',
			'type'        => 'text',
			'default'     => esc_html__( 'Book Now', 'latepoint' ),
		);
		$this->controls['hide_summary'] = array(
			'tab'         => 'content',
			'group'       => 'general',
			'label'       => esc_html__( 'Hide Summary', 'latepoint' ),
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


		#main settings group

		$this->controls['items'] = [
			'tab'        => 'content',
			'group'      => 'main_settings',
			'label'      => esc_html__( 'Resource Type', 'latepoint' ),
			'type'       => 'select',
			'options'    => [
				'services'  => esc_html__( 'Services', 'latepoint' ),
				'agents'    => esc_html__( 'Agents', 'latepoint' ),
				'locations' => esc_html__( 'Locations', 'latepoint' ),
				'bundles'   => esc_html__( 'Bundles', 'latepoint' ),
			],
			'default' => 'services'
		];
		$this->controls['columns'] = [
			'tab'     => 'content',
			'group'   => 'main_settings',
			'label'   => esc_html__( 'Number of columns', 'latepoint' ),
			'type'    => 'select',
			'options' => [
				'1' => esc_html__( 'One', 'latepoint' ),
				'2' => esc_html__( 'Two', 'latepoint' ),
				'3' => esc_html__( 'Three', 'latepoint' ),
				'4' => esc_html__( 'Four', 'latepoint' ),
				'5' => esc_html__( 'Five', 'latepoint' ),
			],
			'default' => '4'
		];


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

		$this->controls['selected_location'] = [
			'tab'         => 'content',
			'group'       => 'step_settings',
			'label'       => esc_html__( 'Preselected Location', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('selected_locations'),
			'placeholder' => esc_html__( 'Preselected Service Category', 'latepoint' ),
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


		# items settings group
		$this->controls['limit'] = [
			'tab'    => 'content',
			'group'  => 'items_settings',
			'label'  => esc_html__( 'Max Number of Items Shown', 'latepoint' ),
			'type'   => 'number',
			'min'    => 0,
			'inline' => true,
		];

		$this->controls['item_ids_services'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Selected Services', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'services' ),
			'placeholder' => esc_html__( 'Show Selected Services', 'latepoint' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'services' ),
		];
		$this->controls['services_categories_ids'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Services Categories', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'service_categories' ),
			'placeholder' => esc_html__( 'Show Services Categories', 'latepoint' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'services' ),
		];

		$this->controls['item_ids_agents'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Selected Agents', 'latepoint' ),
			'placeholder' => esc_html__( 'Show Selected Agents', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'agents' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'agents' ),
		];
		$this->controls['item_ids_locations'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Selected Locations', 'latepoint' ),
			'placeholder' => esc_html__( 'Show Selected Locations', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'locations' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'locations' ),
		];
		$this->controls['location_categories_ids'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Location Categories', 'latepoint' ),
			'placeholder' => esc_html__( 'Show Location Categories', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'location_categories' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'locations' ),
		];

		$this->controls['item_ids_bundles'] = [
			'tab'        => 'content',
			'group'      => 'items_settings',
			'label'      => esc_html__( 'Show Selected Bundles', 'latepoint' ),
			'type'       => 'select',
			'options'    => OsBricksHelper::get_data( 'bundles' ),
			'placeholder' => esc_html__( 'Show Selected Bundles', 'latepoint' ),
			'multiple'   => true,
			'searchable' => true,
			'clearable'  => true,
			'required'   => array( 'items', '=', 'bundles' ),
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
			'placeholder' => esc_html__( 'All Services', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('services'),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
			'required'   => array( 'items', '!=', ['services', 'bundles'] ),
		];

		$this->controls['show_service_categories'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Service Categories', 'latepoint' ),
			'placeholder' => esc_html__( 'All Service Categories', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('service_categories'),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
			'required'   => array( 'items', '!=', ['services', 'bundles'] ),
		];
		$this->controls['show_agents'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Agents', 'latepoint' ),
			'placeholder' => esc_html__( 'All Agents', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('agents'),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
			'required'   => array( 'items', '!=', 'agents' ),
		];

		$this->controls['show_locations'] = [
			'tab'         => 'content',
			'group'       => 'other_settings',
			'label'       => esc_html__( 'Show Locations', 'latepoint' ),
			'type'        => 'select',
			'options'     => OsBricksHelper::get_data('locations'),
			'multiple'    => true,
			'searchable'  => true,
			'clearable'   => true,
			'required'   => array( 'items', '!=', 'locations' ),
		];


		$this->controls['btn_font'] = [
			'tab'    => 'style',
			'group'  => 'button_styling',
			'label'  => esc_html__( 'Typography', 'latepoint' ),
			'type'   => 'typography',
			'css'    => [
				[
					'property' => 'typography',
					'selector' => '.latepoint-book-button',
				],
			],
			'exclude' => ['text-align', 'color'],
			'inline' => true,
		];

		$this->controls['button_full_width'] = array(
			'tab'     => 'style',
			'group'   => 'button_styling',
			'label'   => esc_html__( 'Full Width', 'latepoint' ),
			'type'    => 'checkbox',
			'inline'  => true,
			'default' => false,
			'css'   => array(
				array(
					'property' => 'display',
					'selector' => '.latepoint-book-button',
					'value'    => 'block',
				),
			),
		);

		$this->controls['bg_color_separator'] = array(
			'tab'    => 'style',
			'group'  => 'button_styling',
			'type'     => 'separator',
		);

		$this->controls['bg_color'] = array(
			'tab'      => 'style',
			'group'    => 'button_styling',
			'label'    => esc_html__( 'Background Color', 'latepoint' ),
			'type'     => 'color',
			'css'      => array(
				array(
					'property' => 'background-color',
					'selector' => '.latepoint-book-button',
				),
			),
		);

		$this->controls['text_color'] = array(
			'tab'      => 'style',
			'group'    => 'button_styling',
			'label'    => esc_html__( 'Text Color', 'latepoint' ),
			'type'     => 'color',
			'css'      => array(
				array(
					'property' => 'color',
					'selector' => '.latepoint-book-button',
				),
			),
		);

		$this->controls['border_separator'] = array(
			'tab'    => 'style',
			'group'  => 'button_styling',
			'type'     => 'separator',
		);

		$this->controls['btn_border'] = [
			'tab'      => 'style',
			'group'    => 'button_styling',
			'label' => esc_html__( 'Border', 'latepoint' ),
			'type' => 'border',
			'css' => [
				[
					'property' => 'border',
					'selector' => '.latepoint-book-button',
				],
			],
			'inline' => true,
			'small' => true,
		];

		$this->controls['btn_shadow'] = [
			'tab'      => 'style',
			'group'    => 'button_styling',
			'label' => esc_html__( 'Box Shadow', 'latepoint' ),
			'type' => 'box-shadow',
			'css' => [
				[
					'property' => 'box-shadow',
					'selector' => '.latepoint-book-button',
				],
			],
			'inline' => true,
			'small' => true,
		];

		$this->controls['bg_padding_separator'] = array(
			'tab'    => 'style',
			'group'  => 'button_styling',
			'type'     => 'separator',
		);

		$this->controls['btn_padding'] = [
			'tab'    => 'style',
			'group'  => 'button_styling',
			'label' => esc_html__( 'Padding', 'latepoint' ),
			'type' => 'dimensions',
			'css' => [
				[
					'property' => 'padding',
					'selector' => '.latepoint-book-button',
				]
			],
		];


		#card settings

		$this->controls['align'] = array(
			'tab'      => 'style',
			'group'    => 'card_styling',
			'label'   => esc_html__( 'Align', 'latepoint' ),
			'type'    => 'text-align',
			'inline'  => true,
			'exclude' => 'justify',
			'css'   => array(
				array(
					'property' => 'text-align',
					'selector' => '.resource-item',
				)
			)
		);

		$this->controls['card_bg_color'] = array(
			'tab'      => 'style',
			'group'    => 'card_styling',
			'label'    => esc_html__( 'Background Color', 'latepoint' ),
			'type'     => 'color',
			'css'      => array(
				array(
					'property' => 'background-color',
					'selector' => '.resource-item',
				),
			),
		);

		$this->controls['cbg_color_separator'] = array(
			'tab'    => 'style',
			'group'  => 'card_styling',
			'type'     => 'separator',
		);

		$this->controls['card_title'] = [
			'tab'    => 'style',
			'group'  => 'card_styling',
			'label'  => esc_html__( 'Title', 'latepoint' ),
			'type'   => 'typography',
			'exclude' => ['text-align'],
			'css'    => [
				[
					'property' => 'typography',
					'selector' => '.ri-name > h3, .ri-title',
				],
			],
			'inline' => true,
		];

		$this->controls['card_price'] = [
			'tab'    => 'style',
			'group'  => 'card_styling',
			'label'  => esc_html__( 'Price', 'latepoint' ),
			'type'   => 'typography',
			'exclude' => ['text-align'],
			'css'    => [
				[
					'property' => 'typography',
					'selector' => '.ri-price',
				],
			],
			'inline' => true,
		];

		$this->controls['card_descr'] = [
			'tab'    => 'style',
			'group'  => 'card_styling',
			'label'  => esc_html__( 'Description', 'latepoint' ),
			'type'   => 'typography',
			'exclude' => ['text-align'],
			'css'    => [
				[
					'property' => 'typography',
					'selector' => '.ri-description',
				],
			],
			'inline' => true,
		];

		$this->controls['cborder_separator'] = array(
			'tab'    => 'style',
			'group'  => 'card_styling',
			'type'     => 'separator',
		);

		$this->controls['card_border'] = [
			'tab'      => 'style',
			'group'    => 'card_styling',
			'label' => esc_html__( 'Border', 'latepoint' ),
			'type' => 'border',
			'css' => [
				[
					'property' => 'border',
					'selector' => '.resource-item',
				],
			],
			'inline' => true,
			'small' => true,
		];

		$this->controls['card_shadow'] = [
			'tab'    => 'style',
			'group'  => 'card_styling',
			'label'  => esc_html__( 'Box Shadow', 'latepoint' ),
			'type'   => 'box-shadow',
			'css'    => [
				[
					'property' => 'box-shadow',
					'selector' => '.resource-item',
				],
			],
			'inline' => true,
			'small'  => true,
		];

		$this->controls['card_padding_separator'] = array(
			'tab'    => 'style',
			'group'  => 'button_styling',
			'type'     => 'separator',
		);

		$this->controls['card_padding'] = [
			'tab'    => 'style',
			'group'  => 'card_styling',
			'label' => esc_html__( 'Padding', 'latepoint' ),
			'type' => 'dimensions',
			'css' => [
				[
					'property' => 'padding',
					'selector' => '.resource-item',
				]
			],
		];


	}


	// Render element HTML
	public function render() {

		$this->settings['item_ids'] = OsBlockHelper::get_ids_from_resources($this->settings['items'], $this->settings);
		$this->settings['group_ids'] = $this->settings['services_categories_ids'] ?? $this->settings['location_categories_ids'] ?? [];

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
		$this->settings['btn_wrapper_classes'] = 'bricks-button-wrapper';
		$this->settings['btn_classes'] = 'bricks-button bricks-background-primary';

		$params = OsBlockHelper::attributes_to_data_params($this->settings, $allowed_params);

		$output = "<div {$this->render_attributes( '_root' )}>";
		$output .= do_shortcode('[latepoint_resources ' . $params . ']');
		$output .= '</div>';
		echo $output;

	}
}