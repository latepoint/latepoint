<?php

class OsBlockHelper {
	public static function register_blocks() {
		self::register_latepoint_category();
		self::register_block_book_button();
		self::register_block_book_form();
		self::register_block_list_of_resources();
		self::register_block_calendar();
		self::register_block_customer_dashboard();
		self::register_block_customer_login();
	}

	public static function localized_vars_for_blocks() : array {

		$has_to_pick = [ 'label' => __('Customer will pick', 'latepoint'), 'value' => '' ];
		$localized_block_vars = [];

		// AGENTS
		$localized_block_vars['agents'] = [];
		$localized_block_vars['selected_agents_options'][] = $has_to_pick;
		$localized_block_vars['selected_agents_options'][] = [ 'label' => __('Any Available', 'latepoint'), 'value' => LATEPOINT_ANY_AGENT ];
		$agents = new OsAgentModel();
		$agents = $agents->get_results_as_models();
		if($agents){
			foreach($agents as $agent){
				$localized_block_vars['selected_agents_options'][] = ['label' => $agent->full_name, 'value' => $agent->id];
				$localized_block_vars['agents'][] = [
					'name' => $agent->full_name,
					'id' => $agent->id,
					'title' => $agent->title,
					'short_description' => $agent->short_description,
					'avatar_url' => empty($agent->avatar_image_id) ? '' : $agent->get_avatar_url()];
			}
		}

		// SERVICES
		$localized_block_vars['services'] = [];
		$localized_block_vars['selected_services_options'][] = $has_to_pick;
		$services = new OsServiceModel();
		$services = $services->get_results_as_models();
		if($services){
			foreach($services as $service){
				$localized_block_vars['selected_services_options'][] = ['label' => $service->name, 'value' => $service->id];
				$localized_block_vars['services'][] = [
					'name' => $service->name,
					'id' => $service->id,
					'image_url' => empty($service->description_image_id) ? '' : $service->get_description_image_url(),
					'description' => $service->short_description,
					'category_id' => $service->category_id
				];
			}
		}


		// SERVICE CATEGORIES
		$localized_block_vars['service_categories'] = [];
		$localized_block_vars['selected_service_categories_options'][] = [ 'label' => __('Show All', 'latepoint'), 'value' => '' ];
		$service_categories = new OsServiceCategoryModel();
		$service_categories = $service_categories->get_results_as_models();
		if($service_categories){
			foreach($service_categories as $service_category){
				$localized_block_vars['service_categories'][] = [
					'name' => $service_category->name,
					'id' => $service_category->id
				];
				$localized_block_vars['selected_service_categories_options'][] = ['label' => $service_category->name, 'value' => $service_category->id];
			}
		}


		// LOCATIONS
		$localized_block_vars['locations'] = [];
		$localized_block_vars['selected_locations_options'][] = $has_to_pick;
		$localized_block_vars['selected_locations_options'][] = [ 'label' => __('Any Available', 'latepoint'), 'value' => LATEPOINT_ANY_LOCATION ];
		$locations = new OsLocationModel();
		$locations = $locations->get_results_as_models();
		if($locations){
			foreach($locations as $location){
				$localized_block_vars['selected_locations_options'][] = ['label' => $location->name, 'value' => $location->id];
				$localized_block_vars['locations'][] = [
					'name' => $location->name,
					'id' => $location->id,
					'category_id' => $location->category_id];
			}
		}
		return $localized_block_vars;
	}

	public static function register_latepoint_category() {
		add_filter('block_categories_all', function ($categories) {
			// Adding a new category.
			$categories[] = [
				'slug' => 'latepoint',
				'title' => 'LatePoint',
			];
			return $categories;
		});
	}

	public static function register_block_book_button() {
		register_block_type(LATEPOINT_ABSPATH . 'blocks/build/book-button/block.json',
			[
				'render_callback' => 'OsBlockHelper::render_book_button',
				'editor_script_handles' => ['latepoint-block-book-button']
			]);
	}

	public static function register_block_book_form() {
		register_block_type(LATEPOINT_ABSPATH . 'blocks/build/book-form/block.json',
			[
				'render_callback' => 'OsBlockHelper::render_book_form',
				'editor_script_handles' => ['latepoint-block-book-form']
			]);
	}

	public static function register_block_list_of_resources() {
		register_block_type(LATEPOINT_ABSPATH . 'blocks/build/list-of-resources/block.json',
			[
				'render_callback' => 'OsBlockHelper::render_list_of_resources',
				'editor_script_handles' => ['latepoint-block-list-of-resources']
			]);
	}


