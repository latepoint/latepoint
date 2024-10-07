<?php

class OsBookingMetaModel extends OsMetaModel{
  function __construct($object_id = false){
    $this->table_name = LATEPOINT_TABLE_BOOKING_META;
    parent::__construct($object_id);
  }
}