<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}


if (!class_exists('OsFormFieldsController')) :


	class OsFormFieldsController extends OsController {


		function __construct() {
			parent::__construct();

			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'settings/';
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('form_fields');
			$this->vars['breadcrumbs'][] = array('label' => __('Form Fields', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('form_fields', 'default_form_fields')));
		}

		public function default_form_fields() {
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('form_fields');
			$this->vars['default_fields'] = OsSettingsHelper::get_default_fields_for_customer();

			$this->format_render(__FUNCTION__);
		}

		public function update_default_fields() {
			$updated_fields = $this->params['default_fields'];
			$default_fields = OsSettingsHelper::get_default_fields_for_customer();
			$fields_to_save = [];
			foreach ($default_fields as $name => $default_field) {
				$default_field['width'] = $updated_fields[$name]['width'];
				if (!$default_field['locked']) {
					$default_field['required'] = ($updated_fields[$name]['required'] == 'off') ? false : true;
					$default_field['active'] = ($updated_fields[$name]['active']) ? true : false;
				}
				$fields_to_save[$name] = $default_field;
			}
			OsSettingsHelper::save_setting_by_name('default_fields_for_customer', wp_json_encode($fields_to_save));
			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Default Fields Updated', 'latepoint')));
			}
		}
	}
endif;