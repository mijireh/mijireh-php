<?php 

class R66_Validators {
  
  /**
	 * Return true if the $haystack starts with the $needle
	 * 
	 * @param string $haystack The source string to inspect
	 * @param string $needle The beginning string to look for
	 * @return boolean
	 */
	public static function starts_with($haystack, $needle) {
	  $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
	}
	
	/**
	 * Return true if the $haystack ends with the $needle
	 * 
	 * @param string $haystack The source string to inspect
	 * @param string $needle The ending string to look for
	 * @return boolean
	 */
	public static function ends_with($haystack, $needle) {
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
  }
}