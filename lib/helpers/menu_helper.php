<?php 

class OsMenuHelper {

	public static array $side_menu_items;

  public static function get_menu_items_by_id($query){
    $menus = self::get_side_menu_items();
    foreach($menus as $menu_item){
      if(isset($menu_item['id']) && $menu_item['id'] == $query){
				if(isset($menu_item['children'])){
					// has sub items
					return $menu_item['children'];
				}else{
					// no sub items
					return $menu_item['label'];
				}
      }
    }
    return false;
  }

  public static function get_label_by_id($query){
    $menus = self::get_side_menu_items();
    foreach($menus as $menu_item){
      if(isset($menu_item['id']) && $menu_item['id'] == $query){
				return $menu_item['label'];
      }
    }
    return false;
  }

  public static function get_side_menu_items() {
		if(isset(self::$side_menu_items)) return self::$side_menu_items;
    $is_update_available = false;
		$menus = [];
		$user_role = OsAuthHelper::get_current_user()->backend_user_type;
		switch($user_role){
			case LATEPOINT_USER_TYPE_ADMIN:
			case LATEPOINT_USER_TYPE_CUSTOM:
	      // ---------------
	      // ADMINISTRATOR MENU
	      // ---------------
	      $menus = array(
	        array( 'id' => 'dashboard', 'label' => __( 'Dashboard', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-dashboard', 'link' => OsRouterHelper::build_link(['dashboard', 'index'])),
	        array( 'id' => 'calendar', 'label' => __( 'Calendar', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-calendar2', 'link' => OsRouterHelper::build_link(['calendars', 'view'])),
	        array( 'id' => 'appointments', 'label' => __( 'Appointments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-box1', 'link' => OsRouterHelper::build_link(['bookings', 'index'])),
	        array( 'id' => 'orders', 'label' => __( 'Orders', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-book2', 'link' => OsRouterHelper::build_link(['orders', 'index'])),
	        array( 'id' => 'payments', 'label' => __( 'Payments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-cart', 'link' => OsRouterHelper::build_link(['transactions', 'index'])),
	        array( 'id' => 'customers', 'label' => __( 'Customers', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-user1', 'link' => OsRouterHelper::build_link(['customers', 'index'])),
	        array('label' => '', 'small_label' => __('Resources', 'latepoint'), 'menu_section' => 'records'),
	        array( 'id' => 'services', 'label' => __( 'Services', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-folder', 'link' => OsRouterHelper::build_link(['services', 'index']),
	          'children' => array(
	                          array('id' => 'index','label' => __( 'Services', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['services', 'index'])),
	                          array('id' => 'bundles','label' => __( 'Bundles', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'bundles'])),
	                          array('id' => 'categories','label' => __( 'Categories', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'categories'])),
	                          array('id' => 'service_extras','label' => __( 'Service Extras', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'service_extras'])),
	          )
	        ),
	        array( 'id' => 'agents', 'label' => __( 'Agents', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-user1', 'link' => OsRouterHelper::build_link(['default_agent', 'edit_form'])),
	        array( 'id' => 'locations', 'label' => __( 'Locations', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-map-marker', 'link' => OsRouterHelper::build_link(['pro', 'locations'])),
	        array('label' => '', 'small_label' => __('Settings', 'latepoint'), 'menu_section' => 'settings'),
	        array( 'id' => 'settings', 'show_notice' => $is_update_available, 'label' => __( 'Settings', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-cog1', 'link' => OsRouterHelper::build_link(['settings', 'general']),
	          'children' => array(
	                          array('id' => 'general', 'label' => __( 'General', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'general'])),
	                          array('id' => 'schedule', 'label' => __( 'Schedule', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'work_periods'])),
	                          array('id' => 'taxes', 'label' => __( 'Tax', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'taxes'])),
	                          array('id' => 'coupons', 'label' => __( 'Coupons', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'coupons'])),
	                          array('id' => 'booking_form', 'label' => __( 'Booking Form', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['booking_form_settings', 'show'])),
	                          array('id' => 'payments', 'label' => __( 'Payments', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'payments'])),
	                          array('id' => 'notifications', 'label' => __( 'Notifications', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['settings', 'notifications'])),
	                          array('id' => 'roles', 'label' => __( 'Roles', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'roles'])),
	          )
	        ),
	        array( 'id' => 'processes', 'label' => __( 'Automation', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-play', 'link' => OsRouterHelper::build_link(['processes', 'index']),
	          'children' => array(
	                          array('label' => __('Workflows', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['processes', 'index'])),
	                          array('label' => __('Scheduled Jobs', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['process_jobs', 'index'])),
	                          array('label' => __('Activity Log', 'latepoint'), 'icon' => '', 'link' => OsRouterHelper::build_link(['activities', 'index'])),
	                        )
	        ),
	        array( 'id' => 'integrations', 'label' => __( 'Integrations', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-windows', 'link' => OsRouterHelper::build_link(['integrations', 'external_calendars']),
	          'children' => array(
	                          array('id' => 'calendars', 'label' => __( 'Calendars', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['integrations', 'external_calendars'])),
	                          array('id' => 'meetings', 'label' => __( 'Meetings', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['integrations', 'external_meeting_systems'])),
	                          array('id' => 'meetings', 'label' => __( 'Marketing', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['integrations', 'external_marketing_systems'])),
	          )
	        ),
		      array( 'id' => 'form_fields', 'label' => __( 'Form Fields', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-browser', 'link' => OsRouterHelper::build_link(['form_fields', 'default_form_fields'])),
	      );
				break;
			case LATEPOINT_USER_TYPE_AGENT:
	      // ---------------
	      // AGENT MENU
	      // ---------------
	      $menus = array(
	        array( 'id' => 'dashboard', 'label' => __( 'Dashboard', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-dashboard', 'link' => OsRouterHelper::build_link(['dashboard', 'index'])),
	        array( 'id' => 'calendar', 'label' => __( 'Calendar', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-calendar2', 'link' => OsRouterHelper::build_link(['calendars', 'view'])),
	        array( 'id' => 'appointments', 'label' => __( 'Appointments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-box1', 'link' => OsRouterHelper::build_link(['bookings', 'index'])),
			array( 'id' => 'orders', 'label' => __( 'Orders', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-book2', 'link' => OsRouterHelper::build_link(['orders', 'index'])),
	        array( 'id' => 'payments', 'label' => __( 'Payments', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-cart', 'link' => OsRouterHelper::build_link(['transactions', 'index'])),
	        array( 'id' => 'customers', 'label' => __( 'Customers', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-user1', 'link' => OsRouterHelper::build_link(['customers', 'index'])),
			array( 'id' => 'services', 'label' => __( 'Services', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-folder', 'link' => OsRouterHelper::build_link(['services', 'index']),
	          'children' => array(
	                          array('id' => 'index','label' => __( 'Services', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['services', 'index'])),
	                          array('id' => 'bundles','label' => __( 'Bundles', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'bundles'])),
	                          array('id' => 'categories','label' => __( 'Categories', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'categories'])),
	                          array('id' => 'service_extras','label' => __( 'Service Extras', 'latepoint' ), 'icon' => '', 'link' => OsRouterHelper::build_link(['pro', 'service_extras'])),
	          )
	        ),
	        array( 'id' => 'locations', 'label' => __( 'Locations', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-map-marker', 'link' => OsRouterHelper::build_link(['pro', 'locations'])),
	        array( 'id' => 'settings',  'label' => __( 'Settings', 'latepoint' ), 'icon' => 'latepoint-icon latepoint-icon-cog1', 'link' => OsRouterHelper::build_link(['agents', 'edit_form'], array('id' => OsAuthHelper::get_logged_in_agent_id()) ))
	      );
				break;
		}
		/**
		 * Filters side menu items
		 *
		 * @since 4.7.0
		 * @hook latepoint_side_menu
		 *
		 * @param {array} $menus Array of side menu items in a format ['id' => '', 'label' => '', 'icon' => '', 'link' => '', 'children' => [ ['label' => '', 'icon' => '', 'link' => ''] ]
		 * @returns {array} Filtered array of side menu items
		 */
    $menus = apply_filters('latepoint_side_menu', $menus, $user_role);
		self::$side_menu_items = self::filter_by_user_capabilities($menus);
		return self::$side_menu_items;
  }

	public static function filter_by_user_capabilities(array $menus): array{
		$total_menu_items = count($menus);
		for($i = 0; $i < $total_menu_items; $i++){
			if(!empty($menus[$i]['children'])){
				$menus[$i]['children'] = self::filter_by_user_capabilities($menus[$i]['children']);
			}
			if(!empty($menus[$i]['link'])){
				parse_str(wp_parse_url($menus[$i]['link'])['query'] ?? '',$params);
				if(empty($params['route_name'])) continue; // not a controller__action route, could be custom URL

				$split_route_name = explode('__', $params['route_name']);
				if(empty($split_route_name) || count($split_route_name) != 2) continue; // not a controller__action route, could be custom URL

				$controller_name = $split_route_name[0];
				$action = $split_route_name[1];

				if(empty($controller_name) || empty($action)) continue;  // not a controller__action route, could be custom URL
		    $controller_name = str_replace('_', '', ucwords($controller_name, '_'));
		    $controller_class_name = 'Os'.$controller_name.'Controller';
				$capabilities = OsRolesHelper::get_capabilities_required_for_controller_action($controller_class_name, $action);
				if(!OsAuthHelper::get_current_user()->has_capability($capabilities)) unset($menus[$i]);
			}
		}
		// clean out label items that have no actual items left after them
		$menus = array_values($menus);
		$total_menu_items = count($menus);
		$clean_menu_items = [];
		for($i = 0; $i < $total_menu_items; $i++){
			if(!empty($menus[$i]['small_label']) && (!empty($menus[$i + 1]['small_label']) || $i + 1 == $total_menu_items)){
				continue;
			}
			$clean_menu_items[] = $menus[$i];
		}
		return $clean_menu_items;
	}

}