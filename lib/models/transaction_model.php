<?php

class OsTransactionModel extends OsModel {
	public $id,
		$token,
		$invoice_id,
		$order_id,
		$customer_id,
		$processor,
		$payment_method,
		$payment_portion,
		$kind,
		$amount,
		$status,
		$notes,
		$updated_at,
		$created_at;

	function __construct($id = false) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_TRANSACTIONS;
		$this->nice_names = ['token' => __('Confirmation Number', 'latepoint')];

		if ($id) {
			$this->load_by_id($id);
		}
	}

	public function properties_to_query(): array{
		return [
			'payment_method' => __('Payment Method', 'latepoint'),
			'payment_portion' => __('Payment Portion', 'latepoint'),
			'kind' => __('Type', 'latepoint'),
		];
	}

	public function generate_data_vars(): array {
		return [
			'id' => $this->id,
			'order_id' => $this->order_id,
			'token' => $this->token,
			'customer_id' => $this->customer_id,
			'processor' => $this->processor,
			'payment_method' => $this->payment_method,
			'payment_portion' => $this->payment_portion_nice_name,
			'kind' => $this->kind,
			'status' => $this->status,
			'amount' => OsMoneyHelper::format_price($this->amount),
			'notes' => $this->notes,
		];
	}


	public function filter_allowed_records(): OsModel{
		if(!OsRolesHelper::are_all_records_allowed()){
			// join orders table to filter allowed transactions
			$this->join(LATEPOINT_TABLE_BOOKINGS, ['id' => $this->table_name.'.order_id']);
			$this->select(LATEPOINT_TABLE_TRANSACTIONS.'.*');
			if(!OsRolesHelper::are_all_records_allowed('agent')){
				$this->select(LATEPOINT_TABLE_BOOKINGS.'.agent_id');
				$this->filter_where_conditions([LATEPOINT_TABLE_BOOKINGS.'.agent_id' => OsRolesHelper::get_allowed_records('agent')]);
			}
			if(!OsRolesHelper::are_all_records_allowed('location')){
				$this->select(LATEPOINT_TABLE_BOOKINGS.'.location_id');
				$this->filter_where_conditions([LATEPOINT_TABLE_BOOKINGS.'.location_id' => OsRolesHelper::get_allowed_records('location')]);
			}
			if(!OsRolesHelper::are_all_records_allowed('service')){
				$this->select(LATEPOINT_TABLE_BOOKINGS.'.service_id');
				$this->filter_where_conditions([LATEPOINT_TABLE_BOOKINGS.'.service_id' => OsRolesHelper::get_allowed_records('service')]);
			}
		}
		return $this;
	}

	protected function params_to_sanitize() {
		return ['amount' => 'money'];
	}


	public function get_payment_portion_nice_name($default = '') {
		$payment_portions = OsPaymentsHelper::get_payment_portions_list();
		$nice_name = (!empty($this->payment_portion) && isset($payment_portions[$this->payment_portion])) ? $payment_portions[$this->payment_portion] : $default;
		return $nice_name;
	}


	protected function get_customer(): OsCustomerModel {
		$order = $this->get_order();
		return $order->customer;
	}

	protected function get_order(): OsOrderModel {
		if ($this->order_id) {
			if (!isset($this->order) || (isset($this->order) && ($this->order->id != $this->order_id))) {
				$this->order = new OsOrderModel($this->order_id);
			}
		} else {
			$this->order = new OsOrderModel();
		}
		return $this->order;
	}

	protected function params_to_save($role = 'admin'): array {
		$params_to_save = array('id',
			'token',
			'invoice_id',
			'order_id',
			'processor',
			'customer_id',
			'payment_method',
			'payment_portion',
			'kind',
			'amount',
			'status',
			'notes');
		return $params_to_save;
	}


	protected function allowed_params($role = 'admin'): array {
		$allowed_params = array('id',
			'token',
			'invoice_id',
			'order_id',
			'processor',
			'customer_id',
			'payment_method',
			'payment_portion',
			'kind',
			'amount',
			'status',
			'notes');
		return $allowed_params;
	}


	protected function properties_to_validate() :array {
		$validations = array(
			'order_id' => array('presence'),
		);
		return $validations;
	}
}