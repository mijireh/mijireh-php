<?php 

class R66_Storm extends R66_Model {
  
  protected $_table;
  protected $_data;
  protected $_data_types;
  
  /**
   * Create a new Storm object.
   * 
   * The parameters are passed to the tie() function so that
   * classes can extend and override the constructor using the same
   * function signature yet still allowing the extended class to 
   * use a different number of variables.
   * 
   * @param string $table The database table name
   * @param int $id (Optional) The primary key of the table row to load
   */
  public function __construct() {
    call_user_func_array(array($this,'tie'), func_get_args());
  }
  
  /**
   * Tie the model to an underlying database table.  
   * 
   * An id may optionally be provided to tie the model to a specific row in the table.
   * 
   * @param string $table The database table name
   * @param int $id (Optional) The primary key of the table row to load
   * @return void
   */
  public function tie($table, $id=null) {
    $this->_table = $table;
    $this->_init();
    
    // Tie the model to a specific row if an id is provided
    if(isset($id) && is_numeric($id) && $id > 0) {
      $this->load($id);
    }
  }
  
  /**
   * Load the model with a row from the table with the given id (primary key)
   * If the load succeeds return true, otherwise false.
   * 
   * @param int $id The id in the primary key column of the table
   * @return boolean
   */
  public function load($id) {
    $is_loaded = false;
    if(is_numeric($id) && $id > 0) {
      $query = new R66_Query();
      $query->select($this->_table)->where('id', '=', $id);
      if($result = R66_DB::query($query)) {
        $row = $result->fetch_assoc();
        $this->copy_from($row);
        $is_loaded = true;
      }
    }
    return $is_loaded;
  }
  
  public function load_where() {
    $is_loaded = false;
    $condition = null;
    $order_by = null;
    $num_args = func_num_args();
    if($num_args == 1) {
      $condition = func_get_arg(0);
    }
    elseif($num_args == 2) {
      $condition = func_get_arg(0);
      $order_by = func_get_arg(1);
    }
    elseif($num_args == 3) {
      $condition = R66_QueryCondition::factory(func_get_arg(0), func_get_arg(1), func_get_arg(2));
    }
    elseif($num_args == 4) {
      $condition = R66_QueryCondition::factory(func_get_arg(0), func_get_arg(1), func_get_arg(2));
      $order_by = func_get_arg(3);
    }
    
    if($obj = $this->find_one($condition, $order_by)) {
      $this->copy_from($obj->get_data());
      $is_loaded = true;
    }
    
    return $is_loaded;
  }
  
  /**
   * Just like the Model copyFrom except it only copies scalar values
   */
  public function copy_from(array $data) {
    foreach($data as $key => $value) {
      if(is_scalar($value) && $this->field_exists($key)) {
        $this->$key = $value;
      }
    }
  }
  
  /**
   * Clear all the values in the model and delete the associated row in the table.
   * 
   * Return true if the storm is successfully erased, otherwise false
   * 
   * @return boolean
   */
  public function erase() {
    $ok = false;
    if($this->id > 0) {
      $sql = 'delete from ' . $this->_table . ' where `id` = ' . $this->id;
      R66_DB::query($sql);
      $this->clear();
      $ok = true;
    }
    return $ok;
  }
  
  public function find_where() {
    $objects = array();
    $condition = false;
    $num_args = func_num_args();
    if($num_args == 1) {
      $condition = func_get_arg(0);
    }
    elseif($num_args == 3) {
      $condition = R66_QueryCondition::factory(func_get_arg(0), func_get_arg(1), func_get_arg(2));
    }
    
    if($condition) {
      $query = new R66_Query();
      $query->select($this->_table)
            ->where($condition);
      if($result = R66_DB::query($query)) {
        $my_class = get_class($this);
        while($row = $result->fetch_assoc()) {
          $obj = new $my_class($this->_table);
          $obj->copy_from($row);
          $objects[] = $obj;
        }
      }
    }
    
    return count($objects) > 0 ? $objects : false;
  }
  
  public function find_one() {
    $object = false;
    $condition = false;
    $order_by = false;
    $num_args = func_num_args();
    if($num_args == 1) {
      $condition = func_get_arg(0);
    }
    elseif($num_args == 2) {
      $condition = func_get_arg(0);
      $order_by = func_get_arg(1);
    }
    elseif($num_args == 3) {
      $condition = R66_QueryCondition::factory(func_get_arg(0), func_get_arg(1), func_get_arg(2));
    }
    elseif($num_args == 4) {
      $condition = R66_QueryCondition::factory(func_get_arg(0), func_get_arg(1), func_get_arg(2));
      $order_by = func_get_arg(3);
    }
    
    if($condition) {
      $query = new R66_Query();
      $query->select($this->_table)
            ->where($condition)
            ->order($order_by)
            ->limit(1);
      $sql = $query->get_sql();
      if($result = R66_DB::query($query)) {
        $my_class = get_class($this);
        if($row = $result->fetch_assoc()) {
          $object = new $my_class($this->_table);
          $object->copy_from($row);
        }
      }
    }
    return $object;
  }
  
  public function save() {
    if(func_num_args() == 1) {
      $data = func_get_arg(0);
      if(is_array($data)) {
        $this->clear();
        $this->copy_from($data);
      }
    }
    return $this->id > 0 ? $this->_update() : $this->_insert();
  }
  
  
  // ===================================
  // = Private and Protected Functions =
  // ===================================
  
  protected function _init() {
    $this->_data = array();
    $this->_data_types = array();
    
    $sql = 'show columns from ' . $this->_table;
    $result = R66_DB::query($sql);
    if($result) {
      while($col_meta = $result->fetch_assoc()) {
        $default = ($col_meta['Key'] == 'PRI') ? null : '';
        $this->_data[$col_meta['Field']] = $default;

        $matches = array();
        $pattern = '/([^\(]+)(\(([^\(]+)\))*/';
        preg_match($pattern, $col_meta['Type'], $matches);

        $info = new stdClass();
        $info->type = isset($matches[1]) ? $matches[1] : false;
        $info->length = (isset($matches[3]) && is_numeric($matches[3]) ) ? $matches[3] : 0;

        if($info->type == "enum") {
          $opts = array();
          foreach(explode(',', $matches[3]) as $val) {
            $opts[] = trim($val, "'");
          }
          $info->options = $opts;
        }

        $this->_data_types[$col_meta['Field']] = $info;
      }
    }
  }
  
  protected function _insert() {
    $id = false;
    $query = new R66_Query();
    $data = $this->get_data();
    $query->insert($this->_table, $data);
    if(R66_DB::query($query)) {
      $this->id = R66_DB::last_id();
      $id = $this->id;
    }
    return $id;
  }
  
  protected function _update() {
    $id = false;
    $query = new R66_Query();
    $data = $this->get_data();
    $query->update($this->_table, $data);
    if(R66_DB::query($query)) {
      $id = $this->id;
    }
    return $id;
  }
  
}