<?php

class OsBricksHelper {

	private static ?OsBricksHelper $_instance = null;
	private string $min_php_version = '7.4';
	private static array $data = [];

	private array $widgets = [
		'book_button',
		'list_of_resources',
		'customer_login',
		'customer_dashboard',
		'calendar',
		'book_form',
	];

	public static function init(): OsBricksHelper {
		if ( is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		if ($this->check_requirements()) {
			self::set_data();
			$this->register_widgets();
		}
	}


	private function check_requirements(): bool {
		if(!class_exists( '\Bricks\Elements' )){
			return false;
		}
		if ( ! $this->check_php_version() ) {
			add_action( 'admin_notices', [ $this, 'php_version_error' ] );
			return false;
		}
		return true;
	}

	private function check_php_version(): bool {
		return version_compare( PHP_VERSION, $this->min_php_version, '>=' );
	}

	private function php_version_error(): void {
		$message = esc_html__( 'Theme requires PHP version', 'latepoint' ) . ' <strong>' . $this->min_php_version . '</strong> ' . esc_html__( 'or greater.', 'latepoint' );
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	private function register_widgets() {
		foreach ( $this->widgets as $widget ) {
			$path = LATEPOINT_ABSPATH . 'blocks/bricks/bricks_widget_' . $widget . '.php';
			if ( file_exists( $path ) ) {
				\Bricks\Elements::register_element( $path );
			}
		}
	}

	public static function get_data( $key = false) {
		if (!$key) {
			return self::$data;
		}
		if (!empty(self::$data[$key])) {
			return self::$data[$key];
		}
		return  [];
	}

	private function set_data() {
		$localized_vars = OsBlockHelper::localized_vars_for_blocks();
		self::$data['agents'] = array_column($localized_vars['agents'], 'name', 'id');
		self::$data['services'] = array_column($localized_vars['services'], 'name', 'id');;
		self::$data['locations'] = array_column($localized_vars['locations'], 'name', 'id');
		self::$data['bundles'] = array_column($localized_vars['bundles'], 'name', 'id');
		self::$data['location_categories'] = OsLocationHelper::get_location_categories();
		self::$data['service_categories'] = array_column($localized_vars['service_categories'], 'name', 'id');
		self::$data['selected_agents'] = array_column($localized_vars['selected_agents_options'], 'label', 'value');
		self::$data['selected_services'] = array_column($localized_vars['selected_services_options'], 'label', 'value');
		self::$data['selected_service_categories'] = array_column($localized_vars['selected_service_categories_options'], 'label', 'value');
		self::$data['selected_locations'] = array_column($localized_vars['selected_locations_options'], 'label', 'value');
	}

}