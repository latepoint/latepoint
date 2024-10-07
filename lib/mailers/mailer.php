<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsMailer' ) ) :

class OsMailer {

  protected $views_folder = LATEPOINT_VIEWS_MAILERS_ABSPATH,
  $vars = array(),
  $layout = 'mailer',
  $headers = [];

  public static function send_email($to, $subject, $message, $headers){
    if(!OsSettingsHelper::is_email_allowed()) return true;
    return wp_mail($to, $subject, $message, $headers);
  }

  function get_headers(){
    return $this->headers;
  }

  function get_view_uri($view_name){
    return $this->views_folder.$view_name.'.php';
  }

  function __construct(){
    $this->headers[] = 'Content-Type: text/html; charset=UTF-8';
    $this->headers[] = 'From: '.OsNotificationsHelper::get_email_headers_from();
  }

  function set_layout($layout = 'mailer'){
    if(isset($this->params['layout'])){
      $this->layout = $this->params['layout'];
    }else{
      $this->layout = $layout;
    }
  }

  function get_layout(){
    return $this->layout;
  }

  function render($view, $extra_vars = array()){
    $view = $this->get_view_uri($view);
    extract($this->vars);
    extract($extra_vars);
    ob_start();
    if($this->get_layout() != 'none'){
      // rendering layout, view variable will be passed and used in layout file
      include LATEPOINT_VIEWS_LAYOUTS_ABSPATH . OsRouterHelper::add_extension($this->get_layout(), '.php');
    }else{
      include OsRouterHelper::add_extension($view, '.php');
    }
    $response_html = ob_get_clean();
    return $response_html;
  }

}

endif;