	public static function register_block_calendar(): void {
		register_block_type( LATEPOINT_ABSPATH . 'blocks/build/calendar/block.json',
			[
				'render_callback'       => 'OsBlockHelper::render_calendar',
				'editor_script_handles' => [ 'latepoint-block-calendar' ]
			] );
	}

	public static function register_block_customer_dashboard(): void {
		register_block_type( LATEPOINT_ABSPATH . 'blocks/build/customer-dashboard/block.json',
			[
				'render_callback'       => 'OsBlockHelper::render_customer_dashboard',
				'editor_script_handles' => [ 'latepoint-block-customer-dashboard' ]
			] );
	}

	public static function register_block_customer_login(): void {
		register_block_type( LATEPOINT_ABSPATH . 'blocks/build/customer-login/block.json',
			[
				'render_callback'       => 'OsBlockHelper::render_customer_login',
				'editor_script_handles' => [ 'latepoint-block-customer-login' ]
			] );
	}


	public static function render_book_button($attributes, $content) {
		return do_shortcode('[latepoint_book_button ' . self::attributes_to_data_params($attributes) . ']');
	}

	public static function render_book_form($attributes, $content) {
		return do_shortcode('[latepoint_book_form ' . self::attributes_to_data_params($attributes) . ']');
	}

	public static function add_block_styles_to_page() {
		if (is_page() && $styles = get_option('latepoint_blocks_styles_' . get_the_ID())) {
			wp_add_inline_style('latepoint-main-front', $styles);
		}
	}

	public static function render_list_of_resources($attributes, $content) {
		return do_shortcode('[latepoint_resources ' . self::attributes_to_data_params($attributes) . ']');
	}

	public static function render_calendar($attributes, $content) {
		return do_shortcode('[latepoint_calendar ' . self::attributes_to_data_params($attributes) . ']');
	}

	public static function render_customer_dashboard($attributes, $content) {
		return do_shortcode('[latepoint_customer_dashboard ' . self::attributes_to_data_params($attributes) . ']');
	}

	public static function render_customer_login($attributes, $content) {
		return do_shortcode('[latepoint_customer_login ' . self::attributes_to_data_params($attributes) . ']');
	}

