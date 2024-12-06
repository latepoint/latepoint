<?php

class OsInvoiceModel extends OsModel {
	public $id,
		$order_id,
		$invoice_number,
		$data,
		$charge_amount,
		$payment_portion,
		$status,
		$access_key,
		$due_at,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_ORDER_INVOICES;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	public function get_order(): OsOrderModel{
		return new OsOrderModel($this->order_id);
	}


	protected function params_to_sanitize() {
		return [
			'charge_amount'        => 'money',
		];
	}


	protected function before_save() {

		if ( empty( $this->invoice_number ) ) {
			$this->invoice_number = strtoupper( OsUtilHelper::random_text( 'distinct', 8 ) );
		}
		if ( empty( $this->status ) ) {
			$this->status = LATEPOINT_INVOICE_STATUS_NOT_PAID;
		}
		if ( empty( $this->due_at ) ) {
			$this->due_at = OsTimeHelper::now_datetime_in_format( "Y-m-d H:i:s" );
		}
		if ( empty( $this->access_key ) ) {
			$this->access_key = OsUtilHelper::generate_uuid();
		}
	}

	public function get_access_url(): string{
		return OsRouterHelper::build_admin_post_link( [ 'invoices', 'view_by_key' ], [ 'key' => $this->access_key ] );
	}

	public function get_pay_url(): string{
		return OsRouterHelper::build_admin_post_link( [ 'invoices', 'summary_before_payment' ], [ 'key' => $this->access_key ] );
	}


	protected function params_to_save( $role = 'admin' ): array {
		$params_to_save = [
			'id',
			'order_id',
			'invoice_number',
			'payment_portion',
			'status',
			'data',
			'charge_amount',
			'access_key',
			'due_at',
		];

		return $params_to_save;
	}


	protected function allowed_params( $role = 'admin' ): array {
		$allowed_params = [
			'id',
			'order_id',
			'invoice_number',
			'payment_portion',
			'status',
			'data',
			'charge_amount',
			'access_key',
			'due_at',
		];

		return $allowed_params;
	}


	protected function properties_to_validate(): array {
		$validations = [
			'order_id' => [ 'presence' ],
			'status' => [ 'presence' ],
		];

		return $validations;
	}
}