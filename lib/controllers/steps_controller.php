<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsStepsController' ) ) :


	class OsStepsController extends OsController {

		private $booking;

		function __construct() {
			parent::__construct();
			$this->action_access['customer'] = array_merge( $this->action_access['customer'], [ 'start_from_order_intent' ] );
			$this->action_access['public']   = array_merge( $this->action_access['public'], [
				'start',
				'load_step',
				'reload_booking_form_summary_panel',
				'check_order_intent_bookable',
				'load_datepicker_month'
			] );

			$this->views_folder          = LATEPOINT_VIEWS_ABSPATH . 'steps/';
			$this->vars['page_header']   = __( 'Appointments', 'latepoint' );
			$this->vars['breadcrumbs'][] = array(
				'label' => __( 'Appointments', 'latepoint' ),
				'link'  => OsRouterHelper::build_link( [
					'bookings',
					'pending_approval'
				] )
			);
		}

		public function load_datepicker_month() {
			OsStepsHelper::set_required_objects( $this->params );

			$target_date       = new OsWpDateTime( $this->params['target_date_string'] );
			$calendar_settings = [
				'layout'                => $this->params['calendar_layout'] ?? 'classic',
				'timezone_name'         => $this->params['timezone_name'] ?? false,
			];

			$calendar_settings['earliest_possible_booking'] = OsSettingsHelper::get_settings_value( 'earliest_possible_booking', false );
			$calendar_settings['latest_possible_booking']   = OsSettingsHelper::get_settings_value( 'latest_possible_booking', false );

			$this->format_render( 'partials/_monthly_calendar_days', [
				'target_date'       => $target_date,
				'calendar_settings' => $calendar_settings,
				'booking_request'   => \LatePoint\Misc\BookingRequest::create_from_booking_model( OsStepsHelper::$booking_object )
			] );
		}


		public function check_order_intent_bookable() {
			OsStepsHelper::set_required_objects( $this->params );
			if(OsStepsHelper::$cart_object->order_id){
				// already converted, so we are good
				$this->send_json( [
					'status'  => LATEPOINT_STATUS_SUCCESS,
					'message' => __( 'Cart has already been converted to order', 'latepoint' )
				] );
			}
			$order_intent = OsOrderIntentHelper::create_or_update_order_intent( OsStepsHelper::$cart_object, OsStepsHelper::$restrictions, OsStepsHelper::$presets );
			if ( $order_intent->is_bookable() ) {
				$this->send_json( [
					'status'  => LATEPOINT_STATUS_SUCCESS,
					'message' => __( 'Order intent can be converted to order', 'latepoint' )
				] );
			} else {
				$this->send_json( [
					'status'  => LATEPOINT_STATUS_ERROR,
					'message' => __( 'Selected booking slot is not available anymore. Please pick a different time slot.', 'latepoint' )
				] );
			}
		}


		function reload_booking_form_summary_panel() {
			OsStepsHelper::set_required_objects( $this->params );
			$this->vars['cart'] = OsStepsHelper::$cart_object;

			if ( OsStepsHelper::is_ready_for_summary() ) {
				$this->vars['booking']          = OsStepsHelper::$booking_object;
				$this->vars['active_cart_item'] = OsStepsHelper::$active_cart_item;

				if ( $this->get_return_format() == 'json' ) {
					$response_html = $this->render( $this->views_folder . 'partials/_booking_form_summary_panel', 'none' );
					wp_send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => $response_html ] );
					exit();
				} else {
					echo $this->render( $this->views_folder . 'partials/_booking_form_summary_panel', $this->get_layout() );
				}
			} else {
				wp_send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => '' ] );
			}

		}


		public function start_from_order_intent() {
			$order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key($this->params['order_intent_key']);

			if ( !$order_intent->is_new_record() ) {
				$step_codes_to_preload = [];
				$steps = OsStepsHelper::get_steps();
				OsStepsHelper::set_required_objects($this->params);

				if ( $order_intent->order_id ) {
					// if order is created - load it
					OsStepsHelper::load_order_object( $order_intent->order_id );
					$current_step_code = 'confirmation';
					$current_step      = $steps[ $current_step_code ];

					$this->vars['price_breakdown_rows'] = OsStepsHelper::$order_object->generate_price_breakdown_rows();

					$steps = [ 'confirmation' => $steps['confirmation'] ];


				} else {

					OsStepsHelper::set_cart_object_from_order_intent( $order_intent );
					$current_step_code = 'verify';
					$current_step      = $steps[ $current_step_code ];


					foreach ( OsStepsHelper::$step_codes_in_order as $step_code ) {
						if ( $step_code == $current_step_code ) {
							break;
						} else {
							$step_codes_to_preload[] = $step_code;
						}
					}

					$this->vars['price_breakdown_rows'] = OsStepsHelper::$cart_object->generate_price_breakdown_rows();
					// order exists - only load confirmation step
					$this->vars['step_codes_to_preload'] = $step_codes_to_preload;

				}

				$this->vars['cart'] = OsStepsHelper::$cart_object;
				$this->vars['show_next_btn'] = OsStepsHelper::can_step_show_next_btn( $current_step_code );
				$this->vars['show_prev_btn'] = OsStepsHelper::can_step_show_prev_btn( $current_step_code );
				$this->vars['all_steps']     = OsStepsHelper::get_steps( true );
				$this->vars['steps']         = $steps;
				$this->vars['current_step']  = $current_step;

				$this->vars['current_step_code'] = $current_step_code;
				$this->vars['booking']           = OsStepsHelper::$booking_object;
				$this->vars['active_cart_item']  = OsStepsHelper::$active_cart_item;
				$this->vars['restrictions']      = OsStepsHelper::$restrictions;
				$this->vars['presets']           = OsStepsHelper::$presets;
				$this->vars['booking_element_type']      = 'lightbox';
				$this->vars['booking_element_styles'] = [];





				$this->set_layout( 'none' );

				$this->format_render( 'start', array(), array( 'lightbox_class' => '' ) );
			} else {
				$this->send_json( array(
					'status'  => LATEPOINT_STATUS_ERROR,
					'message' => __( 'Invalid order intent key', 'latepoint' )
				) );
			}

		}

		public function start( array $custom_restrictions = [], array $custom_presets = [], array $booking_element_styles = [], bool $output = true, string $booking_element_type = 'lightbox' ) {
			$merged_params = $this->params;

			if ( ! empty( $custom_restrictions ) ) {
				$merged_params['restrictions'] = $custom_restrictions;
			}
			if ( ! empty( $custom_presets ) ) {
				$merged_params['presets'] = $custom_presets;
			}
			if( ! empty( $this->params['booking_element_type'] ) ) {
				$booking_element_type = $this->params['booking_element_type'];
			}


			if(!empty($merged_params['booking_element_styles'])){
				$booking_element_styles = array_merge( $booking_element_styles, $merged_params['booking_element_styles'] );
			}

			// set early to check if it's converted or should be emptied
			OsStepsHelper::set_cart_object();
			if(!empty(OsStepsHelper::$cart_object->order_id)){
				OsCartsHelper::reset_cart();
				OsStepsHelper::set_cart_object();
			}
			// clear cart if "shopping cart" feature is not enabled
			if ( ! OsCartsHelper::can_checkout_multiple_items() ) OsStepsHelper::$cart_object->clear();




			OsStepsHelper::set_required_objects( $merged_params );

			$steps = OsStepsHelper::get_steps();

			$current_step_code =  OsStepsHelper::get_step_codes_in_order()[0];

			if ( OsStepsHelper::should_step_be_skipped( $current_step_code ) ) {
				$current_step_code = OsStepsHelper::get_next_step_code( $current_step_code );
			}
			// check if all booking steps have to be skipped, if so - it means the booking object is ready and we can add it to the cart
			$ready_to_add_to_cart = true;
			foreach($steps as $step_code => $step_object) {
				$step_main_parent_code = explode('__', $step_code);
				if(!empty($step_main_parent_code[0]) && $step_main_parent_code[0] == 'booking'){
					$ready_to_add_to_cart = false;
					break;
				}
			}
			// looks like item is ready to be added to cart (because all necessary steps/presets where applied in a trigger element), add it to cart
			if($ready_to_add_to_cart){
				try{
					OsStepsHelper::add_current_item_to_cart();
				}catch(Exception $e){
					$this->vars['booking']           = OsStepsHelper::$booking_object;
					if ( $output ) {
						$this->format_render( 'preset_slot_not_available' );
						return false;
					} else {
						return $this->format_render_return( 'preset_slot_not_available' );
					}
				}
			}
			$current_step = $steps[ $current_step_code ];

			$this->vars['cart'] = OsStepsHelper::$cart_object;
			$this->vars['show_next_btn'] = OsStepsHelper::can_step_show_next_btn( $current_step->code );
			$this->vars['show_prev_btn'] = OsStepsHelper::can_step_show_prev_btn( $current_step->code );
			$this->vars['all_steps']     = OsStepsHelper::get_steps( true );
			$this->vars['steps']         = $steps;
			$this->vars['current_step']  = $current_step;

			$this->vars['current_step_code'] = $current_step->code;
			$this->vars['booking']           = OsStepsHelper::$booking_object;
			$this->vars['active_cart_item']  = OsStepsHelper::$active_cart_item;
			$this->vars['restrictions']      = OsStepsHelper::$restrictions;
			$this->vars['presets']           = OsStepsHelper::$presets;
			$this->vars['booking_element_type']      = $booking_element_type;
			$this->vars['booking_element_styles'] = $booking_element_styles;

			$this->set_layout( 'none' );


			LatePoint\Cerber\Router::smell();

			if ( $output ) {
				$this->format_render( __FUNCTION__, [], [ 'step' => $current_step->code ] );
			} else {
				return $this->format_render_return( __FUNCTION__, [], [ 'step' => $current_step->code ] );
			}
		}


		public function load_step() {
			OsStepsHelper::set_required_objects( $this->params );

			$current_step_code = OsStepsHelper::retrieve_step_code( $this->params['current_step_code'] );
			if ( empty( $current_step_code ) ) {
				return false;
			}

			$step_direction    = $this->params['step_direction'] ?? 'next';
			$step_code_to_load = false;
			switch ( $step_direction ) {
				case 'next':
					do_action( 'latepoint_process_step', $current_step_code, OsStepsHelper::$booking_object );
					$step_code_to_load = OsStepsHelper::get_next_step_code( $current_step_code );
					break;
				case 'prev':
					$step_code_to_load = OsStepsHelper::get_prev_step_code( $current_step_code );
					break;
				case 'specific':
					$step_code_to_load = OsStepsHelper::should_step_be_skipped( $current_step_code ) ? OsStepsHelper::get_next_step_code( $current_step_code ) : $current_step_code;
					break;
			}
			if ( $step_code_to_load ) {
				do_action( 'latepoint_load_step', $step_code_to_load, 'json' );
			}
		}


	}


endif;
