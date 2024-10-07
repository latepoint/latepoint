<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class ProcessEvent{
	public string $type = 'booking_created'; //  booking_created, booking_updated, booking_start, booking_end, transaction_created, customer_created
	public array $trigger_conditions = [];
	public array $time_offset = [];

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}


	public function set_from_params($event_params){
		$this->type = $event_params['type'];
		$this->trigger_conditions = ($event_params['conditional'] == LATEPOINT_VALUE_ON) ? $event_params['trigger_conditions'] : [];
		$this->time_offset = ($event_params['has_time_offset'] == LATEPOINT_VALUE_ON) ? $event_params['time_offset'] : [];
	}

	public function get_available_data_sources(array $trigger_conditions = []): array{
		$data_sources = [];
		switch($this->type){
			case 'order_created':
				$orders_for_select = \OsOrdersHelper::get_orders_for_select();
				$data_sources[] = [
					'name' => 'order_id',
					'values' => $orders_for_select,
					'label' => __('Choose an order for this test run:', 'latepoint'),
					'model' => 'order'];
				break;
			case 'order_updated':
				$orders_for_select = \OsOrdersHelper::get_orders_for_select();
				$data_sources[] = [
					'name' => 'new_order_id',
					'values' => $orders_for_select,
					'label' => __('Choose old order to be used for this test run:', 'latepoint'),
					'model' => 'order'];
				$data_sources[] = [
					'name' => 'old_order_id',
					'values' => $orders_for_select,
					'label' => __('Choose new order to be used for this test run:', 'latepoint'),
					'model' => 'order'];
				break;
			case 'booking_created':
			case 'booking_start':
			case 'booking_end':
				$bookings_for_select = \OsBookingHelper::get_bookings_for_select();
				$data_sources[] = [
					'name' => 'booking_id',
					'values' => $bookings_for_select,
					'label' => __('Choose a booking for this test run:', 'latepoint'),
					'model' => 'booking'];
				break;
			case 'booking_updated':
				$bookings_for_select = \OsBookingHelper::get_bookings_for_select();
				$data_sources[] = [
					'name' => 'new_booking_id',
					'values' => $bookings_for_select,
					'label' => __('Choose old booking to be used for this test run:', 'latepoint'),
					'model' => 'booking'];
				$data_sources[] = [
					'name' => 'old_booking_id',
					'values' => $bookings_for_select,
					'label' => __('Choose new booking to be used for this test run:', 'latepoint'),
					'model' => 'booking'];
				break;
			case 'transaction_created':
				$transactions = \OsPaymentsHelper::get_transactions_for_select();
				$data_sources[] = [
					'name' => 'transaction_id',
					'values' => $transactions,
					'label' => __('Choose a transaction for this test run:', 'latepoint'),
					'model' => 'transaction'];
				break;
			case 'customer_created':
				$customers = \OsCustomerHelper::get_customers_for_select();
				$data_sources[] = [
					'name' => 'customer_id',
					'values' => $customers,
					'label' => __('Choose a customer for this test run:', 'latepoint'),
					'model' => 'customer'];
				break;
		}

		/**
		 * Returns an array of process event data sources
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_event_data_sources
		 *
		 * @param {array} $data_sources Current array of process event data sources
		 * @param {ProcessEvent} $event The ProcessEvent object for which to generate data sources
		 *
		 * @returns {array} Filtered array of process event data sources
		 */
		return apply_filters('latepoint_process_event_data_sources', $data_sources, $this);
	}

	public function trigger_conditions_form_html(){
		$html = '';
		if($this->trigger_conditions){
			foreach($this->trigger_conditions as $trigger_condition){
				$html.= $this->generate_trigger_condition_form_html($trigger_condition);
			}
		}else{
			$html.= $this->generate_trigger_condition_form_html();
		}
		return $html;
	}

	public static function get_object_from_property(string $property):string{
		return explode('__', $property)[0] ?? '';
	}

	public static function get_object_attribute_from_property(string $property):string{
		return explode('__', $property)[1] ?? '';
	}

	public static function get_model_by_code(string $object_code): \OsModel{
		$model = new \OsBookingModel();
		switch($object_code){
			case 'order':
			case 'old_order':
				$model = new \OsOrderModel();
				break;
			case 'booking':
			case 'old_booking':
				$model = new \OsBookingModel();
				break;
			case 'transaction':
				$model = new \OsTransactionModel();
				break;
			case 'customer':
				$model = new \OsCustomerModel();
				break;
			case 'agent':
				$model = new \OsAgentModel();
				break;
			case 'service':
				$model = new \OsServiceModel();
				break;
		}

		/**
		 * Returns an instance of <code>OsModel</code>, based on the supplied object code
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_event_model
		 *
		 * @param {OsModel} $model Current model
		 * @param {string} $object_code The object code used to determine the resultant model
		 *
		 * @returns {OsModel} Instance of <code>OsModel</code> based on the supplied object code
		 */
		return apply_filters('latepoint_process_event_model', $model, $object_code);
	}

	public static function get_properties_for_object_code(string $object_code, bool $prepare_for_select = false): array{
		$model = self::get_model_by_code($object_code);
		$properties = $model->get_properties_to_query();
		if($prepare_for_select){
			$properties_for_select = [];
			// glue property name and object code together so they are identifiable in a select box
			foreach($properties as $property_code => $property_label){
				$property_full_code = $object_code.'__'.$property_code;
				$operators = self::trigger_condition_operators_for_property($property_full_code);
				if(!empty($operators)) $properties_for_select[] = ['value' => $property_full_code, 'label' => $property_label];
			}
			return $properties_for_select;
		}else{
			return $properties;
		}
	}

	public static function get_available_trigger_condition_objects_for_event_type(string $event_type): array{
		$objects = [];
		switch ($event_type) {
			case 'booking_created':
			case 'booking_start':
			case 'booking_end':
				$objects[] = ['code' => 'booking', 'model' => 'OsBookingModel', 'label' => __('Booking', 'latepoint'), 'properties' => []];
				break;
			case 'booking_updated':
				$objects[] = ['code' => 'old_booking', 'model' => 'OsBookingModel', 'label' => __('Old Booking', 'latepoint'), 'properties' => []];
				$objects[] = ['code' => 'booking', 'model' => 'OsBookingModel', 'label' => __('New Booking', 'latepoint'), 'properties' => []];
				break;
			case 'order_updated':
				$objects[] = ['code' => 'old_order', 'model' => 'OsOrderModel', 'label' => __('Old Order', 'latepoint'), 'properties' => []];
				$objects[] = ['code' => 'order', 'model' => 'OsOrderModel', 'label' => __('New Order', 'latepoint'), 'properties' => []];
				break;
			case 'order_created':
				$objects[] = ['code' => 'order', 'model' => 'OsOrderModel', 'label' => __('Order', 'latepoint'), 'properties' => []];
				break;
			case 'transaction_created':
				$objects[] = ['code' => 'transaction', 'model' => 'OsTransactionModel', 'label' => __('Transaction', 'latepoint'), 'properties' => []];
				break;
			case 'customer_created':
//				$objects[] = ['code' => 'customer', 'model' => 'OsCustomerModel', 'label' => __('Customer', 'latepoint'), 'properties' => []];
				break;
			case 'agent_created':
				$objects[] = ['code' => 'agent', 'model' => 'OsAgentModel', 'label' => __('Agent', 'latepoint'), 'properties' => []];
				break;
			case 'service_created':
				$objects[] = ['code' => 'service', 'model' => 'OsServiceModel', 'label' => __('Service', 'latepoint'), 'properties' => []];
				break;
		}

		/**
		 * Returns an array of condition objects, based on the supplied event type
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_event_condition_objects
		 *
		 * @param {array} $objects Current array of condition objects
		 * @param {string} $event_type The event type for which to generate condition objects
		 *
		 * @returns {array} Filtered array of available condition objects
		 */
		return apply_filters('latepoint_process_event_condition_objects', $objects, $event_type);
	}

	public function generate_trigger_condition_form_html($trigger_condition = false){
		$objects = self::get_available_trigger_condition_objects_for_event_type($this->type);
		$objects_for_select = [];
		foreach($objects as $object){
			$objects_for_select[] = ['value' => $object['code'], 'label' => $object['label']];
		}

    // new condition
    if(!$trigger_condition){
			$selected_object_code = $objects_for_select[0]['value'];
	    $properties_for_select = self::get_properties_for_object_code($selected_object_code, true);
			$operators_for_select = self::trigger_condition_operators_for_property($properties_for_select[0]['value']);
      $trigger_condition = [
				'id' => self::generate_trigger_condition_id(),
	      'property' => $properties_for_select[0]['value'] ?? '',
	      'operator' => $operators_for_select[0]['value'] ?? 'equal',
	      'value' => false
      ];
    }else{
			$operators_for_select = self::trigger_condition_operators_for_property($trigger_condition['property']);
			$selected_object_code = $trigger_condition['property'] ? explode('__', $trigger_condition['property'])[0] : $objects_for_select[0]['value'];
	    $properties_for_select = self::get_properties_for_object_code($selected_object_code, true);
			$operators_for_select = self::trigger_condition_operators_for_property($trigger_condition['property']);
    }
		// if we only have 1 object available - no need to output the select box for it
		if(count($objects_for_select) > 1){
			$object_selector_html = \OsFormHelper::select_field('process[event][trigger_conditions]['.$trigger_condition['id'].'][object]', false, $objects_for_select, $selected_object_code,
				['class' => 'process-condition-object-selector', 'data-change-target' => 'process-condition-properties-w', 'data-condition-id' => $trigger_condition['id'], 'data-route' => \OsRouterHelper::build_route_name('processes', 'available_properties_for_object_code')]);
		}else{
			$object_selector_html = '';
		}
    $html = '<div class="pe-condition" data-condition-id="'.$trigger_condition['id'].'">'.
                '<button class="pe-remove-condition"><i class="latepoint-icon latepoint-icon-cross"></i></button>'.
                $object_selector_html.
                \OsFormHelper::select_field( 'process[event][trigger_conditions]['.$trigger_condition['id'].'][property]', false, $properties_for_select, $trigger_condition['property'],
	                [ 'class' => 'process-condition-property-selector', 'data-route' => \OsRouterHelper::build_route_name('processes', 'available_operators_for_trigger_condition_property') ],
	                [ 'class' => 'process-condition-properties-w' ]).
                \OsFormHelper::select_field( 'process[event][trigger_conditions]['.$trigger_condition['id'].'][operator]', false, $operators_for_select, $trigger_condition['operator'],
									[ 'class' => 'process-condition-operator-selector', 'data-route' => \OsRouterHelper::build_route_name('processes', 'available_values_for_trigger_condition_property') ],
									[ 'class' => 'process-condition-operators-w' ]).
                \OsFormHelper::multi_select_field('process[event][trigger_conditions]['.$trigger_condition['id'].'][value]', false, \OsProcessesHelper::values_for_trigger_condition_property($trigger_condition['property']), $trigger_condition['value'] ? explode(',', $trigger_condition['value']) : [],
	                [],
	                ['class' => 'process-condition-values-w', 'style' => in_array($trigger_condition['operator'], ['changed', 'not_changed']) ? 'display: none;' : '']).
                '<div data-os-action="'.\OsRouterHelper::build_route_name('processes', 'new_trigger_condition').'" 
                      data-os-pass-response="yes" 
                      data-os-pass-this="yes" 
                      data-os-before-after="none"
                      data-os-params="'.\OsUtilHelper::build_os_params(['event_type' => $this->type]).'"
                      data-os-after-call="latepoint_add_process_condition"><button class="latepoint-btn-outline latepoint-btn"><i class="latepoint-icon latepoint-icon-plus2"></i><span>'.__('AND', 'latepoint').'</span></button></div>'.
            '</div>';
    return $html;
	}

	public static function trigger_condition_operators_for_property($property = ''){
		$object_code = $property ? explode('__', $property)[0] : 'booking';
		$object_property = $property ? explode('__', $property)[1] : '';
		$operators = [];
		switch($object_code){
			case 'old_order':
			case 'old_booking':
				// TODO time range operators instead of removing these opearators completely
				if($object_property != 'start_datetime_utc'){
					$operators['equal'] = __('was equal to', 'latepoint');
					$operators['not_equal'] = __('was not equal to', 'latepoint');
				}
				$operators['changed'] = __('has changed', 'latepoint');
				$operators['not_changed'] = __('has not changed', 'latepoint');
				break;
			case 'order':
			case 'booking':
			case 'customer':
			case 'agent':
			case 'service':
			case 'transaction':
				// TODO time range operators instead of removing these opearators completely
				if($object_property != 'start_datetime_utc'){
					$operators['equal'] = __('is equal to', 'latepoint');
					$operators['not_equal'] = __('is not equal to', 'latepoint');
				}
				break;
		}

		/**
		 * Returns an array of operators available for a selected condition property
		 *
     * @since 4.7.0
     * @hook latepoint_process_event_trigger_condition_properties
		 *
		 * @param {array} $operators Array of operators
		 * @param {string} $property Property in a format of object_code__object_property (e.g. old_booking__agent_id)
	     *
	     * @returns {array} The array of available operators
		 *
		 */
    return apply_filters('latepoint_process_event_trigger_condition_operators', $operators, $property);
	}

	public static function trigger_condition_properties_for_type($event_type){
		$properties = [];
		switch ($event_type){
			case 'order_created':
				$properties = [
					'order__status' => __('Order Status', 'latepoint'),
					'order__fulfillment_status' => __('Fulfillment Service', 'latepoint'),
					'order__payment_status' => __('Payment Status', 'latepoint')];
				break;
			case 'order_updated':
				$properties = [
					'old__order__status' => __('Previous Order Status', 'latepoint'),
					'old__order__fulfillment_status' => __('Previous Fulfillment Service', 'latepoint'),
					'old__order__payment_status' => __('Previous Payment Status', 'latepoint'),
					'order__status' => __('Order Status', 'latepoint'),
					'order__fulfillment_status' => __('Fulfillment Service', 'latepoint'),
					'order__payment_status' => __('Payment Status', 'latepoint')];
				break;
			case 'booking_created':
				$properties = [
					'booking__status' => __('Booking Status', 'latepoint'),
					'booking__service_id' => __('Service', 'latepoint'),
					'booking__agent_id' => __('Agent', 'latepoint')];
				break;
			case 'booking_updated':
				$properties = [
					'old__booking__status' => __('Previous Booking Status', 'latepoint'),
					'old__booking__service_id' => __('Previous Service', 'latepoint'),
					'old__booking__agent_id' => __('Previous Agent', 'latepoint'),
					'old__booking__start_datetime_utc' => __('Start Time', 'latepoint'),
					'booking__status' => __('Booking Status', 'latepoint'),
					'booking__service_id' => __('Service', 'latepoint'),
					'booking__agent_id' => __('Agent', 'latepoint')];
				break;
			case 'transaction_created':
				$properties = [
					'transaction__payment_method' => __('Payment Method', 'latepoint')
				];
				break;
		}

    return apply_filters('latepoint_process_event_trigger_condition_properties', $properties, $event_type);
	}

	public static function get_event_types(){
		$event_types = ['order_created', 'order_updated', 'booking_created', 'booking_updated', 'booking_start', 'booking_end', 'customer_created', 'transaction_created'];
		
		/**
		 * Returns an array of event types that trigger automation process
		 *
	     * @since 4.7.0
	     * @hook latepoint_process_event_types
		 *
		 * @param {array} $event_types Array of event types
	     *
	     * @returns {array} The array of event types that trigger automation process
		 */
	    return apply_filters('latepoint_process_event_types', $event_types);
	}


	public static function get_event_name_for_type($type){
		$names = [
			'order_created' => __('Order Created', 'latepoint'),
			'order_updated' => __('Order Updated', 'latepoint'),
			'booking_created' => __('Booking Created', 'latepoint'),
			'booking_updated' => __('Booking Updated', 'latepoint'),
			'booking_start' => __('Booking Started', 'latepoint'),
			'booking_end' => __('Booking Ended', 'latepoint'),
			'customer_created' => __('Customer Created', 'latepoint'),
			'transaction_created' => __('Transaction Created', 'latepoint'),
		];

		/**
		 * Returns an array of process event types mapped to their displayable names
		 *
		 * @since 4.7.0
		 * @hook latepoint_process_event_names
		 *
		 * @param {array} $names Array of event types/names to filter
		 *
		 * @returns {array} Filtered array of event types/names
		 */
		$names = apply_filters('latepoint_process_event_names', $names);
		
		return $names[$type] ?? $type;
	}

	public static function get_event_types_for_select(){
		$types = self::get_event_types();
		$types_for_select = [];
		foreach($types as $type){
			$types_for_select[$type] = self::get_event_name_for_type($type);
		}
		return $types_for_select;
	}

	public static function generate_trigger_condition_id(): string{
  	return 'pec_'.\OsUtilHelper::random_text('alnum', 6);
  }

	public static function allowed_props(): array{
		return ['id', 'type', 'trigger_conditions', 'time_offset'];
	}
}