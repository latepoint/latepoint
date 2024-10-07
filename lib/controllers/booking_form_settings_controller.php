<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsBookingFormSettingsController' ) ) :


	class OsBookingFormSettingsController extends OsController {


		function __construct() {
			parent::__construct();

			$this->views_folder            = LATEPOINT_VIEWS_ABSPATH . 'booking_form_settings/';
			$this->vars['page_header']     = OsMenuHelper::get_menu_items_by_id( 'settings' );
			$this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id( 'settings' );
			$this->vars['breadcrumbs'][]   = array(
				'label' => __( 'Booking Form Settings', 'latepoint' ),
				'link'  => OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'booking_form_settings', 'preview' ) )
			);
		}

		public function reload_preview() {
			OsStepsHelper::set_cart_object();
			OsStepsHelper::set_booking_object();
			OsStepsHelper::set_restrictions();
			OsStepsHelper::set_presets();
			OsStepsHelper::set_active_cart_item_object();
			$steps_settings = OsStepsHelper::get_steps_settings();
			$steps          = OsStepsHelper::get_step_codes_in_order();
			foreach ( $steps as $step_code ) {
				if ( ! empty( $this->params['steps_settings'][ $step_code ] ) ) {
					foreach ( $this->params['steps_settings'][ $step_code ] as $step_setting_key => $step_setting_value ) {
						// remove empty content for text content settings
						if ( in_array( $step_setting_value, [ '<p><br></p>', '<br>', '<br/>' ] ) ) {
							$step_setting_value = '';
						}
						$steps_settings[ $step_code ][ $step_setting_key ] = trim( $step_setting_value );
					}
				}
			}
			// shared settings also needs to be cleaned
			if ( ! empty( $this->params['steps_settings']['shared'] ) ) {
				foreach ( $this->params['steps_settings']['shared'] as $step_setting_key => $step_setting_value ) {
					// remove empty content for text content settings
					if ( in_array( $step_setting_value, [ '<p><br></p>', '<br>', '<br/>' ] ) ) {
						$step_setting_value = '';
					}
					// shared settings are saved in general settings
					OsSettingsHelper::save_setting_by_name( $step_setting_key, trim( $step_setting_value ) );
				}
			}
			OsStepsHelper::save_steps_settings( $steps_settings );
			$steps_for_select = OsStepsHelper::get_steps_for_select();

			$selected_step_code = $this->params['selected_step_code'];

			$errors = array();

			if ( $this->params['settings'] ) {
				foreach ( $this->params['settings'] as $setting_name => $setting_value ) {
					$setting       = new OsSettingsModel();
					$setting       = $setting->load_by_name( $setting_name );
					$is_new_record = $setting->is_new_record();
					if ( ! $is_new_record ) {
						$old_setting_value = $setting->value;
					}
					$setting->name  = $setting_name;
					$setting->value = OsSettingsHelper::prepare_value( $setting_name, $setting_value );
					OsSettingsHelper::reset_loaded_value( $setting_name );

					if ( $setting->save() ) {
						if ( $is_new_record ) {
							do_action( 'latepoint_setting_created', $setting );
						} else {
							do_action( 'latepoint_setting_updated', $setting, $old_setting_value );
						}
					} else {
						$errors[] = $setting->get_error_messages();
					}
				}

				do_action( 'latepoint_settings_updated', $this->params['settings'] );
			}

			$this->vars['selected_step_code'] = $selected_step_code;
			$this->vars['steps_for_select']   = $steps_for_select;
			$this->vars['booking']            = OsStepsHelper::build_booking_object_for_current_step_preview( $selected_step_code );

			$data['step_settings_html'] = OsStepsHelper::get_step_settings_edit_form_html( $this->params['selected_step_code'] );
			$data['booking_form_html']  = $this->render( $this->views_folder . '_booking_form_preview' );
			$data['css_variables']      = OsStylesHelper::generate_css_variables();
			$data['status']             = LATEPOINT_STATUS_SUCCESS;


			if ( $this->get_return_format() == 'json' ) {
				$this->send_json( $data );
			}
		}


		public function show() {
			$steps                            = OsStepsHelper::get_step_codes_in_order();
			$steps_for_select                 = OsStepsHelper::get_steps_for_select();
			$selected_step_code               = array_key_first( $steps_for_select );
			$this->vars['steps_for_select']   = $steps_for_select;
			$this->vars['steps']              = $steps;
			$this->vars['selected_step_code'] = $selected_step_code;
			$this->vars['booking']            = OsStepsHelper::build_booking_object_for_current_step_preview( $selected_step_code );

			$this->format_render( __FUNCTION__ );
		}

	}
endif;