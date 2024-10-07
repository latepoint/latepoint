<?php

class Latepoint_Elementor_Widget_Customer_Login extends \Elementor\Widget_Base {

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
		return 'latepoint_customer_login';
	}

	/**
	 * Get widget title
	 */
	public function get_title(): string {
		return esc_html__( 'Customer Login', 'latepoint' );
	}

	/**
	 * Get widget icon
	 */
	public function get_icon(): string {
		return 'eicon-lock-user';
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
	protected function register_controls(): void {}


	/**
	 * Render widget output on the frontend
	 */
	protected function render(): void {
		//$settings = $this->get_settings_for_display();

		echo do_shortcode('[latepoint_customer_login]');

	}

}