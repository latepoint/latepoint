<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsManageBookingByKeyController' ) ) :


  class OsManageBookingByKeyController extends OsController {
		private $booking;
		private $key_for;
		private $key = '';

		private function set_booking_by_key(){
			if(empty($this->params['key'])) return;

			$key = sanitize_text_field( $this->params['key'] );
			$data = OsBookingHelper::get_booking_id_and_manage_ability_by_key($key);
			if(empty($data)) return;
			$booking = new OsBookingModel($data['booking_id']);
			if($booking->id){
				$this->key = $key;
				$this->booking = $booking;
				$this->key_for = $data['for'];
			}
		}

	  function __construct() {
		  parent::__construct();
		  $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'manage_booking_by_key/';

		  $this->action_access['public'] = array_merge($this->action_access['public'], [
			  'show',
			  'print_booking_info',
			  'ical_download',
			  'change_status',
			  'request_cancellation',
			  'process_reschedule_request',
			  'request_reschedule_calendar',]);

			$this->set_booking_by_key();

	  }


		function show(){
			if(empty($this->booking->id)) return;


			$this->vars['key'] = $this->key;
			$this->vars['for'] = $this->key_for;
			$this->vars['booking'] = $this->booking;
			$this->vars['order'] = $this->booking->get_order();
			$this->vars['order_manage_key'] = OsMetaHelper::get_order_meta_by_key( 'key_to_manage_for_' . $this->key_for, $this->booking->get_order()->id );

			$this->vars['timezone_name'] = $this->key_for == 'agent' ? OsTimeHelper::get_wp_timezone_name() : $this->booking->customer->get_selected_timezone_name();

      if($this->get_return_format() == 'json'){
	      $this->set_layout('none');
				$response_html = $this->format_render_return(__FUNCTION__);
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html));
      }else{
	      $this->set_layout('clean');
	      $content = $this->format_render_return(__FUNCTION__);
				echo $content;
      }
		}


    function ical_download(){
			if(empty($this->booking->id)) return;

      header('Content-Type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename=booking_'.$this->booking->id.'.ics');

      echo OsBookingHelper::generate_ical_event_string($this->booking);
    }

    function print_booking_info(){
			if(empty($this->booking->id)) return;

			$this->vars['booking'] = $this->booking;
			$this->vars['order'] = $this->booking->get_order();
			$this->vars['customer'] = $this->booking->customer;
      $this->set_layout('print');
      $content = $this->format_render_return(__FUNCTION__, [], [], true);
			echo $content;
    }



		function process_reschedule_request(){
			if(empty($this->booking->id) || empty($this->params['start_date']) || empty($this->params['start_time'])) return;

			$allowed = ($this->key_for == 'agent') ? true : OsCustomerHelper::can_reschedule_booking($this->booking);
			if($allowed){
				$old_booking = clone $this->booking;
	      $this->booking->start_date = $this->params['start_date'];
	      $this->booking->start_time = $this->params['start_time'];
				$this->booking->end_time = $this->booking->calculate_end_time();
	      $this->booking->end_date = $this->booking->calculate_end_date();
				$this->booking->set_utc_datetimes();
				// check if booking time is still available
	      if (!OsBookingHelper::is_booking_request_available(\LatePoint\Misc\BookingRequest::create_from_booking_model($this->booking), ['exclude_booking_ids' => [$this->booking->id]])) {
		      $response_html = __('Unfortunately the selected time slot is not available anymore, please select another timeslot.', 'latepoint');
					$status = LATEPOINT_STATUS_ERROR;
	      }else{
					// customer rescheduled, perform actions
					if($this->key_for == 'customer'){
						if(OsSettingsHelper::is_on('change_status_on_customer_reschedule')){
							$allowed_statuses = OsBookingHelper::get_statuses_list();
							if(isset($allowed_statuses[OsSettingsHelper::get_settings_value('status_to_set_after_customer_reschedule')])) $this->booking->status = OsSettingsHelper::get_settings_value('status_to_set_after_customer_reschedule');
						}
					}
					if($this->booking->save()){
						/**
						 * Booking is updated
						 *
						 * @since 4.9.0
						 * @hook latepoint_booking_updated
						 *
						 * @param {OsBookingModel} $this->>booking Updated instance of booking model
						 * @param {OsBookingModel} $old_booking Instance of booking model before it was updated
						 */
			      do_action('latepoint_booking_updated', $this->booking, $old_booking);
						$this->vars['booking'] = $this->booking;
						$this->vars['timezone_name'] = ($this->key_for == 'agent') ? OsTimeHelper::get_wp_timezone_name() : $this->booking->customer->get_selected_timezone_name();
						$status = LATEPOINT_STATUS_SUCCESS;
		        $this->set_layout('none');
						$response_html = $this->format_render_return(__FUNCTION__, [], [], true);
			    }else{
						OsDebugHelper::log('Error rescheduling appointment', 'booking_reschedule_error', $this->booking->get_error_messages());
			      $response_html = __('Error! Please try again later', 'latepoint');
						$status = LATEPOINT_STATUS_ERROR;
			    }
				}
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error! LKDFU343', 'latepoint');
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
		}

		function request_reschedule_calendar(){
			if(empty($this->booking->id)) return;

			$allowed = ($this->key_for == 'agent') ? true : OsCustomerHelper::can_reschedule_booking($this->booking);
			if($allowed){
				$this->vars['booking'] = $this->booking;
				$this->vars['key'] = $this->key;
				$this->vars['calendar_start_date'] = !empty($this->params['calendar_start_date']) ? new OsWpDateTime($this->params['calendar_start_date']) : new OsWpDateTime('today');
				$timezone_name = ($this->key_for == 'agent') ? OsTimeHelper::get_wp_timezone_name() : $this->booking->customer->get_selected_timezone_name();
				$this->vars['timeshift_minutes'] = OsTimeHelper::get_timezone_shift_in_minutes($timezone_name);
				$this->vars['timezone_name'] = $timezone_name;

        $this->set_layout('none');
        $response_html = $this->format_render_return(__FUNCTION__, [], [], true);
			}else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Reschedule is not allowed', 'latepoint');
			}
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
		}

    function request_cancellation(){
			if(empty($this->booking->id)) return;

      if(OsCustomerHelper::can_cancel_booking($this->booking)){
				if($this->booking->update_status(LATEPOINT_BOOKING_STATUS_CANCELLED)){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Appointment Status Updated', 'latepoint');
				}else{
	        $status = LATEPOINT_STATUS_ERROR;
	        $response_html = __('Error Updating Booking Status!', 'latepoint').' '.implode(',', $this->booking->get_error_messages());
				}
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Not allowed to cancel', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    function change_status(){
			// only agent key can cancel
			if($this->key_for != 'agent' || empty($this->booking->id) || empty($this->params['status'])) return;
			$statuses = OsBookingHelper::get_statuses_list();
			if(!isset($statuses[$this->params['status']])) return;


			if($this->booking->update_status($this->params['status'])){
        $status = LATEPOINT_STATUS_SUCCESS;
        $response_html = __('Appointment Status Updated', 'latepoint');
			}else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error Updating Booking Status!', 'latepoint').' '.implode(',', $this->booking->get_error_messages());
			}

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }
  }
endif;