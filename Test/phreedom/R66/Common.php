<?php 

class R66_Common {
  
  /**
   * Return true if the provided array is an associative array.
   * 
   * @param array $array The array to inspect
   * @return boolean True if array is assoc
   */ 
  public static function is_assoc($array) {  
    return is_array($array) && !is_numeric(implode('', array_keys($array)));  
  }
  
  /**
	 * Return a random string that contains only numbers or uppercase letters.
	 * The default length of the string is 14 characters.
	 * 
	 * @param int (Optional) $length The number of characters in the string. Default: 14
	 * @return string
	 */
	public static function rand_string($length = 14) {
	  $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for($i=0; $i<$length; $i++) {
      $loc = mt_rand(0, strlen($chrs)-1);
      $string .= $chrs[$loc];
    }
	  return $string;
	}
	
	/**
	 * Return an id that is the concatenation of microseconds and seconds.
	 * 
	 * An example of the output is: 1330795708025685300 (19 characters)
	 * @return string
	 */
	public static function micro_id() {
	  $str = explode(' ', microtime(false));
    $str = $str[1] . $str[0];
    $str = preg_replace('/\D/', '', $str);
	  return $str;
	}
	
	/**
	 * Return the scubbed value from the source array for the given key.
	 * If the given $key is not in the source array, return NULL
	 * If the source parameter is not provided, use the $_REQUEST array
	 * 
	 * @param string $key
	 * @param array (Optional) $source 
	 * @return mixed
	 */
	public static function scrub($key, $source=$_REQUEST) {
    $value = null;
    if(isset($source[$key])) {
      $value = self::deep_clean($source[$key]);
    }
    return $value;
  }
  
  public static function deep_clean(&$data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        if(is_array($value)) {
          $data[$key] = self::deep_clean($value);
        }
        else {
          $value = strip_tags($value);
          $data[$key] = self::scrub_value($value);
        }
      }
    }
    else {
      $data= strip_tags($data);
      $data = self::scrub_value($data);
    }
    return $data;
  }
  
  private static function scrub_value($value) {
    $value = preg_replace('/[<>\\\\\/:;]/', '', $value);
    return $value;
  }
	
}