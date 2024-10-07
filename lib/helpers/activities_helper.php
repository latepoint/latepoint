<?php

class OsActivitiesHelper {
	public static function create_activity($atts = array()) {
		$activity = new OsActivityModel();
		$atts['initiated_by'] = OsAuthHelper::get_highest_current_user_type();
		$atts['initiated_by_id'] = OsAuthHelper::get_highest_current_user_id();

		$activity = $activity->set_data($atts);
		$activity->save();
		return $activity;
	}

	public static function get_codes() {
		$codes = [
			'customer_created' => __('New Customer Registration', 'latepoint'),
			'customer_updated' => __('Customer Profile Update', 'latepoint'),
			'order_created' => __('New Order', 'latepoint'),
			'order_updated' => __('Order Edited', 'latepoint'),
			'order_item_created' => __('New Order Item', 'latepoint'),
			'order_item_updated' => __('Order Item Edited', 'latepoint'),
			'order_item_deleted' => __('Order Item Deleted', 'latepoint'),
			'booking_created' => __('New Appointment', 'latepoint'),
			'booking_change_status' => __('Appointment Status Changed', 'latepoint'),
			'booking_updated' => __('Appointment Edited', 'latepoint'),
			'booking_deleted' => __('Appointment Deleted', 'latepoint'),
			'agent_created' => __('New Agent', 'latepoint'),
			'agent_updated' => __('Agent Profile Update', 'latepoint'),
			'coupon_created' => __('New Coupon', 'latepoint'),
			'coupon_updated' => __('Coupon Update', 'latepoint'),
			'service_updated' => __('Service Updated', 'latepoint'),
			'service_created' => __('Service Created', 'latepoint'),
			'location_updated' => __('Location Updated', 'latepoint'),
			'location_created' => __('Location Created', 'latepoint'),
			'sms_sent' => __('SMS Sent', 'latepoint'),
			'email_sent' => __('Email Sent', 'latepoint'),
			'process_job_run' => __('Process Job Run', 'latepoint'),
			'order_intent_converted' => __('Order Intent Converted', 'latepoint'),
			'order_intent_created' => __('Order Intent Created', 'latepoint'),
			'order_intent_updated' => __('Order Intent Updated', 'latepoint'),
			'error' => __('Error', 'latepoint'),
		];
		return apply_filters('latepoint_activity_codes', $codes);
	}

	public static function init_hooks() {

		add_action('latepoint_order_created', 'OsActivitiesHelper::log_order_created');
		add_action('latepoint_order_updated', 'OsActivitiesHelper::log_order_updated', 10, 2);
		add_action('latepoint_booking_created', 'OsActivitiesHelper::log_booking_created');
		add_action('latepoint_booking_updated', 'OsActivitiesHelper::log_booking_updated', 10, 2);
		add_action('latepoint_customer_created', 'OsActivitiesHelper::log_customer_created');
		add_action('latepoint_customer_updated', 'OsActivitiesHelper::log_customer_updated', 10, 2);
		add_action('latepoint_agent_created', 'OsActivitiesHelper::log_agent_created');
		add_action('latepoint_agent_updated', 'OsActivitiesHelper::log_agent_updated', 10, 2);
		add_action('latepoint_service_created', 'OsActivitiesHelper::log_service_created');
		add_action('latepoint_service_updated', 'OsActivitiesHelper::log_service_updated', 10, 2);
		add_action('latepoint_order_intent_converted', 'OsActivitiesHelper::log_order_intent_converted', 10, 2);
		add_action('latepoint_order_intent_created', 'OsActivitiesHelper::log_order_intent_created');
		add_action('latepoint_order_intent_updated', 'OsActivitiesHelper::log_order_intent_updated');

	}

