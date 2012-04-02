<?php 
class R66_QueryCondition {
  
  protected $_column;
  protected $_operator;
  protected $_value;
  protected $_combine_by;
  
  private $_allowed_operators = array(
    '=', '!=', '<>', '>=', '>', '<', '<=', '%', 'MOD', 
    'is', 'is null', 'is not', 'is not null', 'not', 
    'like', 'not like', 'in', 'not in', 'between', 'not between',
    'not regexp', 'regexp', 'rlike', 'sounds like'
  );
  
  public static function factory($column, $operator, $value) {
    $condition = new R66_QueryCondition($column, $operator, $value);
    return $condition;
  }
  
  public function __construct($column, $operator, $value) {
    $operator = strtolower($operator);
    if(in_array($operator, $this->_allowed_operators)) {
      $this->_column = $column;
      $this->_operator = $operator;
      $this->_value = R66_DB::escape($value);
    }
    else {
      throw new Exception("Illegal query condition operator");
    }
  }
  
  public function __get($key) {
    $value = false;
    switch($key) {
      case 'column':
      case 'col':
        $value = $this->_column;
      break;
      
      case 'operator':
      case 'op':
        $value = $this->_operator;
      break;
      
      case 'value':
      case 'val':
        $value = $this->_value;
      break;
    }
    return $value;
  }
  
}