<?php 

class R66_Model {
  
  /**
   * An array representation of a database table row
   * 
   * @var array
   */
  protected $_data;
  
  /**
   * If the data in the model has been validated the value is true. 
   * If the data validation fails or the validate() function has never been called 
   * the value is false. Data is assumed to be invalid until proven otherwise.
   * 
   * @var boolean
   */
  protected $_validated = false;
  
  /**
   * Construct a new model class.
   * 
   * If the $data param is an associative array, then $_data is set to the provided array.
   * If the $data param is an array, the $_data is set to an associative array where the 
   * array keys are the values in the provided array and the values are all empty strings.
   * 
   * @param array $data The data array to use for storing attributes
   * @return void
   */
  public function __construct() {
    if($data = func_get_arg(0)) {
      if(is_array($data)) {
        if(Common::isAssoc($data)) {
          $this->_data = $data;
        }
        else {
          $this->_data = array();
          foreach($data as $key) {
            $this->_data[$key] = '';
          }
        }
      }
    }
  }
  
  /**
   * Set the value of one of the keys in the private $_data array.
   * 
   * @param string $key The key in the $_data array
   * @param string $value The value to assign to the key
   * @return boolean
   */
  public function __set($key, $value) {
    $success = false;
    if(array_key_exists($key, $this->_data)) {
      $this->_data[$key] = $value;
      $success = true;
    }
    return $success;
  }
  
  /**
   * Get the value for the key from the private $_data array.
   * 
   * Return false if the requested key does not exist
   * 
   * @param string $key The key from the $_data array
   * @return mixed
   */
  public function __get($key) {
    $value = false;
    if(array_key_exists($key, $this->_data)) {
      $value = $this->_data[$key];
    }
    
    /*
    elseif(method_exists($this, $key)) {
      $value = call_user_func_array(array($this, $key), func_get_args());
    }
    */
    
    return $value;
  }
  
  /**
   * Return true if the given $key in the private $_data array is set
   * 
   * @param string $key
   * @return boolean   
   */
  public function __isset($key) {
    return isset($this->_data[$key]);
  }
  
  /**
   * Set the value of the $_data array to null for the given key. 
   * 
   * @param string $key
   * @return void
   */
  public function __unset($key) {
    if(array_key_exists($key, $this->_data)) {
      $this->_data[$key] = null;
    }
  }
  
  /**
   * Return the private $_data array
   * 
   * @return mixed
   */
  public function get_data() {
    return $this->_data;
  }
  
  /**
   * Return true if the given $key exists in the private $_data array
   * 
   * @param string $key
   * @return boolean
   */
  public function field_exists($key) {
    return array_key_exists($key, $this->_data);
  }
  
  /**
   * Reset all the values of the model to empty strings except for the id which is set to null
   * 
   * @return void
   */
  public function clear() {
    foreach($this->_data as $key => $value) {
      $value = ($key == 'id') ? null : '';
      $this->_data[$key] = $value;
    }
  }
  
  /**
   * Populate the data for the model from the given assoc array by matching the keys
   * of the private $_data array with the keys in the given assoc array.
   * 
   * The model is not cleared before setting the new values. Therefore, if the model 
   * has an id value and the array does not contain an id key, then the original id 
   * value in the model is not changed.
   * 
   * @param array The source data
   * @return void
   */
  public function copy_from(array $data) {
    foreach($data as $key => $value) {
      if($this->field_exists($key)) {
        $this->$key = $value;
      }
    }
  }  
}