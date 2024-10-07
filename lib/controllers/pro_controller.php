<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsProController' ) ) :


  class OsProController extends OsController {


	  function __construct() {
		  parent::__construct();
	  }

	  public function roles() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('settings');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('settings');
      $this->format_render('pro_feature', [], [], true);
	  }


	  public function coupons() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('settings');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('settings');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function taxes() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('settings');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('settings');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function bundles() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('services');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('services');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function categories() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('services');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('services');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function service_extras() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('services');
		  $this->vars['pre_page_header'] = OsMenuHelper::get_label_by_id('services');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function agents() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('agents');
      $this->format_render('pro_feature', [], [], true);
	  }

	  public function locations() {
		  $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('locations');
      $this->format_render('pro_feature', [], [], true);
	  }

  }

endif;