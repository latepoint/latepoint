<?php

class OsCustomerMetaModel extends OsMetaModel{
  function __construct($object_id = false){
    $this->table_name = LATEPOINT_TABLE_CUSTOMER_META;
    parent::__construct($object_id);
  }
}