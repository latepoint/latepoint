<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsTodosController' ) ) :


  class OsTodosController extends OsController {

	  function __construct() {
		  parent::__construct();


		  $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'processes/';
		  $this->vars['page_header'] = __('Tasks', 'latepoint');
		  $this->vars['breadcrumbs'][] = array('label' => __('Todos', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('todos', 'index')));
	  }
  }
endif;