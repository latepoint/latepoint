<?php

class OsInvoiceModel extends OsModel {
	public $id,
		$order_id,
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_ORDER_INVOICES;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}


	protected function params_to_save( $role = 'admin' ): array {
		$params_to_save = [
			'id',
			'order_id'
		];

		return $params_to_save;
	}


	protected function allowed_params( $role = 'admin' ): array {
		$allowed_params = [
			'id',
			'order_id'
		];

		return $allowed_params;
	}


	protected function properties_to_validate(): array {
		$validations = [
			'order_id' => [ 'presence' ],
		];

		return $validations;
	}
}