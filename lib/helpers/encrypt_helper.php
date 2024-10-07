<?php 
class OsEncryptHelper {


  public static function encrypt_value($value){
    return openssl_encrypt($value, 'aes-256-ecb', LATEPOINT_ENCRYPTION_KEY);
  }
  
  public static function decrypt_value($value){
    return openssl_decrypt($value, 'aes-256-ecb', LATEPOINT_ENCRYPTION_KEY);
  }


}