<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsRolesHelper {
	public static array $capabilities_for_controllers;

	public static function get_capabilities_for_all_controllers(){
		if(isset(self::$capabilities_for_controllers)) return self::$capabilities_for_controllers;
		$capabilities = include(LATEPOINT_CONFIG_ABSPATH . 'capabilities_for_controllers.php');

		/**
		 * Get array of capabilities for all controllers
		 *
		 * @since 4.7.0
		 * @hook latepoint_capabilities_for_controllers
		 *
		 * @param {array} $capabilities array of controllers with their default and per action capablities
		 * @returns {array} The filtered array of controllers with capability information
		 */
		self::$capabilities_for_controllers = apply_filters('latepoint_capabilities_for_controllers', $capabilities);
		return self::$capabilities_for_controllers;
	}

	public static function build_capability_name_from_model_action(string $model_class, string $action): string{
		$capability_names_for_models = [
			'OsActivityModel' => 'activity',
			'OsAgentModel' => 'agent',
			'OsAgentMetaModel' => 'agent',
			'OsOrderModel' => 'booking',
			'OsOrderIntentModel' => 'booking',
			'OsOrderMetaModel' => 'booking',
			'OsBookingModel' => 'booking',
			'OsBookingMetaModel' => 'booking',
			'OsConnectorModel' => 'connection',
			'OsCustomerModel' => 'customer',
			'OsCustomerMetaModel' => 'customer',
			'OsLocationModel' => 'location',
			'OsLocationCategoryModel' => 'location',
			'OsServiceModel' => 'service',
			'OsServiceExtraModel' => 'service',
			'OsServiceMetaModel' => 'service',
			'OsBundleModel' => 'bundle',
			'OsServiceCategoryModel' => 'service',
			'OsTransactionModel' => 'transaction',
			'OsInvoiceModel' => 'invoice',
			'OsProcessJobModel' => 'settings',
			'OsStepSettingsModel' => 'settings',
			'OsProcessModel' => 'settings',
			'OsSettingsModel' => 'settings',
			'OsWorkPeriodModel' => 'settings',
		];
		/**
		 * Get array of key => value connections between model name and capability name
		 *
		 * @since 4.7.0
		 * @hook latepoint_capability_names_for_models
		 *
		 * @param {array} $capability_names_for_models array of key => value pairs of model name => capability name
		 * @returns {array} The filtered array of value pairs
		 */
		$capability_names_for_models = apply_filters('latepoint_capability_names_for_models', $capability_names_for_models);
		if(isset($capability_names_for_models[$model_class])){
			$capability = $capability_names_for_models[$model_class].'__'.$action;
			$all_actions = self::get_all_available_actions_list();
			if(in_array($capability, $all_actions)){
				return $capability;
			}else{
				$capability = $capability_names_for_models[$model_class].'__edit';
				if(in_array($capability, $all_actions)) return $capability;
			}
		}
		return '';
	}

	public static function can_user_make_action_on_model_record(OsModel $model, string $action){
		if(OsAuthHelper::get_current_user()->backend_user_type == LATEPOINT_USER_TYPE_ADMIN) return true; // admins are allowed to do everything

		// check if customer is logged in and tries to edit records that belong to them
		if(OsAuthHelper::is_customer_logged_in()){
			// if it's customer - they can edit their customer record and also their bookings
			if($model instanceof OsBookingModel && $model->customer_id == OsAuthHelper::get_logged_in_customer_id()){
				return true;
			}
			if($model instanceof OsCustomerModel && $model->id == OsAuthHelper::get_logged_in_customer_id()){
				return true;
			}
		}
		// if customer is not logged in or logged in customer doesn't have rights to access this record, check if backend user has
		if(self::can_user_perform_model_action(get_class($model), $action)){
			switch(get_class($model)){
				case 'OsBookingModel':
					if(OsAuthHelper::get_current_user()->are_all_records_allowed('agent')) return true;
					$allowed_ids = OsAuthHelper::get_current_user()->get_allowed_records('agent');
					if($allowed_ids && in_array($model->agent_id, $allowed_ids)) return true;
					break;
				case 'OsCustomerModel':
					// check if this customer has any bookings with allowed agents
					if(OsAuthHelper::get_current_user()->are_all_records_allowed('agent')) return true;
					$allowed_ids = OsAuthHelper::get_current_user()->get_allowed_records('agent');
					$bookings = new OsBookingModel();
					if($allowed_ids){
						if($allowed_ids && in_array($model->agent_id, $allowed_ids)) return true;
						$has_bookings_with_allowed_agents = $bookings->select('id')->where(['agent_id' => $allowed_ids, 'customer_id' => $model->id])->set_limit(1)->get_results();
						if(!empty($has_bookings_with_allowed_agents)){
							return true;
						}
					}
					break;
			}
		}
		return false;
	}

	public static function can_user_perform_model_action(string $model_class, string $action): bool{
		$capability = self::build_capability_name_from_model_action($model_class, $action);
		$can = !empty($capability) ? self::can_user($capability) : true;
		return $can;
	}

	/**
	 *
	 * Get capabilities required to access specific action of the controller
	 *
	 * @param string $controller_name class name of the controller
	 * @param string $action action name
	 * @return array
	 */
	public static function get_capabilities_required_for_controller_action(string $controller_name, string $action): array{
		$required_capabilities = ['settings__edit']; // default capabilities, if not set on controller/action level

		$capabilities_for_controllers = self::get_capabilities_for_all_controllers();

		if(isset($capabilities_for_controllers[$controller_name])){
			// try to get capabilities that are specific for action, if not get default for controller
			if(isset($capabilities_for_controllers[$controller_name]['per_action'][$action])){
				$required_capabilities = $capabilities_for_controllers[$controller_name]['per_action'][$action];
			}elseif(isset($capabilities_for_controllers[$controller_name]['default'])){
				$required_capabilities = $capabilities_for_controllers[$controller_name]['default'];
			}
		}
		/**
		 * Get array of capabilities required to access controller's action
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_capabilities_for_controller_action
		 *
		 * @param {array} $required_capabilities array of required capabilities
		 * @returns {array} The filtered array of required capabilities
		 */
		return apply_filters('latepoint_get_capabilities_for_controller_action', $required_capabilities, $controller_name, $action);
	}

	public static function save_capabilities_list_for_agent_role(array $capabilities){
		return OsSettingsHelper::save_setting_by_name('agent_role_capabilities', wp_json_encode($capabilities));
	}

	public static function get_capabilities_list_for_agent_role(){
		$capabilities = json_decode(OsSettingsHelper::get_settings_value('agent_role_capabilities', ''), true);
		if(empty($capabilities)) $capabilities = self::get_default_capabilities_list_for_agent_role();
		/**
		 * Get array of permitted actions for agent role
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_capabilities_list_for_agent_role
		 *
		 * @param {array} $capabilities array of actions permitted to admin user type
		 * @returns {array} The filtered array of permitted actions
		 */
		return apply_filters('latepoint_get_capabilities_list_for_agent_role', $capabilities);
	}


	public static function get_all_available_actions_list_grouped(){
		$actions = self::get_all_available_actions_list();
		$groups = [];
		foreach($actions as $action){
			$object = explode('__', $action);
			$groups[$object[0]][] = $object[1];
		}
		return $groups;
	}

	public static function save_from_params(array $role_params){
		$role = \LatePoint\Misc\Role::generate_from_params($role_params);
		if(empty($role->wp_role)) return new WP_Error('invalid_wp_role', __('WP Role invalid', 'latepoint'));
		switch($role->user_type){
			case LATEPOINT_USER_TYPE_AGENT:
				if(self::save_capabilities_list_for_agent_role($role->get_capabilities())){
					return true;
				}else{
					return new WP_Error('error_saving_role', __('WP Agent role can not be saved', 'latepoint'));
				}
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				$roles = self::get_custom_roles();
				$roles[$role->wp_role] = $role->as_array_to_save();
				if(OsSettingsHelper::save_setting_by_name('custom_roles', wp_json_encode($roles))){
					// register in WP (it's ok to register it on every save, we remove it first and then add, to make sure display name is updated)
					$role->register_in_wp();
					return true;
				}else{
					return new WP_Error('error_saving_role', __('WP Role can not be saved', 'latepoint'));
				}
				break;
			default:
				return new WP_Error('invalid_role_type', __('Invalid role type', 'latepoint'));
				break;
		}
	}

	public static function register_roles_in_wp(){
		// Register Agent Role
		$role = \LatePoint\Misc\Role::get_from_wp_role(LATEPOINT_WP_AGENT_ROLE);
		$role->register_in_wp();

		// Register Custom Roles
		$custom_roles = self::get_custom_roles(true);
		if($custom_roles){
			foreach($custom_roles as $custom_role){
				$custom_role->register_in_wp();
			}
		}
	}

	public static function get_model_types_for_allowed_records(){
		return ['agent', 'service', 'location'];
	}


  public static function generate_role_id(){
  	return 'role_'.OsUtilHelper::random_text('alnum', 8);
  }

	public static function get_custom_roles($as_objects = false): array{
		$roles = json_decode(\OsSettingsHelper::get_settings_value('custom_roles', ''), true) ?? [];
		if($as_objects){
			$roles_objects = [];
			foreach($roles as $role_params){
				$roles_objects[$role_params['wp_role']] = \LatePoint\Misc\Role::generate_from_params($role_params);
			}
			return $roles_objects;
		}else{
			return $roles;
		}
	}

	public static function save_custom_roles(array $roles){
		return \OsSettingsHelper::save_setting_by_name('custom_roles', wp_json_encode($roles));
	}

	public static function delete(string $wp_role): bool{
		// can't delete default roles (admin and agent)
		if(in_array($wp_role, [LATEPOINT_WP_ADMIN_ROLE, LATEPOINT_WP_AGENT_ROLE])) return false;
		// get all custom roles to make sure the one that needs to be deleted is custom role
		$custom_roles = self::get_custom_roles();
		if(isset($custom_roles[$wp_role])){
			unset($custom_roles[$wp_role]);
			self::save_custom_roles($custom_roles);
			remove_role($wp_role);
		}
		return true;
	}

	public static function get_default_roles(): array{
		$roles = [];

		$roles[] = new \LatePoint\Misc\Role(LATEPOINT_USER_TYPE_ADMIN);
		$roles[] = new \LatePoint\Misc\Role(LATEPOINT_USER_TYPE_AGENT);

		return $roles;
	}

	public static function description_for_action($action_code){
		$action_descriptions = [
			'resource_schedule' => __('Edit custom schedule of individual agent, location or service.', 'latepoint'),
			'settings' => __('Access to all settings pages, including general schedule and booking steps.', 'latepoint'),
			'connection' => __('Ability to connect agents to services and locations.', 'latepoint'),
			'chat' => __('Ability to send messages to customers (available with chat addon).', 'latepoint'),
		];

		/**
		 * List of detailed descriptions for available actions
		 *
		 * @since 4.7.2
		 * @hook latepoint_roles_action_descriptions
		 *
		 * @param {array} $action_descriptions Array of descriptions for available actions
		 * @param {string} $action_code Programmatic code of the action being filtered
		 * @returns {array} The filtered array of action descriptions
		 */
		$action_descriptions = apply_filters('latepoint_roles_action_descriptions', $action_descriptions, $action_code);
		return $action_descriptions[$action_code] ?? '';
	}

	public static function name_for_action($action_code){
		$action_names = [
			'chat' => __('Chat', 'latepoint'),
			'activity' => __('Activity Logs', 'latepoint'),
			'agent' => __('Agents', 'latepoint'),
			'service' => __('Services', 'latepoint'),
			'bundle' => __('Bundle', 'latepoint'),
			'location' => __('Locations', 'latepoint'),
			'booking' => __('Bookings & Orders', 'latepoint'),
			'customer' => __('Customers', 'latepoint'),
			'transaction' => __('Payments', 'latepoint'),
			'invoice' => __('Invoice', 'latepoint'),
			'order' => __('Order', 'latepoint'),
			'resource_schedule' => __('Resource Schedules', 'latepoint'),
			'settings' => __('Settings', 'latepoint'),
			'connection' => __('Connections', 'latepoint'),
			'edit' => __('Edit', 'latepoint'),
			'delete' => __('Delete', 'latepoint'),
			'view' => __('View', 'latepoint'),
			'create' => __('Create', 'latepoint'),
		];

		/**
		 * List of human-friendly names for available actions
		 *
		 * @since 4.7.2
		 * @hook latepoint_roles_action_names
		 *
		 * @param {array} $action_names Array of names for available actions
		 * @param {string} $action_code Programmatic code of the action being filtered
		 * @returns {array} The filtered array of action names
		 */
		$action_names = apply_filters('latepoint_roles_action_names', $action_names, $action_code);
		return $action_names[$action_code] ?? $action_code;
	}

	public static function get_default_capabilities_list_for_agent_role(){
		$capabilities = [
			'agent__view', 'agent__edit' ,
			'booking__view', 'booking__delete' ,'booking__create', 'booking__edit',
			'customer__view', 'customer__delete' ,'customer__create', 'customer__edit',
			'transaction__view', 'transaction__delete' ,'transaction__create', 'transaction__edit',
			'invoice__view', 'invoice__delete' ,'invoice__create', 'invoice__edit',
			'activity__view', 'activity__delete' ,'activity__create', 'activity__edit',
			'chat__edit', 'resource_schedule__edit'
		];

		/**
		 * Default list of permitted actions available for agent user type
		 *
		 * @since 4.7.0
		 * @hook latepoint_roles_get_default_capabilities_list_for_agent_role
		 *
		 * @param {array} $capabilities array of permitted actions available to agent user type by default
		 * @returns {array} The filtered array of permitted actions
		 */
		return apply_filters('latepoint_roles_get_default_capabilities_list_for_agent_role', $capabilities);
	}

	public static function get_all_available_actions_list(){
		$actions = [
			'agent__view', 'agent__delete' ,'agent__create', 'agent__edit' ,
			'service__view', 'service__delete' ,'service__create', 'service__edit' ,
			'bundle__view', 'bundle__delete' ,'bundle__create', 'bundle__edit' ,
			'location__view', 'location__delete' ,'location__create', 'location__edit' ,
			'booking__view', 'booking__delete' ,'booking__create', 'booking__edit' ,
			'customer__view', 'customer__delete' ,'customer__create', 'customer__edit' ,
			'transaction__view', 'transaction__delete' ,'transaction__create', 'transaction__edit' ,
			'invoice__view', 'invoice__delete' ,'invoice__create', 'invoice__edit' ,
			'activity__view', 'activity__delete' ,'activity__create', 'activity__edit' ,
			'chat__edit', 'resource_schedule__edit', 'settings__edit', 'connection__edit'
		];

		/**
		 * All available actions to be attached to a user role
		 *
		 * @since 4.7.0
		 * @hook latepoint_roles_get_all_available_actions_list
		 *
		 * @param {array} $actions array of actions that can be attached to a user role
		 * @returns {array} The filtered array of actions
		 */
		return apply_filters('latepoint_roles_get_all_available_actions_list', $actions);
	}

	/**
	 * @param \LatePoint\Misc\Role $role
	 * @return \LatePoint\Misc\User[]
	 */
	public static function get_users_for_role(\LatePoint\Misc\Role $role): array{
		$users = [];
		$wp_users = [];
		switch($role->user_type){
			case LATEPOINT_USER_TYPE_ADMIN:
				$wp_users = get_users(['role__in' => LATEPOINT_WP_ADMIN_ROLE]);
				break;
			case LATEPOINT_USER_TYPE_AGENT:
				$wp_users = get_users(['role' => LATEPOINT_WP_AGENT_ROLE]);
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				if($role->wp_role) $wp_users = get_users(['role' => $role->wp_role]);
				break;
		}
		foreach($wp_users as $wp_user){
			$users[] = \LatePoint\Misc\User::load_from_wp_user($wp_user);
		}
		return $users;
	}

	public static function get_user_roles($user): array {
		return [];
	}

	/**
	 * Checks if currently logged in user has certain capabilities
	 *
	 * @param array|string $capabilities array or string of capabilities that you want to check if logged in user has or not
	 * @return bool
	 */
	public static function can_user($capabilities) :bool{
		if (OsAuthHelper::get_current_user()) {
			return OsAuthHelper::get_current_user()->has_capability( $capabilities );
		}

		return false;
	}

	public static function get_allowed_records(string $model_type, $load_from_db = false){
		return OsAuthHelper::get_current_user()->get_allowed_records($model_type, $load_from_db);
	}

	public static function are_all_records_allowed(string $model_type = '', $load_from_db = false): bool{
		return OsAuthHelper::get_current_user()->are_all_records_allowed($model_type, $load_from_db);
	}

	/*
	 * Pass filter object or an array of arguments for a query to be filtered based on what logged in user is allowed to access
	 */
	public static function filter_allowed_records_from_arguments_or_filter($args_or_filter){
		$model_types = ['agent', 'location', 'service'];
		foreach($model_types as $model_type){
			if(!OsAuthHelper::get_current_user()->are_all_records_allowed($model_type)){
				$prop = $model_type.'_id';

				// get value that needs to be filtered by allowed records
				$value = ($args_or_filter instanceof \LatePoint\Misc\Filter) ? $args_or_filter->$prop ?? [] : $args_or_filter[$prop] ?? [];

				if(empty($value)){
					// no value is set - limit it to allowed records
					$value = OsAuthHelper::get_current_user()->get_allowed_records($model_type);
				}else{
					// value is set - make sure it's in the allowed records list, if not - set to allowed records
					$allowed_from_set = array_intersect(is_array($value) ? $value : [$value], OsAuthHelper::get_current_user()->get_allowed_records($model_type));
					$value = empty($allowed_from_set) ? OsAuthHelper::get_current_user()->get_allowed_records($model_type) : $allowed_from_set;
				}
				if($args_or_filter instanceof \LatePoint\Misc\Filter){
					$args_or_filter->$prop = $value;
				}elseif(is_array($args_or_filter)){
					$args_or_filter[$prop] = $value;
				}
			}
		}
		return $args_or_filter;
	}
}