<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsSearchController' ) ) :


  class OsSearchController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();
      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'search/';
    }

    function query_results(){
      $query = trim($this->params['query']);
      if(!$query) return;
    	$sql_query = '%'.$query.'%';


			$bookings = new OsBookingModel();
			$bookings = $bookings->filter_allowed_records()->where(['booking_code' => strtoupper($query)])->get_results_as_models();
			$this->vars['bookings'] = $bookings;

      $customers = new OsCustomerModel();
      $customers->filter_allowed_records()->where(array('OR' => array('CONCAT (first_name, " ", last_name) LIKE ' => $sql_query, 'email LIKE' => $sql_query, 'phone LIKE' => $sql_query)))->set_limit(6);
      
      $customers = $customers->get_results_as_models();
      $this->vars['customers'] = $customers;
      $this->vars['query'] = $query;

      if(OsRolesHelper::can_user('service__view')){
        $services = new OsServiceModel();
        $services = $services->filter_allowed_records()->where(array('name LIKE ' => $sql_query))->set_limit(6)->get_results_as_models();
        $this->vars['services'] = $services;
      }
      if(OsRolesHelper::can_user('agent__view')) {
	      $agents = new OsAgentModel();
	      $agents = $agents->filter_allowed_records()->where(array('OR' => array('CONCAT (first_name, " ", last_name) LIKE ' => $sql_query, 'email LIKE' => $sql_query, 'phone LIKE' => $sql_query)))->set_limit(6)->get_results_as_models();
	      $this->vars['agents'] = $agents;
      }


      $this->format_render(__FUNCTION__);
    }

  }
endif;