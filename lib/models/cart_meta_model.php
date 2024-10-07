<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsCartMetaModel extends OsMetaModel{
  function __construct($object_id = false){
    $this->table_name = LATEPOINT_TABLE_CART_META;
    parent::__construct($object_id);
  }
}