	public static function log_order_intent_updated(OsOrderIntentModel $order_intent) {
		$data = [];
		$data['booking_id'] = $order_intent->order_id;
		$data['customer_id'] = $order_intent->customer_id;
		$data['code'] = 'order_intent_updated';
		$data['description'] = wp_json_encode(['order_data_vars' => $order_intent->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_order_intent_created(OsOrderIntentModel $order_intent) {
		$data = [];
		$data['booking_id'] = $order_intent->order_id;
		$data['customer_id'] = $order_intent->customer_id;
		$data['code'] = 'order_intent_created';
		$data['description'] = wp_json_encode(['order_data_vars' => $order_intent->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_order_intent_converted(OsOrderIntentModel $order_intent, OsOrderModel $order) {
		$data = [];
		$data['booking_id'] = $order_intent->order_id;
		$data['customer_id'] = $order_intent->customer_id;
		$data['code'] = 'order_intent_converted';
		$data['description'] = wp_json_encode(['order_data_vars' => $order_intent->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_order_created(OsOrderModel $order) {
		$data = [];
		$data['order_id'] = $order->id;
		$data['code'] = 'order_created';
		$data['description'] = wp_json_encode(['order_data_vars' => $order->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_order_updated(OsOrderModel $order, OsOrderModel $old_order) {
		$data = [];
		$data['order_id'] = $order->id;
		$data['code'] = 'order_updated';
		$data['description'] = wp_json_encode(['order_data_vars' => ['new' => $order->get_data_vars(), 'old' => $old_order->get_data_vars()]]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_order_item_deleted(OsOrderItemModel $order_item) {
		$data = [];
		$data['order_item_id'] = $order_item->id;
		$data['code'] = 'order_item_deleted';
		$data['description'] = wp_json_encode(['order_item_data_vars' => $order_item->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}


	public static function log_booking_created(OsBookingModel $booking) {
		$data = [];
		$data['booking_id'] = $booking->id;
		$data['code'] = 'booking_created';
		$data['description'] = wp_json_encode(['booking_data_vars' => $booking->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_booking_deleted(OsBookingModel $booking) {
		$data = [];
		$data['booking_id'] = $booking->id;
		$data['code'] = 'booking_deleted';
		$data['description'] = wp_json_encode(['booking_data_vars' => $booking->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_booking_updated(OsBookingModel $booking, OsBookingModel $old_booking) {
		$data = [];
		$data['booking_id'] = $booking->id;
		$data['code'] = 'booking_updated';
		$data['description'] = wp_json_encode(['booking_data_vars' => ['new' => $booking->get_data_vars(), 'old' => $old_booking->get_data_vars()]]);
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_booking_change_status(OsBookingModel $booking, OsBookingModel $old_booking) {
		$data = [];
		$data['booking_id'] = $booking->id;
		$data['code'] = 'booking_change_status';
		// translators: %1$s previous appointment status, %2$s new appointment status
		$data['description'] = sprintf(__('Appointment status changed from %1$s to %2$s', 'latepoint'), $old_booking->status, $booking->status);;
		OsActivitiesHelper::create_activity($data);
	}

	public static function log_customer_created(OsCustomerModel $customer) {
		$data = [];
		$data['customer_id'] = $customer->id;
		$data['code'] = 'customer_created';
		$data['description'] = wp_json_encode(['customer_data_vars' => $customer->get_data_vars()]);
		OsActivitiesHelper::create_activity($data);

	}

	public static function log_customer_updated(OsCustomerModel $customer, array $old_customer_data) {
		$data = [];
		$data['customer_id'] = $customer->id;
		$data['code'] = 'customer_updated';
		$data['description'] = wp_json_encode(['customer_data_vars' => ['new' => $customer->get_data_vars(), 'old' => $old_customer_data]]);
		OsActivitiesHelper::create_activity($data);

	}

	public static function log_agent_created(OsAgentModel $agent) {

	}

	public static function log_agent_updated(OsAgentModel $agent, array $old_agent) {

	}

	public static function log_service_created(OsServiceModel $service) {

	}

	public static function log_service_updated(OsServiceModel $service, array $old_service) {

	}
}