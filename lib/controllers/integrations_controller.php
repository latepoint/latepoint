<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsIntegrationsController' ) ) :


  class OsIntegrationsController extends OsController {


	  function __construct() {
		  parent::__construct();

		  $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'integrations/';
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('integrations');
      $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('integrations');
		  $this->vars['breadcrumbs'][] = array('label' => __('Integrations', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('integrations', 'external_calendars')));
	  }

	  public function external_meeting_systems() {
		  $this->vars['available_meeting_systems'] = OsMeetingSystemsHelper::get_list_of_external_meeting_systems();
		  $this->format_render(__FUNCTION__);
	  }

	  public function external_calendars() {
		  $this->vars['available_calendars'] = OsCalendarHelper::get_list_of_external_calendars();
		  $this->format_render(__FUNCTION__);
	  }

	  public function external_marketing_systems() {
		  $this->vars['available_marketing_systems'] = OsMarketingSystemsHelper::get_list_of_external_marketing_systems();
		  $this->format_render(__FUNCTION__);
	  }
  }

endif;