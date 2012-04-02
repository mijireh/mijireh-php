<?php 

class R66_DB {
  
  public static $last_query;
  
  private static $_db = false;
  
  public static function init() {
    if(!self::$_db) {
      global $db_credentials;
      self::$_db = new R66_Database($db_credentials);
    }
  }
  
  public static function query($sql) {
    self::init();
    self::$last_query = $sql;
    return self::$_db->query($sql);
  }
  
  public static function last_query() {
    return self::$last_query;
  }
  
  public static function transaction($sql_queries) {
    self::init();
    return self::$_db->transaction($sql_queries);
  }
  
  public static function run_file($file_name) {
    $ok = false;
    if(file_exists($file_name)) {
      $sql = file_get_contents($file_name);
      $sql = explode(';', $sql);
      for($i=0; $i<count($sql); $i++) {
        $sql[$i] = trim($sql[$i]);
      }
      $ok = self::transaction($sql);
    }
    else {
      R66_Log::write("Unable to run sql because file does not exist: $file_name");
    }
    return $ok;
  }
  
  public static function escape($value) {
    self::init();
    return self::$_db->escape($value);
  }
  
  public static function last_id() {
    $id = false;
    if(self::$_db) {
      $id = self::$_db->last_id();
    }
    return $id;
  }
  
}