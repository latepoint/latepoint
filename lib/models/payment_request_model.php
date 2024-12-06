<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsPaymentRequestModel extends OsModel{
	var $id,
			$invoice_id,
			$order_id,
		$portion,
		$charge_amount,
		$due_at,
      $updated_at,
      $created_at;

	function __construct($id = false){
    parent::__construct();
    $this->table_name = LATEPOINT_TABLE_PAYMENT_REQUESTS;

    if($id){
      $this->load_by_id($id);
    }
  }


	protected function params_to_sanitize() {
		return [
			'charge_amount'        => 'money',
		];
	}

  protected function params_to_save($role = 'admin'){
    $params_to_save = [
			'id',
		'portion',
		'charge_amount',
		'due_at',
	    'invoice_id',
	    'order_id'
    ];
    return $params_to_save;
  }

  protected function allowed_params($role = 'admin'){
    $allowed_params = [
			'id',
		'portion',
		'charge_amount',
		'due_at',
	    'invoice_id',
	    'order_id'
    ];
    return $allowed_params;
  }


  protected function properties_to_validate(){
    $validations = [];
    return $validations;
  }
}