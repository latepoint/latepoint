<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsAgentMailer' ) ) :


  class OsAgentMailer extends OsMailer {



    function __construct(){
      parent::__construct();
      $this->views_folder = LATEPOINT_VIEWS_MAILERS_ABSPATH . 'agent/';
    }

	}

endif;