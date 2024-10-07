<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}


if (!class_exists('OsSettingsController')) :


	class OsSettingsController extends OsController {


		function __construct() {
			parent::__construct();

			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'settings/';
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('settings');
			$this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('settings');
			$this->vars['breadcrumbs'][] = array('label' => __('Settings', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('settings', 'general')));
		}

		public function version_5_intro(){

		    $this->set_layout('full_modal');
			$this->format_render(__FUNCTION__);
		}

		public function steps_order_modal(){
			$this->vars['steps'] = OsStepsHelper::unflatten_steps(OsStepsHelper::get_step_codes_in_order(true));

			$this->format_render(__FUNCTION__);
		}

		public function update_steps_order(){
			$new_order = explode(',', $this->params['steps_order']);
			$errors = [];

			if($new_order){
				$errors = OsStepsHelper::check_steps_for_errors($new_order, OsStepsHelper::get_step_codes_with_rules());
				if(empty($errors)) OsStepsHelper::save_step_codes_in_order($new_order);
			}

			if(empty($errors)){
				$status = LATEPOINT_STATUS_SUCCESS;
				$message = __('Order of steps has been successfully updated', 'latepoint');
			}else{
				$status = LATEPOINT_STATUS_ERROR;
				$message = implode(', ', $errors);
			}

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $message));
			}
		}


		public function set_menu_layout_style() {
			$menu_layout_style = (isset($this->params['menu_layout_style']) && in_array($this->params['menu_layout_style'], ['full', 'compact'])) ? $this->params['menu_layout_style'] : 'full';
			OsSettingsHelper::set_menu_layout_style($menu_layout_style);

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => ''));
			}
		}

		public function notifications() {
			$this->vars['notification_types'] = OsNotificationsHelper::get_available_notification_types();
			$this->format_render(__FUNCTION__);
		}


		public function pages() {
			$this->vars['breadcrumbs'][] = array('label' => __('Pages Setup', 'latepoint'), 'link' => false);

			$pages = get_pages();

			$this->vars['pages'] = $pages;

			$this->format_render(__FUNCTION__);
		}

		public function payments() {
			$this->vars['breadcrumbs'][] = array('label' => __('Payment Processing', 'latepoint'), 'link' => false);

			$pages = get_pages();

			$this->vars['pages'] = $pages;
			$this->vars['payment_processors'] = OsPaymentsHelper::get_payment_processors();

			$this->format_render(__FUNCTION__);
		}


		public function work_periods() {

			$this->vars['breadcrumbs'][] = array('label' => __('Work Schedule Settings', 'latepoint'), 'link' => false);

			$this->format_render(__FUNCTION__);
		}


		public function general() {

			$this->vars['breadcrumbs'][] = array('label' => __('General', 'latepoint'), 'link' => false);


			$this->format_render(__FUNCTION__);
		}

		public function remove_chain_schedule() {
			$chain_id = $this->params['chain_id'];
			if ($chain_id && OsWorkPeriodsHelper::remove_periods_for_chain_id($chain_id)) {
				$response_html = __('Date Range Schedule Removed', 'latepoint');
				$status = LATEPOINT_STATUS_SUCCESS;
			} else {
				$response_html = __('Invalid Data', 'latepoint');
				$status = LATEPOINT_STATUS_ERROR;
			}

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}

		public function remove_custom_day_schedule() {
			$target_date_string = $this->params['date'];
			$args = [];
			$args['agent_id'] = isset($this->params['agent_id']) ? $this->params['agent_id'] : 0;
			$args['service_id'] = isset($this->params['service_id']) ? $this->params['service_id'] : 0;
			$args['location_id'] = isset($this->params['location_id']) ? $this->params['location_id'] : 0;
			if (OsUtilHelper::is_date_valid($target_date_string) && OsWorkPeriodsHelper::remove_periods_for_date($target_date_string, $args)) {
				$response_html = __('Custom Day Schedule Removed', 'latepoint');
				$status = LATEPOINT_STATUS_SUCCESS;
			} else {
				$response_html = __('Invalid Date', 'latepoint');
				$status = LATEPOINT_STATUS_ERROR;
			}

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}


		public function save_columns_for_bookings_table() {
			$selected_columns = [];
			if (isset($this->params['selected_columns']) && $this->params['selected_columns']) {
				foreach ($this->params['selected_columns'] as $column_type => $columns) {
					foreach ($columns as $column_key => $selected_column) {
						if ($selected_column == 'on') {
							$selected_columns[$column_type][] = $column_key;
						}
					}
				}
			}
			OsSettingsHelper::save_setting_by_name('bookings_table_columns', $selected_columns);
			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Columns Saved', 'latepoint')));
			}
		}

		public function save_custom_day_schedule() {
			$this->check_nonce('save_custom_day_schedule');
			$response_html = __('Work Schedule Updated', 'latepoint');
			$status = LATEPOINT_STATUS_SUCCESS;
			$day_date = new OsWpDateTime($this->params['start_custom_date']);
			// if end date is provided and is range
			$period_type = ($this->params['period_type'] == 'range' && $this->params['end_custom_date']) ? 'range' : 'single';

			$start_date = new OsWpDateTime($this->params['start_custom_date']);
			$end_date = ($period_type == 'range') ? new OsWpDateTime($this->params['end_custom_date']) : $start_date;
			$chain_id = (isset($this->params['chain_id'])) ? $this->params['chain_id'] : false;
			$existing_work_periods_ids = (isset($this->params['existing_work_periods_ids'])) ? $this->params['existing_work_periods_ids'] : false;

			// remove existing chained periods by chain ID
			if ($chain_id) {
				$work_periods_to_delete = new OsWorkPeriodModel();
				$work_periods_to_delete->delete_where(['chain_id' => $chain_id]);
				if ($period_type == 'single') $chain_id = false;
			} else {
				$chain_id = ($period_type == 'range') ? uniqid() : false;
			}

			// remove existing periods by period ID
			if ($existing_work_periods_ids) {
				$work_periods_to_delete = new OsWorkPeriodModel();
				$delete_ids = explode(',', $existing_work_periods_ids);
				foreach ($delete_ids as $delete_id) {
					$work_periods_to_delete->delete_where(['id' => $delete_id]);
				}
			}

			for ($day_date = clone $start_date; $day_date <= $end_date; $day_date->modify('+1 day')) {
				$work_periods = $this->params['work_periods'];
				foreach ($work_periods as &$work_period) {
					$work_period['custom_date'] = $day_date->format('Y-m-d');
					$work_period['week_day'] = $day_date->format('N');
					$work_period['chain_id'] = $chain_id ? $chain_id : null;
				}
				unset($work_period);

				OsWorkPeriodsHelper::save_work_periods($work_periods, true);
			}

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}


		public function custom_day_schedule_form() {
			$target_date_string = isset($this->params['target_date']) ? $this->params['target_date'] : 'now + 1 month';
			$this->vars['date_is_preselected'] = isset($this->params['target_date']);
			$this->vars['target_date'] = new OsWpDateTime($target_date_string);
			$this->vars['day_off'] = isset($this->params['day_off']) ? true : false;
			$this->vars['agent_id'] = isset($this->params['agent_id']) ? $this->params['agent_id'] : 0;
			$this->vars['service_id'] = isset($this->params['service_id']) ? $this->params['service_id'] : 0;
			$this->vars['location_id'] = isset($this->params['location_id']) ? $this->params['location_id'] : 0;
			$chain_id = isset($this->params['chain_id']) ? $this->params['chain_id'] : false;
			$this->vars['chain_id'] = $chain_id;
			$this->vars['chain_end_date'] = false;
			if ($chain_id) {
				$work_period = new OsWorkPeriodModel();
				$chained_work_period = $work_period->where(['chain_id' => $chain_id])->order_by('custom_date desc')->set_limit(1)->get_results_as_models();
				if ($chained_work_period) {
					$this->vars['chain_end_date'] = new OsWpDateTime($chained_work_period->custom_date);
				} else {
					$this->vars['chain_id'] = false;
				}
			}
			$this->format_render(__FUNCTION__);
		}


		public function update_work_periods() {
			$this->check_nonce('update_work_periods');
			OsWorkPeriodsHelper::save_work_periods($this->params['work_periods']);
			$response_html = __('Work Schedule Updated', 'latepoint');
			$status = LATEPOINT_STATUS_SUCCESS;

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}


		public function update() {
			$this->check_nonce('update_settings');
			$errors = array();

			if ($this->params['settings']) {
				// make sure thousands and decimal separator are not the same symbol
				if (isset($this->params['settings']['thousand_separator']) && isset($this->params['settings']['decimal_separator']) && ($this->params['settings']['thousand_separator'] == $this->params['settings']['decimal_separator'])) {
					$this->params['settings']['thousand_separator'] = '';
				}
				foreach ($this->params['settings'] as $setting_name => $setting_value) {
					$setting = new OsSettingsModel();
					$setting = $setting->load_by_name($setting_name);
					$is_new_record = $setting->is_new_record();
					if (!$is_new_record) $old_setting_value = $setting->value;
					$setting->name = $setting_name;
					$setting->value = OsSettingsHelper::prepare_value($setting_name, $setting_value);
					if ($setting->save()) {
						if ($is_new_record) {
							do_action('latepoint_setting_created', $setting);
						} else {
							do_action('latepoint_setting_updated', $setting, $old_setting_value);
						}
					} else {
						$errors[] = $setting->get_error_messages();
					}
				}

				do_action('latepoint_settings_updated', $this->params['settings']);
			}

			if (empty($errors)) {
				$response_html = esc_html__('Settings Updated', 'latepoint');
				$status = LATEPOINT_STATUS_SUCCESS;
			} else {
				$response_html = esc_html__('Settings Updated With Errors:', 'latepoint') . implode(', ', $errors);
				$status = LATEPOINT_STATUS_ERROR;
			}

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}

		public function load_step_settings() {

		}


		public function load_work_period_form() {
			$args = ['week_day' => 1, 'agent_id' => 0, 'service_id' => 0, 'location_id' => 0];

			if (isset($this->params['week_day'])) $args['week_day'] = $this->params['week_day'];
			if (isset($this->params['agent_id'])) $args['agent_id'] = $this->params['agent_id'];
			if (isset($this->params['service_id'])) $args['service_id'] = $this->params['service_id'];
			if (isset($this->params['location_id'])) $args['location_id'] = $this->params['location_id'];

			$response_html = OsWorkPeriodsHelper::generate_work_period_form($args);
			$status = LATEPOINT_STATUS_SUCCESS;

			if ($this->get_return_format() == 'json') {
				$this->send_json(array('status' => $status, 'message' => $response_html));
			}
		}

	}


endif;