<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsEventsController' ) ) :


  class OsEventsController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();

      $this->action_access['public'] = array_merge($this->action_access['public'], ['load_calendar_events', 'events_day_view']);
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'events/';
    }

	  public function load_calendar_events() {

		  $target_date               = new OsWpDateTime( $this->params['target_date_string'] );
		  $this->vars['target_date'] = $target_date;

		  $this->vars['filter']       = $this->params['filter'] ?? [];
		  $this->vars['range_type']   = $this->params['calendar_range_type'] ?? 'month';
		  $this->vars['restrictions'] = $this->params['restrictions'] ? json_decode( $this->params['restrictions'], true ) : [];

		  $this->set_layout( 'none' );
		  $this->format_render( __FUNCTION__ );
	  }

		public function events_day_view(){
			$target_date = new OsWpDateTime($this->params['target_date_string']);

			$this->vars['target_date'] = $target_date;
			$this->vars['filter'] = $this->params['filter'] ?? [];

      $this->set_layout('none');
      $this->format_render(__FUNCTION__);
		}
  }

endif;