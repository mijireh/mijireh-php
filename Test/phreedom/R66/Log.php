<?php 

if(!defined('R66_DEBUG')) {
  define('R66_DEBUG', true);
}

class R66_Log {
  
  public static function write($data) {
    if(defined('R66_DEBUG') && defined('R66_LOG_FILE') && R66_DEBUG) {
      $tz = '- Server time zone ' . date_default_timezone_get();
      $date = date('m/d/Y g:i:s a');
      $header = "[LOG DATE: $date $tz]\n";
      $filename = R66_LOG_FILE; 
      file_put_contents($filename, $header . $data . "\n\n", FILE_APPEND);
    }
  }
  
}