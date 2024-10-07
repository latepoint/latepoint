<?php

class OsAgentMetaModel extends OsMetaModel{
  function __construct($object_id = false){
    $this->table_name = LATEPOINT_TABLE_AGENT_META;
    parent::__construct($object_id);
  }
}