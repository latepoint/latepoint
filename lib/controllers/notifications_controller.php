<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}


if (!class_exists('OsNotificationsController')) :


	class OsNotificationsController extends OsController {

		function __construct() {
			parent::__construct();

			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'notifications/';
			$this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('notifications');
			$this->vars['breadcrumbs'][] = array('label' => __('Notifications', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('notifications', 'settings')));
		}

		public function templates_index() {
			$action_id = $this->params['action_id'];
			$action_type = $this->params['action_type'];

			$templates = OsNotificationsHelper::load_templates_for_action_type($action_type);
			$grouped_templates = [];
			foreach($templates as $template){
				$grouped_templates[$template['to_user_type']][] = $template;
			}

			switch ($action_type) {
				case 'send_email':
					$this->vars['heading'] = __('Select a template', 'latepoint');
					break;
				case 'send_sms':
					$this->vars['heading'] = __('Select a template', 'latepoint');
					break;
			}

			$this->vars['action_type'] = $action_type;
			$this->vars['action_id'] = $action_id;

			$this->vars['selected_template_id'] = false;
			$this->vars['templates'] = $templates;
			$this->vars['grouped_templates'] = $grouped_templates;
			$this->format_render(__FUNCTION__);

		}


	}
endif;