<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class User{
	public ?string $backend_user_type = null;
	public ?\WP_User $wp_user = null;
	public array $roles = [];

	public ?\OsAgentModel $agent = null;
	public ?\OsCustomerModel $customer = null;

	public ?string $wp_capability = null;
	protected array $allowed_records = ['agent' => [], 'service' => [], 'location' => []];
	protected array $capabilities = [];


	function __construct(){
	}


	public function get_wp_user_meta($meta_key, $default = ''){
		$meta_value = $default;
		if($this->wp_user){
			$meta_value = get_user_meta($this->wp_user->ID, $meta_key, true);
			if(empty($meta_value)) $meta_value = $default;
		}
		return $meta_value;
	}


	public function update_wp_user_meta($meta_key, $meta_value){
		if($this->wp_user){
			update_user_meta($this->wp_user->ID, $meta_key, $meta_value);
		}
	}

	/**
	 *
	 * Checks if user has a certain capability
	 *
	 * @param array|string $capability single capability or an array of capabilities to check if user has or not
	 * @return bool
	 */
	public function has_capability($capability): bool{
		if($this->has_backend_access()){
			// only backend user types have capabilities, check if it's a backend user type first
			if(is_array($capability)){
				$can = empty(array_diff($capability, $this->get_capabilities()));
			}else{
				$can = in_array($capability, $this->get_capabilities());
			}
		}else{
			$can = false;
		}
		/**
		 * Checks if a user has certain capability
		 *
		 * @since 4.7.0
		 * @hook latepoint_user_has_capability
		 *
		 * @param {bool} $can answer to a question if user has a capability
		 * @param {array|string} $capability array or a single capability that needs to be checked
		 * @returns {bool} answer to a question if user has a capability
		 */
		return apply_filters('latepoint_user_has_capability', $can, $capability);
	}

	public static function load_from_wp_user(\WP_User $wp_user): User{
		$user = new self();
		$user->wp_user = $wp_user;

		if(in_array('administrator', $wp_user->roles)){
			// ADMIN
			$user->backend_user_type = LATEPOINT_USER_TYPE_ADMIN;
			$user->wp_capability = 'manage_options';
		}elseif(in_array(LATEPOINT_WP_AGENT_ROLE, $wp_user->roles)){
			// AGENT
			$user->backend_user_type = LATEPOINT_USER_TYPE_AGENT;
			$user->wp_capability = 'edit_bookings';
			// get connected agent model
      $agent = new \OsAgentModel();
      $agent = $agent->where(['wp_user_id' => $wp_user->ID])->set_limit(1)->get_results_as_models();
			if($agent) $user->agent = $agent;
		}else{
			// see if it's one of custom roles
			$custom_roles = \OsRolesHelper::get_custom_roles();
			foreach($custom_roles as $custom_role){
				if(!empty($wp_user->roles) && in_array($custom_role['wp_role'], $wp_user->roles)){
					$user->backend_user_type = LATEPOINT_USER_TYPE_CUSTOM;
					$user->wp_capability = 'manage_latepoint';
					break;
				}
			}
		}
		$user->set_roles();
		$user->set_capabilities();
		$user->set_allowed_records();
		return $user;
	}

	public function set_roles(){
		if(empty($this->wp_user)) return;
		if($this->wp_user->roles){
			foreach($this->wp_user->roles as $role){
				$this->roles[] = Role::get_from_wp_role($role);
			}
		}
	}

	/*
	 * Check if user has custom permissions set to access actions, instead of using default ones attached to the role
	 */
	public function is_custom_capabilities(): bool{
		return(!empty($this->get_custom_capabilities()));
	}

	/*
	 * Check if user has custom access to model records, instead of using default ones attached to the role
	 */
	public function is_custom_allowed_records(): bool{
		return(!empty($this->get_custom_allowed_records()));
	}


	// PERMITTED ACTIONS
	// -----------------

	public function clear_custom_capabilities(){
		return delete_user_meta($this->wp_user->ID, 'latepoint_custom_capabilities');
	}

	public function set_custom_capabilities($capabilities){
		return update_user_meta($this->wp_user->ID, 'latepoint_custom_capabilities', $capabilities);
	}

	public function get_custom_capabilities(){
		return get_user_meta($this->wp_user->ID, 'latepoint_custom_capabilities', true);
	}

	public function get_capabilities(){
		return $this->capabilities;
	}

	protected function set_capabilities(){
		switch($this->backend_user_type){
			case LATEPOINT_USER_TYPE_ADMIN:
				// admins always get all permissions
				$this->capabilities = \OsRolesHelper::get_all_available_actions_list();
				break;
			case LATEPOINT_USER_TYPE_AGENT:
			case LATEPOINT_USER_TYPE_CUSTOM:
					$custom = $this->get_custom_capabilities();
					if(empty($custom)){
						foreach($this->roles as $role){
							$this->capabilities = array_merge($this->capabilities, $role->get_capabilities());
						}
					}else{
						$this->capabilities = maybe_unserialize($custom);
					}
				break;
		}
	}


	// ALLOWED RECORDS
	// -----------------

	public function clear_custom_allowed_records(){
		return delete_user_meta($this->wp_user->ID, 'latepoint_custom_allowed_records');
	}

	public function set_custom_allowed_records($allowed_records){
		if(empty($allowed_records)){
			return $this->clear_custom_allowed_records();
		}else{
			return update_user_meta($this->wp_user->ID, 'latepoint_custom_allowed_records', $allowed_records);
		}
	}

	public function get_custom_allowed_records(){
		return get_user_meta($this->wp_user->ID, 'latepoint_custom_allowed_records', true);
	}

	public function get_allowed_records(string $model_type, $load_from_db = false){
		if($load_from_db){
			// do not calculate allowed records based on what connections are available, just load from database whatever is set
			$custom = $this->get_custom_allowed_records();
			if($custom){
				$custom_records = maybe_unserialize($custom);
				return !empty($custom_records[$model_type]) ? $custom_records[$model_type] : [];
			}else{
				return LATEPOINT_ALL;
			}
		}else{
			return !empty($this->allowed_records[$model_type]) ? $this->allowed_records[$model_type] : [];
		}
	}

	public function is_single_record_allowed(string $model_type): bool{
		return $this->are_all_records_allowed($model_type) ? false : (count($this->get_allowed_records($model_type)) == 1);
	}

	public function are_all_records_allowed(string $model_type = '', $load_from_db = false): bool{
		if($load_from_db && $model_type){
			$allowed_records = $this->get_allowed_records($model_type, true);
			return ($allowed_records == LATEPOINT_ALL);
		}else{
			if($model_type){
				return ($this->allowed_records[$model_type] == LATEPOINT_ALL);
			}else{
				return (($this->allowed_records['agent'] == LATEPOINT_ALL) && ($this->allowed_records['location'] == LATEPOINT_ALL) && ($this->allowed_records['service'] == LATEPOINT_ALL));
			}
		}
	}

	public function check_if_allowed_record_id($id, string $model_type){
		if(empty($id)) return false;
		if($this->are_all_records_allowed($model_type)) return $id;
		if(array_intersect([$id], $this->get_allowed_records($model_type))) return $id;
		return false;
	}

	public function clean_query_args($args){
		$model_types = ['agent', 'service', 'location'];
		foreach($model_types as $model_type){
			if(empty($args[$model_type.'_id']) && !$this->are_all_records_allowed($model_type)){
				$args[$model_type.'_id'] = $this->get_allowed_records($model_type);
			}
		}
		return $args;
	}

	protected function set_allowed_records(){
		switch($this->backend_user_type){
			case LATEPOINT_USER_TYPE_ADMIN:
				$this->allowed_records['agent'] = LATEPOINT_ALL;
				$this->allowed_records['service'] = LATEPOINT_ALL;
				$this->allowed_records['location'] = LATEPOINT_ALL;
				break;
			case LATEPOINT_USER_TYPE_AGENT:
				if($this->agent){
					$this->allowed_records['agent'] = [$this->agent->id] ?? [];
					$connection = new \OsConnectorModel();
					$connections = $connection->where(['agent_id' => $this->agent->id])->get_results_as_models();

					if($connections){
						foreach($connections as $connection){
							if(!in_array($connection->service_id, $this->allowed_records['service'])) $this->allowed_records['service'][] = $connection->service_id;
							if(!in_array($connection->location_id, $this->allowed_records['location'])) $this->allowed_records['location'][] = $connection->location_id;
						}
					}
				}
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				// each user has their own settings, by default they allowed to access ALL, query DB to find custom settings
				$custom = $this->get_custom_allowed_records();
				if(empty($custom)){
					$this->allowed_records['agent'] = LATEPOINT_ALL;
					$this->allowed_records['service'] = LATEPOINT_ALL;
					$this->allowed_records['location'] = LATEPOINT_ALL;
				}else{
					$custom_records = maybe_unserialize($custom);
					$model_types = ['agent', 'service', 'location'];
					$connection = new \OsConnectorModel();
					// (e.g. if specific locations are selected - make sure agents and services are filtered to only have those that are connected to that location)
					foreach($model_types as $model_type){
						if($custom_records[$model_type] != LATEPOINT_ALL){
							$connection->where([$model_type.'_id' => $custom_records[$model_type]]);
						}
					}
					$allowed_connections = $connection->get_results_as_models();
					foreach($allowed_connections as $allowed_connection){
						if(!in_array($allowed_connection->agent_id, $this->allowed_records['agent'])) $this->allowed_records['agent'][] = $allowed_connection->agent_id;
						if(!in_array($allowed_connection->service_id, $this->allowed_records['service'])) $this->allowed_records['service'][] = $allowed_connection->service_id;
						if(!in_array($allowed_connection->location_id, $this->allowed_records['location'])) $this->allowed_records['location'][] = $allowed_connection->location_id;
					}
				}
				break;
		}
	}


	public function get_link_to_settings(): string{
		switch($this->backend_user_type){
			case LATEPOINT_USER_TYPE_ADMIN:
				return \OsRouterHelper::build_link(['settings', 'general']);
			case LATEPOINT_USER_TYPE_AGENT:
				return \OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $this->agent->id]);
		}
		return '';
	}

	public function get_avatar_url(): string{
		// if backend user logged in - try to get their avatar first, before trying to get customer url
		if($this->backend_user_type){
			if($this->agent) return $this->agent->get_avatar_url();
			if($this->wp_user) return get_avatar_url($this->wp_user->user_email);
		}
		if($this->customer) return $this->customer->get_avatar_url();
		if($this->wp_user) return get_avatar_url($this->wp_user->user_email);
		return '';
	}

	public function get_display_name(){
		if($this->backend_user_type){
			if($this->agent) return $this->agent->full_name;
			if($this->wp_user) return $this->wp_user->display_name;
		}
		if($this->customer) return $this->customer->full_name;
		if($this->wp_user) return $this->wp_user->display_name;
	}

	public function get_user_type_label(){
		$labels = [
			LATEPOINT_USER_TYPE_ADMIN => __('Administrator', 'latepoint'),
			LATEPOINT_USER_TYPE_AGENT => __('Agent', 'latepoint'),
			LATEPOINT_USER_TYPE_CUSTOM => __('Custom', 'latepoint')
		];
		if($this->backend_user_type){
			if($this->backend_user_type == LATEPOINT_USER_TYPE_CUSTOM){
				if($this->roles && ($this->roles[0] instanceof Role)){
					return $this->roles[0]->name;
				}else{
					return $labels[$this->backend_user_type];
				}
			}else{
				return $labels[$this->backend_user_type];
			}
		}elseif($this->customer){
			return __('Customer', 'latepoint');
		}
	}

	public static function get_backend_user_types(){
		$backend_user_types = [LATEPOINT_USER_TYPE_ADMIN, LATEPOINT_USER_TYPE_AGENT, LATEPOINT_USER_TYPE_CUSTOM];
		/**
		 * Get array of user levels that can access backend
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_backend_user_types
		 *
		 * @param {array} $backend_user_types array of user levels (strings) that can access backend
		 * @returns {array} The filtered array of user types
		 */
		return apply_filters('latepoint_get_backend_user_types', $backend_user_types);
	}

	public function has_backend_access(): bool{
		return in_array($this->backend_user_type, self::get_backend_user_types());
	}

	public static function allowed_props(): array{
		return [];
	}
}