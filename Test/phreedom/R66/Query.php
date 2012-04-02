<?php 

class R66_Query {
  
  private $_verb;
  private $_columns;
  private $_tables;
  private $_joins;
  private $_conditions;
  private $_order;
  private $_limit;
  private $_where_clause;
  private $_data;
  
  public function __construct() {
    $this->reset();
  }
  
  public function reset() {
    $this->_verb = null;
    $this->_columns = array();
    $this->_tables = array();
    $this->_joins = array();
    $this->_conditions = array();
    $this->_data = array();
    $this->_order = null;
    $this->_limit = null;
  }
  
  public function select($tables, $columns=null) {
    $this->_verb = 'select';
    $this->_tables = $tables;
    $this->_columns = $columns;
    return $this;
  }
  
  /**
   * Alias for the select() function
   */
  public function get($tables, $columns=null) {
    return $this->select($tables, $columns);
  }
  
  public function where() {
    $num_args = func_num_args();
    if($num_args == 1) {
      $condition = func_get_arg(0);
      if(is_array($condition)) {
        $this->_append_where_querycondition_array($condition);
      }
      elseif(get_class($condition) == 'R66_QueryCondition') {
        $this->_append_where_querycondition($condition);
      }
    }
    elseif($num_args==3) {
      $this->_append_where(func_get_arg(0), func_get_arg(1), func_get_arg(2));
    }
    return $this;
  }
  
  public function or_where() {
    $num_args = func_num_args();
    if($num_args == 1) {
      $condition = func_get_arg(0);
      if(is_array($condition)) {
        $this->_append_where_querycondition_array($condition, 'or');
      }
      elseif(get_class($condition) == 'R66_QueryCondition') {
        $this->_append_where_querycondition($condition, 'or');
      }
    }
    elseif($num_args==3) {
      $this->_append_where(func_get_arg(0), func_get_arg(1), func_get_arg(2), 'or');
    }
    return $this;
  }
  
  public function join($col1, $col2) {
    $this->_joins[] = $col1 . ' = ' . $col2;
    return $this;
  }
  
  public function limit($limit, $offset=0) {
    $this->_limit = "limit $offset, $limit";
    return $this;
  }
  
  public function order($column_name) {
    if($column_name) {
      $this->_order = "order by `$column_name`";
    }
    return $this;
  }
  
  public function get_sql() {
    return $this->__toString();
  }
  
  public function insert($table, array $data) {
    $this->_verb = 'insert';
    $this->_tables = $table;
    $this->_data = $data;
    return $this;
  }
  
  public function update($table, array $data) {
    $this->_verb = 'update';
    $this->_tables = $table;
    $this->_data = $data;
    return $this;
  }
  
  public function delete($table) {
    $this->_verb = 'delete';
    $this->_tables = $table;
    return $this;
  }
  
  public function __toString() {
    $sql = false;
    switch($this->_verb) {
      case 'select':
        $sql = $this->_select_query();
        break;
      case 'insert':
        $sql = $this->_insert_query();
        break;
      case 'update':
        $sql = $this->_update_query();
        break;
      case 'delete':
        $sql = $this->_delete_query();
        break;
    }
    return $sql;
  }
  
  
  // ===============================
  // = Private Function Begin Here =
  // ===============================
  
  private function _append_where($column, $operator, $value, $combine_by='and') {
     $condition = R66_QueryCondition::factory($column, $operator, $value);
     $this->_append_where_querycondition($condition, $combine_by);
  }
  
  private function _append_where_querycondition(R66_QueryCondition $condition, $combine_by='and') {
    $condition_group = new stdClass();
    $condition_group->condition = $condition;
    $condition_group->combine_by = $combine_by;
    $this->_conditions[] = $condition_group;
  }
  
  private function _append_where_querycondition_array(array $conditions, $combine_by='and') {
    $condition_groups = array();
    foreach($conditions as $condition) {
      $condition_group = new stdClass();
      $condition_group->condition = $condition;
      $condition_group->combine_by = 'and';
      $condition_groups[] = $condition_group;
    }
    
    $condition_group = new stdClass();
    $condition_group->condition = $condition_groups;
    $condition_group->combine_by = $combine_by;
    $this->_conditions[] = $condition_group;
  }
  