	/**
	 * Prepare data for shortcode
	 * @param array $settings
	 * @param array $allowed_params
	 *
	 * @return string
	 */
	public static function attributes_to_data_params( array $settings, array $allowed_params = [] ): string {
		$data_html = '';
		foreach ( $settings as $key => $value ) {
			if(empty($value)) continue;

			if ( !empty($allowed_params) && ! in_array( $key, $allowed_params ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}
			if (is_bool($value)) {
				$value = $value ? 'yes' : 'no';
			}

			$data_html .= $key . '="' . esc_attr( $value ) . '" ';
		}

		return $data_html;
	}

	/**
	 * When save page - save gutenberg blocks styles to options
	 * @param $page_id
	 * @return void
	 */
	public static function save_blocks_styles( $page_id ):void {
		if (wp_is_post_revision($page_id)) return;

		$styles = '';

		if ( has_block('latepoint/list-of-resources', $page_id) ||
			has_block('latepoint/book-button', $page_id)
		) {
			$post_content = get_post_field( 'post_content', $page_id );
			$blocks = parse_blocks($post_content);

			foreach ($blocks as $block) {
				switch ($block['blockName']) {
				    case 'latepoint/list-of-resources':
						$styles .= self::generate_styles_for_list_of_resources($block['attrs']);
				    break;
					case 'latepoint/book-button':
						$styles .= self::generate_styles_for_book_button($block['attrs']);
					break;
				}
			}
		}

		self::save_blocks_styles_in_options($page_id, $styles);
	}

	/**
	 * Save gutenberg blocks styles in wp options. if styles is empty - delete option
	 * @param int $page_id
	 * @param string $styles
	 *
	 * @return void
	 */
	private static function save_blocks_styles_in_options(int $page_id, string $styles = "" ):void {
		$option_name = 'latepoint_blocks_styles_' . $page_id;
		if (trim($styles) == '') {
			if(get_option($option_name)) delete_option($option_name);
		} else {
			update_option($option_name, $styles);
		}
	}

	/**
	 * Styles for gutenberg block "Book Button"
	 * @param $attrs
	 *
	 * @return string
	 */
	public static function generate_styles_for_book_button($attrs): string {
		$styles = [];
		if (!empty($attrs['is_inherit'])) return "";

		$prefix = ".latepoint-book-button.latepoint-book-button-". $attrs['id'];

		$styles[$prefix . "," . $prefix . ":focus"] = [
			'background-color' => $attrs['bg_color'] ?? '',
			'color' => $attrs['text_color'] ?? '',
			'font-size' => $attrs['font_size'] ?? '',
			'font-weight' => $attrs['font_weight'] ?? '',
			'text-transform' => $attrs['text_transform'] ?? '',
			'font-family' => $attrs['font_family'] ?? '',
			'line-height' => $attrs['line_height'] ?? '',
			'letter-spacing' => $attrs['letter_spacing'] ?? '',
			'border-color' => $attrs['border_color'] ?? '',
			'border-width' => $attrs['border_width'] ?? '',
			'border-style' => $attrs['border_style'] ?? '',
			'border-radius' => $attrs['border_radius'] ?? '',
			'padding' => $attrs['padding'] ?? '',
		];
		$styles[$prefix . ":hover"] = [
			'background-color' => $attrs['bg_color_hover'] ?? "",
			'color' => $attrs['text_color_hover'] ?? '',
			'border-color' => $attrs['border_color_hover'] ?? '',
		];
		return self::generate_css($styles);
	}

	/**
	 * Styles for gutenberg block "List of Resources"
	 *
	 * @param array $attrs
	 * @return string
	 */
	public static function generate_styles_for_list_of_resources(array $attrs): string {
		$styles = [];
		if (!empty($attrs['is_inherit'])) return "";

		$card_prefix = ".latepoint-resources-items-w .resource-item.resource-item-". $attrs['id'];
		$styles[ $card_prefix . ' .latepoint-book-button' ] = [
			'background-color' => $attrs['button_bg_color'] ?? "",
			'color'            => $attrs['button_text_color'] ?? "",
			'font-size'        => $attrs['button_font_size'] ?? "",
			'font-weight'      => $attrs['font_weight'] ?? "",
			'text-transform'   => $attrs['text_transform'] ?? "",
			'font-family'      => $attrs['font_family'] ?? "",
			'line-height'      => $attrs['line_height'] ?? "",
			'letter-spacing'   => $attrs['letter_spacing'] ?? "",
			'border-radius'    => $attrs['button_border_radius'] ?? "",
			'border-color'     => $attrs['border_color'] ?? "",
			'border-style'     => $attrs['border_style'] ?? "",
			'border-width'     => $attrs['border_width'] ?? "",
			'padding'          => $attrs['padding'] ?? ""
		];
		$styles[ $card_prefix . ' .latepoint-book-button:hover' ] = [
			'background-color' => $attrs['bg_color_hover'] ?? "",
			'border-color'     => $attrs['border_color_hover'] ?? "",
			'color'            => $attrs['text_color_hover'] ?? "",
		];
		$styles[ $card_prefix ] = [
			'padding'          => $attrs['card_padding'] ?? "",
			'box-shadow'       => $attrs['card_box_shadow'] ?? "",
			'border-style'     => $attrs['card_border_style'] ?? "",
			'border-radius'    => $attrs['card_border_radius'] ?? "",
			'border-width'     => $attrs['card_border_width'] ?? "",
			'border-color'     => $attrs['card_border_color'] ?? "",
			'color'       => $attrs['card_text_color'] ?? "",
			'background-color' => $attrs['card_bg_color'] ?? "",
		];
		$styles[ $card_prefix . ":hover" ] = [
			'border-color'     => $attrs['card_border_color_hover'] ?? "",
			'background-color' => $attrs['card_bg_color_hover'] ?? "",
			'color'            => $attrs['card_text_color_hover'] ?? "",
		];
		$styles[ $card_prefix . ":hover .ri-name h3" ] = [
			'color' => $attrs['card_text_color_hover'] ?? ""
		];
		$styles[ $card_prefix . " .ri-name h3" ] = [
			'color' => $attrs['card_text_color'] ?? ""
		];
		return self::generate_css($styles);
	}

	/**
	 * Convert array with styles to string
	 *
	 * @param array $selectors
	 * @return string
	 */
	public static function generate_css(array $selectors): string {
		$styling_css = '';

		foreach ( $selectors as $selector => $options ) {

			$css = '';

			foreach ( $options as $option_name => $option_val ) {
				if ( empty( $option_val ) ) {
					continue;
				}
				$css .= $option_name . ': ' . $option_val . ';';
			}
			if ( ! empty( $css ) ) {
				$styling_css     .= $selector . '{';
				$styling_css .= $css . '}';
			}
		}

		return $styling_css;
	}

}