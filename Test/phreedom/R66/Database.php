<?php 
class R66_Database {
  
  /**
   * The mysql link identifier for the database connection
   * 
   * @var mysqli
   */
  private $_db = false;
  
  /**
   * The wordpress database table prefix
   * 
   * @var string
   */
  private $_prefix;
  
  /**
   * The database host name
   * 
   * @var string
   */
  private $_host;
  
  /**
   * The database username
   * 
   * @var string
   */
  private $_user;
  
  /**
   * The database password
   * 
   * @var string
   */
  private $_password;
  
  /**
   * The name of the database
   * 
   * @var string
   */
  private $_database;
  
  
  public function __construct($conn) {
    if(is_resource($conn)) {
      
    }
    elseif(get_class($conn) == 'stdClass') {
      $this->_host = $conn->host;
      $this->_username = $conn->username;
      $this->_password = $conn->password;
      $this->_database = $conn->database;
      $this->_prefix = $conn->prefix;
    }
    elseif(get_class($conn) == 'wpdb') {
      $this->_host = $conn->dbhost;
      $this->_username = $conn->dbuser;
      $this->_password = $conn->dbpassword;
      $this->_database = $conn->dbname;
      $this->_prefix = $conn->prefix;
      R66_Log::write('WPDB says the prefix is: ' . $conn->prefix);
    }
    $this->connect();
  }
  
  /**
   * Execute a query against the database
   * 
   * The $query parameter may either be a string containing a SQL statement or
   * it may be a R66_Query object.
   * 
   * @param mixed $query string or R66_Query
   * @return boolean or mysqli_result object
   */
  public function query($query) {
    if(is_object($query) && get_class($query) == 'R66_Query') {
      $query = $query->get_sql();
    }
    $result = empty($query) ? true : $this->_db->query($query);
    return $result;
  }
  
  public function transaction(array $queries) {
    $success = true;
    $this->_db->autocommit(false);
    foreach($queries as $query) {
      if(!$this->query($query)) {
        $success = false;
        R66_Log::write("SQL QUERY FAILED:\n$query\nError: " . $this->_db->error);
        break;
      }
    }
    $success ? $this->_db->commit() : $this->_db->rollback();
    $this->_db->autocommit(true);
    return $success;
  }
  
  public function get_connection_details() {
    $details = stdClass();
    $details->database = $this->_database;
    $details->username = $this->_username;
    $details->password = $this->_password;
    $details->host = $this->_host;
    return $details;
  }
  
  public function escape($value) {
    if(is_null($value)) {
      $escaped_value = 'NULL';
    }
    else {
      $escaped_value = $this->_db->real_escape_string($value);
      if(!is_numeric($value)) {
        $escaped_value = "'" . $escaped_value . "'";
      }
    }
    return $escaped_value;
  }
  
  public function last_id() {
    return $this->_db->insert_id;
  }
  
  private function connect() {
    if(!$this->_db) {
      $this->_db = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
    }
  }
  
}