<?php

class OsElementorHelper {

	private static ?OsElementorHelper $_instance = null;
	public static string $category = "latepoint_builder";

	private string $min_elementor_version = '3.5.0';
	private string $min_php_version = '7.0';

	private array $widgets = [
		'book_button',
		'list_of_resources',
		'customer_login',
		'customer_dashboard',
		'calendar',
		'book_form',
	];

	/**
	 * Instance
	 * Ensures only one instance of the class is loaded or can be loaded.
	 */
	public static function init(): OsElementorHelper {
		if ( is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public function __construct() {
		if ( $this->check_requirements() ) {
			add_action( 'elementor/elements/categories_registered', [ $this, 'register_widgets_category' ] );
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		}
	}

	/**
	 * Check requirements for php and elementor
	 * @return bool
	 */
	private function check_requirements(): bool {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		if ( ! $this->check_php_version() ) {
			add_action( 'admin_notices', [ $this, 'php_version_error' ] );
			return false;
		}

		if ( ! $this->check_elementor_version() ) {
			add_action( 'admin_notices', [ $this, 'elementor_version_error' ] );
			return false;
		}

		return true;
	}

	private function check_php_version(): bool {
		return version_compare( PHP_VERSION, $this->min_php_version, '>=' );
	}

	private function check_elementor_version(): bool {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return false;
		}

		return version_compare( ELEMENTOR_VERSION, $this->min_elementor_version, '>=' );
	}

	private function php_version_error(): void {
		$message = esc_html__( 'Theme requires PHP version', 'latepoint' ) . ' <strong>' . $this->min_php_version . '</strong> ' . esc_html__( 'or greater.', 'latepoint' );
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	private function elementor_version_error(): void {
		$message = esc_html__( 'Theme requires Elementor version', 'latepoint' ) . ' <strong>' . $this->min_elementor_version . '</strong> ' . esc_html__( 'or greater.', 'latepoint' );
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}


	/**
	 * @return array
	 */
	private function get_data_for_blocks(): array {
		$result = [];
		$localized_vars = OsBlockHelper::localized_vars_for_blocks();

		$result['selected_agents_options'] = $this->prepare_data_for_select($localized_vars['selected_agents_options']);
		$result['selected_services_options'] = $this->prepare_data_for_select($localized_vars['selected_services_options']);
		$result['selected_service_categories_options'] = $this->prepare_data_for_select($localized_vars['selected_service_categories_options']);
		$result['selected_locations_options'] = $this->prepare_data_for_select($localized_vars['selected_locations_options']);

		$result['services'] = array_column($localized_vars['services'], 'name', 'id');
		$result['agents'] = array_column($localized_vars['agents'], 'name', 'id');;
		$result['locations'] = array_column($localized_vars['locations'], 'name', 'id');
		$result['location_categories'] = OsLocationHelper::get_location_categories();
		$result['service_categories'] = array_column($localized_vars['service_categories'], 'name', 'id');
		$result['bundles'] = array_column($localized_vars['bundles'], 'name', 'id');
		return $result;
	}

	private function prepare_data_for_select( array $options ): array {
		return array_column($options, 'label', 'value');
	}

	/**
	 * Init Widgets - Include widgets files and register them
	 */
	public function register_widgets(): void {

		$data_for_blocks = $this->get_data_for_blocks();


		foreach ( $this->widgets as $widget ) {
			$path = LATEPOINT_ABSPATH . 'blocks/elementor/elementor_widget_' . $widget . '.php';
			if ( file_exists( $path ) ) {
				include_once( $path );
				# class name should be in format: Latepoint_Elementor_Widget_Book_Button
				$class_name = 'Latepoint_Elementor_Widget_' . str_replace( [ '-', ' ' ], [ '_', '_' ], ucfirst( $widget ) );
				if ( class_exists( $class_name ) ) {
					\Elementor\Plugin::instance()->widgets_manager->register( new $class_name([], $data_for_blocks) );
				}
			}
		}
	}


	/**
	 * Register Widgets Category
	 *
	 * @param $elements_manager
	 *
	 * @return void
	 */
	public function register_widgets_category( $elements_manager ): void {
		$elements_manager->add_category(
			self::$category, [
				'title' => __( 'Latepoint', 'latepoint' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

}