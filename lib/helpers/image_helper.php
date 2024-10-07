<?php 

class OsImageHelper {

  public static function get_image_url_by_id($attachment_id, $size = 'thumbnail', $placeholder = false){
    $image = $attachment_id ? wp_get_attachment_image_src($attachment_id, $size) : false;
    if($image){
      $url = $image[0];
    }else{
      if($placeholder){
        $url = $placeholder;
      }else{
        $url = LATEPOINT_IMAGES_URL . 'default-avatar.jpg';
      }
    }
    return $url;
  }


  public static function get_agent_avatar($agent_id){
    if($agent_id && has_post_thumbnail($agent_id)){
      return self::get_image_url_by_id(get_post_thumbnail_id($agent_id), 'thumbnail', LATEPOINT_IMAGES_URL . 'default-avatar.jpg');
    }else{
      return LATEPOINT_IMAGES_URL . 'default-avatar.jpg';
    }
  }


  public static function get_customer_avatar($customer_id){
    if($customer_id && has_post_thumbnail($customer_id)){
      return self::get_image_url_by_id(get_post_thumbnail_id($customer_id), 'thumbnail', LATEPOINT_IMAGES_URL . 'default-avatar.jpg');
    }else{
      return LATEPOINT_IMAGES_URL . 'default-avatar.jpg';
    }
  }
  
}