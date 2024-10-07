<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class Step {
	public string $code;
	public string $label;
	public string $side_panel_heading;
	public string $side_panel_description;
	public string $main_panel_heading;
	public string $main_panel_content_before;
	public string $main_panel_content_after;
	public string $side_panel_custom_image_id;


	function __construct( $args = [] ) {
		$allowed_props = static::allowed_props();
		foreach ( $args as $key => $arg ) {
			if ( in_array( $key, $allowed_props ) ) {
				$this->$key = $arg;
			}
		}
	}

	public function get_image_url_for_side_panel() : string {
		if ( $this->is_using_custom_image_for_side_panel() ) {
			return \OsImageHelper::get_image_url_by_id( $this->side_panel_custom_image_id, 'thumbnail', '' );
		}else{
			return '';
		}
	}

	public function get_default_image_html_for_side_panel() {
		return \OsStepsHelper::get_default_side_panel_image_html_for_step_code( $this->code );
	}


	public function is_using_custom_image_for_side_panel(): bool {
		return ! empty( $this->side_panel_custom_image_id );
	}

	public static function create_from_settings( string $step_code, array $step_settings ): Step {
		return new Step( [
			'code'                      => $step_code,
			'label'                     => \OsStepsHelper::get_step_label_by_code( $step_code ),
			'side_panel_heading'        => $step_settings['side_panel_heading'] ?? '',
			'side_panel_description'    => $step_settings['side_panel_description'] ?? '',
			'main_panel_heading'        => $step_settings['main_panel_heading'] ?? '',
			'main_panel_content_before' => $step_settings['main_panel_content_before'] ?? '',
			'main_panel_content_after'  => $step_settings['main_panel_content_after'] ?? '',
			'side_panel_custom_image_id'   => $step_settings['side_panel_custom_image_id'] ?? '',
		] );
	}

	public static function allowed_props(): array {
		return [
			'code',
			'label',
			'side_panel_heading',
			'side_panel_description',
			'main_panel_heading',
			'main_panel_content_before',
			'main_panel_content_after',
			'side_panel_custom_image_id',
		];
	}
}