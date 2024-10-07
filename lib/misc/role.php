<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class Role {
	public ?string $user_type = null;
	public ?string $name;

	public ?string $wp_capability = null;
	public ?string $wp_role = null;

	protected array $capabilities = [];

	function __construct($user_type = null, $wp_role = null) {
		if($user_type){
			$this->user_type = $user_type;

			switch($this->user_type){
				case LATEPOINT_USER_TYPE_ADMIN:
					$this->name = __('Administrator', 'latepoint');
					$this->wp_role = LATEPOINT_WP_ADMIN_ROLE;
					break;
				case LATEPOINT_USER_TYPE_AGENT:
					$this->name = __('LatePoint Agent', 'latepoint');
					$this->wp_role = LATEPOINT_WP_AGENT_ROLE;
					break;
				case LATEPOINT_USER_TYPE_CUSTOM:
					$this->name = __('New Custom Role', 'latepoint');
					$this->wp_role = $wp_role ?? \OsRolesHelper::generate_role_id();
					break;
			}
			$this->set_capabilities();
		}
	}

	public function register_in_wp(): \WP_Role{
		remove_role($this->wp_role);
		add_role($this->wp_role, $this->name);
		$role = get_role($this->wp_role);
		$role->add_cap('read');
		$role->add_cap('upload_files');
		switch($this->user_type){
			case LATEPOINT_USER_TYPE_AGENT:
				$role->add_cap('edit_bookings');
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				$role->add_cap('manage_latepoint');
				break;
		}

		/**
		 * Custom role that was just been registered in LatePoint
		 *
		 * @since 4.7.0
		 * @hook latepoint_register_role
		 *
		 * @param {WP_Role} $wp_role custom role that was just been added
		 * @param {Role} $role role that is used to register wp role
		 * @returns {WP_Role} The filtered wp role object
		 */
		return apply_filters('latepoint_register_role', $role, $this);
	}

	public function as_array_to_save(){
		return ['user_type' => $this->user_type, 'wp_role' => $this->wp_role, 'name' => $this->name, 'capabilities' => $this->capabilities];
	}

	public function set_from_params($params){
		$this->user_type = $params['user_type'] ?? LATEPOINT_USER_TYPE_CUSTOM;
		$this->wp_role = $params['wp_role'] ?? '';
		$this->name = $params['name'] ?? '';
		$this->capabilities = $params['capabilities'] ?? [];
	}

	public static function get_from_wp_role(string $wp_role): Role{
		switch($wp_role){
			case LATEPOINT_WP_ADMIN_ROLE:
				$role = new self(LATEPOINT_USER_TYPE_ADMIN, LATEPOINT_WP_ADMIN_ROLE);
				break;
			case LATEPOINT_WP_AGENT_ROLE:
				$role = new self(LATEPOINT_USER_TYPE_AGENT, LATEPOINT_WP_AGENT_ROLE);

				break;
			default:
				// custom role
				$custom_roles = \OsRolesHelper::get_custom_roles();
				$role = new self();
				if(isset($custom_roles[$wp_role])) $role->set_from_params($custom_roles[$wp_role]);
				break;
		}
		return $role;
	}

	public static function generate_from_params(array $params): Role{
		$role = new self();
		$role->set_from_params($params);
		return $role;
	}

	public function is_action_permitted($action){
		return in_array($action, $this->capabilities);
	}

	public function get_wp_role_display_name(){
		switch($this->user_type) {
			case LATEPOINT_USER_TYPE_ADMIN:
				$display_name = __('Administrator', 'latepoint');
				break;
			case LATEPOINT_USER_TYPE_AGENT:
				$display_name = __('LatePoint Agent', 'latepoint');
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				$display_name = $this->name;
				break;
		}
		return $display_name ?? 'n/a';
	}


	public function set_capabilities(){
		switch($this->user_type){
			case LATEPOINT_USER_TYPE_ADMIN:
				// admin role has access to all actions by default, and can't be changed
				$this->capabilities = \OsRolesHelper::get_all_available_actions_list();
				break;
			case LATEPOINT_USER_TYPE_AGENT:
				$this->capabilities = \OsRolesHelper::get_capabilities_list_for_agent_role();
				break;
			case LATEPOINT_USER_TYPE_CUSTOM:
				$custom_roles = \OsRolesHelper::get_custom_roles();
				$this->capabilities = $custom_roles[$this->wp_role] ?? [];
				break;
		}
	}

	public function get_capabilities(): array{
		if(empty($this->capabilities)) $this->set_capabilities();
		return $this->capabilities;
	}
}