  private function _select_query() {
    $columns = '*';
    
    if(is_array($this->_columns)) {
      $columns = $this->_columns;
      foreach($columns as $index => $column) {
        $columns[$index] = '`' . $column . '`';
      }
      $columns = implode(', ', $columns);
    }
    
    $tables = $this->_build_tables();
    $sql = "select $columns from $tables" . $this->_build_where_clause();
    if($this->_order) {
      $sql .= ' ' . $this->_order;
    }
    
    if($this->_limit) {
      $sql .= ' ' . $this->_limit;
    }
    return trim($sql);
  }
  
  private function _insert_query() {
    $table = $this->_build_tables();
    $columns = array();
    foreach($this->_data as $column => $value) {
      $columns[] = '`' . $column . '`';
    }
    $columns = implode(', ', $columns);
    $values = implode(', ', array_map(array('R66_DB', 'escape'), $this->_data));
    $sql = "insert into $table ($columns) values ($values)";
    return trim($sql);
  }
  
  /**
   * If no conditions are provided, attempt to update based on the id column.
   */
  private function _update_query() {
    $where = '';
    $pairs = array();
    foreach($this->_data as $col => $value) {
      $value = R66_DB::escape($value);
      $pairs[] = '`' . $col . '`' . ' = ' . $value;
    }
    $combined_pairs = implode(', ', $pairs);
    $table = $this->_build_tables();
    $sql = "update $table set $combined_pairs";
    
    if(count($this->_conditions)) {
      $where = $this->_build_where_clause();
    }
    elseif(isset($this->_data['id']) && $this->_data['id'] > 0) {
      $where = ' where id = ' . $this->_data['id'];
    }
    else {
      R66_Log::write("R66_Query failed to compile. Update query has no where clause.\n" . $sql);
    }
    
    if(!empty($where)) {
      $sql .= $where;
      return trim($sql);
    }
    
  }
  
  public function _delete_query() {
    $table = $this->_build_tables();
    $sql = "delete from $table" . $this->_build_where_clause();
    return $sql;
  }
  
  private function _build_tables() {    
    $tables = $this->_tables;
    if(is_array($tables)) {
      $tables = implode(', ', $this->_tables);
    }
    return $tables;
  }
  
  private function _build_joins() {
    $segment = '';
    if(count($this->_joins)) {
      $segment = ' where ' . implode(' and ', $this->_joins);
    }
    return $segment;
  }
  
  public function _append_where_segment($single_condition_group, $include_connector=false) {
    $condition = $single_condition_group->condition;
    $connector = $include_connector ? ' ' . $single_condition_group->combine_by . ' ' : '';
    $segment = $connector . '`' . $condition->column . '`' . ' ' . $condition->operator . ' ' . $condition->value;
    return $segment;
  }
  
  public function _build_where_clause() {
    $where_clause = '';
    $joins = $this->_build_joins();
    
    if(count($this->_conditions)) {
      $segment = '';
      $condition_groups = $this->_conditions;
      $use_group_connector = 0;
      foreach($condition_groups as $condition_group) {
        if(is_array($condition_group->condition)) {
          $use_subgroup_connector = 0;
          
          if($use_group_connector) {
            $segment .= ' ' . $condition_group->combine_by . ' ';
          }
          $segment .= '(';
          foreach($condition_group->condition as $cg) {
            $segment .= $this->_append_where_segment($cg, $use_subgroup_connector);
            $use_subgroup_connector++;
          }
          $segment .= ')';
        }
        else {
          $segment .= $this->_append_where_segment($condition_group, $use_group_connector);
        }
        $use_group_connector++;
      }
      
      $where_clause = empty($joins) ? ' where ' : $joins . ' and ';
      $where_clause .= $segment;
    }
    
    return $where_clause;
  }
  
}