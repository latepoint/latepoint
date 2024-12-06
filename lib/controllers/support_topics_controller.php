<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsSupportTopicsController' ) ) :


	class OsSupportTopicsController extends OsController {

		function __construct() {
			parent::__construct();

			$this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'support_topics/';
		}

		function view(){
			$topic = sanitize_text_field($this->params['topic']);
			$topic = str_replace(['..', '/'], '', $topic);

			$available_topics = ['payment_request'];
			if(in_array($topic, $available_topics)){
				$this->vars['topic'] = $topic;
				$response_html = $this->render($this->views_folder.'view', 'none');
				$status = LATEPOINT_STATUS_SUCCESS;
			}else{
				$response_html = __('Not Found', 'latepoint');
				$status = LATEPOINT_STATUS_ERROR;
			}
			$this->send_json( [ 'status' => $status, 'message' => $response_html ] );

		}
	}


endif;