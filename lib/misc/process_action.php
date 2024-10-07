<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class ProcessAction{
	public string $id;
	public string $type = 'send_email';
	public string $status = 'active';
	public ?array $settings = [];
	public array $prepared_data_for_run = [];
	public array $replacement_vars = [];
	public array $selected_data_objects = []; // example ['model' => 'booking', 'id' => INT]
	public ProcessEvent $event;

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
		if(empty($this->id)) $this->id = self::generate_id();
		switch($this->type){
			case 'send_email':
				$this->settings['to_email'] = $this->settings['to_email'] ?? '';
				$this->settings['subject'] = $this->settings['subject'] ?? '';
				$this->settings['content'] = $this->settings['content'] ?? '';
				break;
			case 'send_sms':
				$this->settings['to_phone'] = $this->settings['to_phone'] ?? '';
				$this->settings['content'] = $this->settings['content'] ?? '';
				break;
		}
	}

	public function get_nice_type_name(){
		return self::get_action_name_for_type($this->type);
	}

	public function get_descriptive_setting(){
		switch($this->type) {
			case 'send_email':
				return $this->settings['to_email'] ?? '';
			case 'send_sms':
				return $this->settings['to_phone'] ?? '';
		}
	}

	public function generate_replacement_vars(){
		$this->replacement_vars = \OsReplacerHelper::generate_replacement_vars_from_data_objects($this->selected_data_objects);
	}

	public function set_from_params($params){
		if(!empty($params['id'])) $this->id = $params['id'];
		if(!empty($params['type'])) $this->type = $params['type'];
		if(!empty($params['settings'])) $this->settings = $params['settings'];
		if(!empty($params['event'])) $this->event = new ProcessEvent(['type' => $params['event']['type']]);
	}

	public function load_settings_from_template($template_id){
		$templates = \OsNotificationsHelper::load_templates_for_action_type($this->type);
		foreach($templates as $template){
			if($template['id'] == $template_id){
				switch($this->type){
					case 'send_email':
						$this->settings['to_email'] = $template['to_email'];
						$this->settings['subject'] = $template['subject'];
						$this->settings['content'] = $template['content'];
						break;
					case 'send_sms':
						$this->settings['to_phone'] = $template['to_phone'];
						$this->settings['content'] = $template['content'];
						break;
				}

				/**
				 * Returns an array of process action settings, based on a selected template
				 *
				 * @since 4.7.0
				 * @hook latepoint_process_action_settings
				 *
				 * @param {array} $settings Array of settings to filter
				 * @param {array} $template Array of data representing selected template
				 * @param {ProcessAction} $action Instance of <code>ProcessAction</code> for which settings are being generated
				 *
				 * @returns {array} Filtered array of process action settings
				 */
				$this->settings = apply_filters('latepoint_process_action_settings', $this->settings, $template, $this);
				break;
			}
		}
	}

	public static function generate_form(ProcessAction $action): string{
		$descriptive_setting = $action->get_descriptive_setting() ? '<div class="process-action-descriptive-setting">'.$action->get_descriptive_setting().'</div>' : '';
		$html = '<div class="process-action-form pa-type-'.$action->type.' pa-status-'.$action->status.'" data-id="'.$action->id.'">';
			$html.= '<div class="process-action-heading">
								<div class="process-action-status"></div>
								<div class="process-action-icon"></div>
								<div class="process-action-name">'.self::get_action_name_for_type($action->type).'</div>
								'.$descriptive_setting.'
								<div class="process-action-chevron"><i class="latepoint-icon latepoint-icon-chevron-down"></i></div>
								<a href="#" class="process-action-remove os-remove-process-action" 
										data-os-prompt="'.__('Are you sure you want to delete this action?', 'latepoint').'"><i class="latepoint-icon latepoint-icon-cross"></i></a>
							</div>';
			$html.= '<div class="process-action-content">';
			$html.= '<div class="os-row">';
			$html.= \OsFormHelper::select_field('process[actions]['.$action->id.'][type]', __('Action Type', 'latepoint'), \LatePoint\Misc\ProcessAction::get_action_types_for_select(), $action->type, ['class' => 'process-action-type', 'data-action-id' => $action->id, 'data-route' => \OsRouterHelper::build_route_name('processes', 'load_action_settings')], ['class' => 'os-col-10']);
			$html.= \OsFormHelper::select_field('process[actions]['.$action->id.'][status]', __('Status', 'latepoint'), [LATEPOINT_STATUS_ACTIVE => __('Active', 'latepoint'), LATEPOINT_STATUS_DISABLED => __('Disabled', 'latepoint')], $action->status, false, ['class' => 'os-col-2']);
			$html.= '</div>';
			$html.= '<div class="process-action-settings">';
				$html.= self::generate_settings_fields($action);
			$html.= '</div>';
			$html.= '<div class="process-buttons">
								<a href="#" class="latepoint-btn latepoint-btn-danger pull-left os-remove-process-action" 
									data-os-prompt="'.__('Are you sure you want to delete this action?', 'latepoint').'" >'.__('Delete', 'latepoint').'</a>
								<a href="#" data-route="'.\OsRouterHelper::build_route_name('processes', 'action_test_preview').'" class="latepoint-btn latepoint-btn-secondary os-run-process-action" ><i class="latepoint-icon latepoint-icon-play-circle"></i><span>'.__('Test this action', 'latepoint').'</span></a>
							</div>';
			$html.= '</div>';
		$html.= '</div>';
		return $html;
	}


	public static function generate_settings_fields(ProcessAction $action){
		$html = '';

		if(in_array($action->type, ['send_email', 'send_sms'])){
			$html.= '<div class="process-action-controls-wrapper">';
			$html.= '<a href="#" data-os-after-call="latepoint_init_template_library" data-os-params="'.\OsUtilHelper::build_os_params(['action_id'=>$action->id, 'action_type'=>$action->type]).'" data-os-lightbox-classes="width-1000" data-os-action="'.\OsRouterHelper::build_route_name('notifications', 'templates_index').'" href="#" data-os-output-target="side-panel" class="latepoint-btn latepoint-btn-outline latepoint-btn-sm"><i class="latepoint-icon latepoint-icon-book"></i><span>'.__('Load from template', 'latepoint').'</span></a>';
			$html.= '<a href="#" class="latepoint-btn latepoint-btn-outline latepoint-btn-sm open-template-variables-panel"><i class="latepoint-icon latepoint-icon-zap"></i><span>'.__('Show smart variables', 'latepoint').'</span></a>';
			$html.= '</div>';
		}

		switch($action->type){
			case 'send_email':
				$html.= '<div class="os-row">';
				$html.= \OsFormHelper::text_field('process[actions]['.$action->id.'][settings][to_email]', __('To Email', 'latepoint'),  $action->settings['to_email'], ['theme' => 'simple', 'placeholder' => __('To email address', 'latepoint')], ['class' => 'os-col-6']);
				$html.= \OsFormHelper::text_field('process[actions]['.$action->id.'][settings][subject]', __('Email Subject', 'latepoint'),  $action->settings['subject'], ['theme' => 'simple', 'placeholder' => __('Email Subject', 'latepoint')], ['class' => 'os-col-6']);
				$html.= '</div>';
				$html.= \OsFormHelper::textarea_field('process[actions]['.$action->id.'][settings][content]', false, $action->settings['content'], ['id' => 'process_actions_'.$action->id.'_settings_content', 'class' => 'os-wp-editor-textarea']);
				break;
			case 'send_sms':
				if(\OsSmsHelper::get_sms_processors()){
					$html.= \OsFormHelper::text_field('process[actions]['.$action->id.'][settings][to_phone]', __('To Phone Number', 'latepoint'),  $action->settings['to_phone'], ['theme' => 'simple', 'placeholder' => __('Phone Number', 'latepoint')]);
					$html.= \OsFormHelper::textarea_field('process[actions]['.$action->id.'][settings][content]', __('Message Content', 'latepoint'),  $action->settings['content'], ['theme' => 'simple', 'placeholder' => __('Message', 'latepoint'), 'rows' => 4]);
				}else{
					$html = \OsUtilHelper::generate_missing_addon_link(__('Requires upgrade to a premium version', 'latepoint'));
				}
				break;
			case 'trigger_webhook':
				$html = \OsUtilHelper::generate_missing_addon_link(__('Requires upgrade to a premium version', 'latepoint'));
				break;
		}

		/**
	    * Filters HTML code (after) for Process Action settings form
		*
	    * @since 4.7.0
	    * @hook latepoint_process_action_settings_fields_html_after
	    *
	    * @param {string} $html HTML content of the settings form
	    * @param {ProcessAction} $action ProcessAction object for which this settings form is being generated
	    *
	    * @returns {string} HTML content of the settings form
	    */
		return apply_filters('latepoint_process_action_settings_fields_html_after', $html, $action);
	}

	public static function generate_id(): string{
  	return 'pa_'.\OsUtilHelper::random_text('alnum', 6);
  }

	public function settings_form(){
		$html = '';
		switch($this->type){
			case 'send_email':
				$html.= \OsFormHelper::text_field('action[actions]['.$this->id.'][to]', '', $this->settings['to'], ['theme' => 'simple', 'placeholder' => __('Email To', 'latepoint')]);
				break;
			case 'send_sms':
				$html.= \OsFormHelper::text_field('action[actions]['.$this->id.'][to]', '', $this->settings['to'], ['theme' => 'simple', 'placeholder' => __('SMS To', 'latepoint')]);
				break;
			case 'trigger_webhook':
				$html.= \OsFormHelper::text_field('action[actions]['.$this->id.'][url]', '', $this->settings['url'], ['theme' => 'simple', 'placeholder' => __('Webhook URL', 'latepoint')]);
				break;
		}

		return apply_filters('latepoint_process_action_settings_form_html', $html, $this);
	}


	public static function get_action_types(){
		$action_types = ['send_email', 'send_sms', 'trigger_webhook'];

		/**
		 * Returns an array of process action types that can be executed when an event is triggered
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_action_types
		 *
		 * @param {array} $action_types Array of action types to filter
		 *
		 * @returns {array} Filtered array of action types
		 */
		return apply_filters('latepoint_process_action_types', $action_types);
	}

	public static function get_action_name_for_type($type){
		$names = [
			'send_email' => __('Send Email', 'latepoint'),
			'send_sms' => __('Send SMS', 'latepoint'),
			'trigger_webhook' => __('HTTP Request', 'latepoint')
		];

		/**
		 * Returns an array of process action types mapped to their displayable names
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_action_names
		 *
		 * @param {array} $names Array of action types/names to filter
		 *
		 * @returns {array} Filtered array of action types/names
		 */
		$names = apply_filters('latepoint_process_action_names', $names);

		return $names[$type] ?? __('n/a', 'latepoint');
	}

	public static function get_action_types_for_select(){
		$types = self::get_action_types();
		$types_for_select = [];
		foreach($types as $type){
			$types_for_select[$type] = self::get_action_name_for_type($type);
		}
		return $types_for_select;
	}

	public function prepare_data_for_run(){
		$this->generate_replacement_vars();
		foreach($this->selected_data_objects as $data_object){
			switch($data_object['model']) {
				case 'order':
					$this->prepared_data_for_run['activity_data']['order_id'] = $data_object['id'];
					if(!empty($data_object['model_ready'])){
						$this->prepared_data_for_run['activity_data']['customer_id'] = $data_object['model_ready']->customer_id;
					}
					break;
				case 'booking':
					$this->prepared_data_for_run['activity_data']['booking_id'] = $data_object['id'];
					if(!empty($data_object['model_ready'])){
						$this->prepared_data_for_run['activity_data']['agent_id'] = $data_object['model_ready']->agent_id;
						$this->prepared_data_for_run['activity_data']['service_id'] = $data_object['model_ready']->service_id;
						$this->prepared_data_for_run['activity_data']['customer_id'] = $data_object['model_ready']->customer_id;
					}
					break;
				case 'customer':
					$this->prepared_data_for_run['activity_data']['customer_id'] = $data_object['id'];
					break;
				case 'transaction':
					$this->prepared_data_for_run['activity_data']['transaction_id'] = $data_object['id'];
					break;
			}
		}

		switch($this->type) {
			case 'send_email':
				$this->prepared_data_for_run['to'] = \OsReplacerHelper::replace_all_vars($this->settings['to_email'], $this->replacement_vars);
				$this->prepared_data_for_run['subject'] = \OsReplacerHelper::replace_all_vars($this->settings['subject'], $this->replacement_vars);
				$this->prepared_data_for_run['content'] = \OsReplacerHelper::replace_all_vars($this->settings['content'], $this->replacement_vars);
				break;
			case 'send_sms':
				$this->prepared_data_for_run['to'] = \OsReplacerHelper::replace_all_vars($this->settings['to_phone'], $this->replacement_vars);
				$this->prepared_data_for_run['content'] = \OsReplacerHelper::replace_all_vars($this->settings['content'], $this->replacement_vars);
				break;
		}

		/**
		 * Prepare data for action run
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_prepare_data_for_run
		 *
		 * @param {ProcessAction} $action ProcessAction that was executed
		 *
		 * @returns {ProcessAction} $action ProcessAction with prepared data for action run
		 */
		return apply_filters('latepoint_process_prepare_data_for_run', $this);
	}


	/**
	 * @param $prepare_data
	 * @return array [status => '', 'message' => '']
	 */
	public function run($prepare_data = true){
		$result = [
			'status' => LATEPOINT_STATUS_SUCCESS,
			'message' => __('Nothing to run', 'latepoint')
		];
		if($prepare_data) $this->prepare_data_for_run();

		switch($this->type) {
			case 'send_email':
				$notification_type = 'email';
				$result = \OsNotificationsHelper::send($notification_type, $this->prepared_data_for_run);
				break;
			case 'send_sms':
				$notification_type = 'sms';
				$result = \OsNotificationsHelper::send($notification_type, $this->prepared_data_for_run);
				break;
		}


		/**
		 * ProcessAction run result
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_action_run
		 *
		 * @param {array} $result The array of data describing the status of the action run
		 * @param {ProcessAction} $action ProcessAction that was executed
		 *
		 * @returns {array}  The array of descriptive data, possibly transformed by additional hooked ProcessAction handlers
		 */
		return apply_filters('latepoint_process_action_run', $result, $this);
	}


	/**
	 * @return string
	 */
	public function generate_preview(){
		if(empty($this->prepared_data_for_run)){
			$this->prepare_data_for_run();
			// nothing was generated, probably because there is no object attached
			if(empty($this->prepared_data_for_run)) return '<div class="action-preview-error">'.__('You have to create a booking to be able to test this action.', 'latepoint').'</div>';
		}
		$html = '<div class="action-preview-content-wrapper">';
		$preview_content_html = '';
		switch($this->type){
			case 'send_email':
				$preview_content_html.= '<div class="action-preview-subject"><span class="os-label">'.__('Subject:', 'latepoint').'</span> '.$this->prepared_data_for_run['subject'].'</div>';
				$preview_content_html.= '<div class="action-preview-to"><span class="os-label">'.__('To:', 'latepoint').'</span><span class="os-value">'.esc_html($this->prepared_data_for_run['to']).'</div>';
				$preview_content_html.= '<div class="action-preview-content">'.$this->prepared_data_for_run['content'].'</div>';
				break;
			case 'send_sms':
				$preview_content_html.= '<div class="action-preview-to"><span class="os-label">'.__('To:', 'latepoint').'</span><span class="os-value">'.esc_html($this->prepared_data_for_run['to']).'</span></div>';
				$preview_content_html.= '<div class="action-preview-content">'.$this->prepared_data_for_run['content'].'</div>';
				break;
		}

		/**
		 * Generates a preview html for ProcessAction testing
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_action_generate_preview
		 *
		 * @param {string} $preview_content_html HTML that goes inside of a preview
		 * @param {ProcessAction} $action ProcessAction that is being tested
		 *
		 * @returns {string} HTML that goes inside of a preview
		 */
		$preview_content_html = apply_filters('latepoint_process_action_generate_preview', $preview_content_html, $this);
		$html.= $preview_content_html;
		$html.= '</div>';
		return $html;
	}

	public static function replace_variables($test){

	}

	public static function allowed_props(): array{
		return ['id', 'type', 'settings', 'status'];
	}
}