<?php 
require_once '../bootstrap.php';

class Test_Query extends R66_Test {

  public function test_insert() {
    $expected = "insert into my_table (`col1`, `col2`, `col3`) values ('val1', 'val2', 12)";
    $data = array(
      'col1' => 'val1',
      'col2' => 'val2',
      'col3' => 12
    );
    $query = new R66_Query();
    $actual = $query->insert('my_table', $data)->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_update() {
    $expected = "update my_table set `col1` = 'val1', `col2` = 'val2', `col3` = 12 where `id` > 1 and `status` like '%live%'";
    $data = array(
      'col1' => 'val1',
      'col2' => 'val2',
      'col3' => 12
    );
    $query = new R66_Query();
    $query->update('my_table', $data)
          ->where('id', '>', 1)
          ->where('status', 'LIKE', '%live%');
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_delete() {
    $expected = "delete from my_table where `id` > 1 and `status` = 'deleted'";
    $query = new R66_Query();
    $query->delete('my_table')
          ->where('id', '>', 1)
          ->where('status', '=', 'deleted');
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_star() {
    $expected = "select * from my_table";
    $query = new R66_Query();
    $query->select('my_table');
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_columns_from_single_table() {
    $expected = 'select `col1`, `col2` from my_table';
    $query = new R66_Query();
    $query->select('my_table', array('col1', 'col2'));
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_columns_from_single_table_where() {
    $expected = 'select `col1`, `col2` from my_table where `id` = 1';
    $query = new R66_Query();
    $query->select('my_table', array('col1', 'col2'))->where('id', '=', 1);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_columns_from_single_table_where_multiple_and_conditions() {
    $expected = "select `col1`, `col2` from my_table where `id` = 1 and `name` = 'Test'";
    $query = new R66_Query();
    $query->select('my_table', array('col1', 'col2'))
          ->where('id', '=', 1)
          ->where('name', '=', 'Test');
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_multiple_tables_and_conditions() {
    $expected = "select `a.col1`, `b.col2` from table_a as a, table_b as b where a.id = b.a_id and `a.id` = 1";
    $query = new R66_Query();
    $query->select(array('table_a as a', 'table_b as b'), array('a.col1', 'b.col2'))
          ->join('a.id', 'b.a_id')
          ->where('a.id', '=', 1);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_QueryCondition() {
    $expected = "select * from my_table where `id` = 1 and `first_name` = 'Lee'";
    $cond_id   = R66_QueryCondition::factory('id', '=', 1);
    $cond_name = R66_QueryCondition::factory('first_name', '=', 'Lee');
    $query = new R66_Query();
    $query->select('my_table')->where($cond_id)->where($cond_name);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_grouped_conditions_by_and() {
    $expected = "select * from my_table where (`id` = 1 and `first_name` = 'Lee')";
    $cond_id   = R66_QueryCondition::factory('id', '=', 1);
    $cond_name = R66_QueryCondition::factory('first_name', '=', 'Lee');
    $query = new R66_Query();
    $query->select('my_table')->where(array($cond_id, $cond_name));
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_one_and_one_or() {
    $expected = "select * from my_table where `id` = 1 or `age` > 10";
    $cond_id  = R66_QueryCondition::factory('id', '=', 1);
    $cond_age = R66_QueryCondition::factory('age', '>', 10);
    $query = new R66_Query();
    $query->select('my_table')->where($cond_id)->or_where($cond_age);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_a_single_condition_and_a_group_of_conditions() {
    $expected = "select * from my_table where `id` > 0 or (`name` = 'Blue' and `age` > 20)";
    $cond_id   = R66_QueryCondition::factory('id', '>', 0);
    $cond_name = R66_QueryCondition::factory('name', '=', 'Blue');
    $cond_age  = R66_QueryCondition::factory('age', '>', 20);
    $query = new R66_Query();
    $query->select('my_table');
    $query->where($cond_id)->or_where(array($cond_name, $cond_age));
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_two_groups_of_conditions_combined_with_or() {
    $expected = "select * from my_table where (`age` > 20 and `gender` = 'male') or (`age` > 18 and `gender` = 'female')";
    $males      = R66_QueryCondition::factory('gender', '=', 'male');
    $females    = R66_QueryCondition::factory('gender', '=', 'female');
    $male_age   = R66_QueryCondition::factory('age', '>', 20);
    $female_age = R66_QueryCondition::factory('age', '>', 18);
    $query = new R66_Query();
    $query->select('my_table')
          ->where(array($male_age, $males))
          ->or_where(array($female_age, $females));
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_three_groups_of_conditions_combined_with_or() {
    $expected = "select * from my_table where (`age` > 20 and `gender` = 'male') or (`age` > 18 and `gender` = 'female') or (`age` > 20 and `name` = 'Blue')";
    $males     = R66_QueryCondition::factory('gender', '=', 'male');
    $females   = R66_QueryCondition::factory('gender', '=', 'female');
    $age_20    = R66_QueryCondition::factory('age', '>', 20);
    $age_18    = R66_QueryCondition::factory('age', '>', 18);
    $name_blue = R66_QueryCondition::factory('name', '=', 'Blue');

    $query = new R66_Query();
    $query->select('my_table')
          ->where(array($age_20, $males))
          ->or_where(array($age_18, $females))
          ->or_where(array($age_20, $name_blue));
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_limit() {
    $expected = "select * from my_table where `id` = 1 limit 0, 1";
    $query = new R66_Query();
    $query->select('my_table')->where('id', '=', 1)->limit(1);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_limit_and_offset() {
    $expected = "select * from my_table where `id` = 1 limit 10, 1";
    $query = new R66_Query();
    $query->select('my_table')->where('id', '=', 1)->limit(1, 10);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_order_by() {
    $expected = "select * from my_table where `id` > 0 order by `last_name`";
    $query = new R66_Query();
    $query->select('my_table')->where('id', '>', 0)->order('last_name');
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
  public function test_select_with_order_by_and_limit() {
    $expected = "select * from my_table where `id` > 0 order by `last_name` limit 0, 1";
    $query = new R66_Query();
    $query->select('my_table')->where('id', '>', 0)->order('last_name')->limit(1);
    $actual = $query->get_sql();
    $this->check($expected == $actual, $actual);
  }
  
}

Test_Query::run_tests();