<?php

class OsServiceMetaModel extends OsMetaModel{
  function __construct($object_id = false){
    $this->table_name = LATEPOINT_TABLE_SERVICE_META;
    parent::__construct($object_id);
  }
}