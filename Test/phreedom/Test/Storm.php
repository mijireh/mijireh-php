<?php 
require_once '../bootstrap.php';

class Test_Storm extends R66_Test {
  
  public function before_tests() {
    if(! R66_DB::run_file('Data/test_storm.sql')) {
      echo "Unable to initialize test data.";
      $this->log_file_check();
      die();
    }
  }
  
  public function test_create_empty_storm() {
    $expected = array(
      'id' => null,
      'first_name' => '',
      'last_name' => ''
    );
    $storm = new R66_Storm('my_test_table');
    $data = $storm->get_data();
    $message = "Expected: " . print_r($expected, true) . "\nActual: " . print_r($data, true);
    $this->check($expected == $data, $message);
  }
  
  public function test_load_storm() {
    $expected = array(
      'id' => 1,
      'first_name' => 'Test',
      'last_name' => 'Person'
    );
    $storm = new R66_Storm('my_test_table');
    $storm->load(1);
    $data = $storm->get_data();
    $message = "Expected: " . print_r($expected, true) . "\nActual: " . print_r($data, true);
    $this->check($expected == $data, $message);
  }
  
  public function test_load_storm_in_constructor() {
    $expected = array(
      'id' => 1,
      'first_name' => 'Test',
      'last_name' => 'Person'
    );
    $storm = new R66_Storm('my_test_table', 1);
    $data = $storm->get_data();
    $message = "Expected: " . print_r($expected, true) . "\nActual: " . print_r($data, true);
    $this->check($expected == $data, $message);
  }
  
  public function test_finding_multiple_objects_with_one_condition() {
    $success = true;
    $error = null;
    $storm = new R66_Storm('my_test_table');
    $results = $storm->find_where('last_name', '=', 'Blue');
    
    if(count($results) == 2) {
      foreach($results as $obj) {
        if(get_class($obj) == 'R66_Storm') {
          if($obj->last_name == 'Blue') {
            // looks good
            // echo "Name: " . $obj->first_name . ' ' . $obj->last_name . "\n";
          }
          else {
            $success = false;
            $error = "Last name should be Blue but was " . $obj->last_name;
          }
        }
        else {
          $success = false;
          $error = "Class should be R66_Storm but was " . get_class($obj);
          break;
        }
      }
    }
    else {
      $success = false;
      $error = 'Expecting to get 2 results but got: ' . count($results);
    }
    
    $this->check($success, $error);
  }
  
  public function test_find_one_object_with_one_condition() {
    $success = false;
    $error = null;
    $storm = new R66_Storm('my_test_table');
    $obj = $storm->find_one('last_name', '=', 'Blue');
    if($obj) {
      if(get_class($obj) == 'R66_Storm') {
        if($obj->last_name == 'Blue' && $obj->first_name = 'Lee') {
          $success = true;
        }
        else {
          $success = false;
          $name = $obj->first_name . ' ' . $obj->last_name;
          $error = "Looking for Lee Blue but found $name";
        }
      }
      else {
        $success = false;
        $error = 'The class should be R66_Storm but was ' . get_class($obj);
      }
    }
    else {
      $success = false;
      $error = 'failed to find object where id = 1';
    }
    $this->check($success, $error);
  }
  
  public function test_find_one_object_with_one_condition_ordered() {
    $success = false;
    $error = null;
    $storm = new R66_Storm('my_test_table');
    $obj = $storm->find_one('last_name', '=', 'Blue', 'first_name');
    if($obj) {
      if(get_class($obj) == 'R66_Storm') {
        if($obj->last_name == 'Blue' && $obj->first_name = 'Emily') {
          $success = true;
        }
        else {
          $success = false;
          $name = $obj->first_name . ' ' . $obj->last_name;
          $error = "Looking for Emily Blue but found $name";
        }
      }
      else {
        $success = false;
        $error = 'The class should be R66_Storm but was ' . get_class($obj);
      }
    }
    else {
      $success = false;
      $error = 'failed to find object where id = 1';
    }
    $this->check($success, $error);
  }
  
  public function test_find_one_object_with_multiple_conditions() {
    $success = false;
    $error = null;
    $storm = new R66_Storm('my_test_table');
    $first_name_lee = R66_QueryCondition::factory('first_name', '=', 'Lee');
    $last_name_blue = R66_QueryCondition::factory('last_name', '=', 'Blue');
    $obj = $storm->find_one(array($first_name_lee, $last_name_blue));
    if($obj) {
      if(get_class($obj) == 'R66_Storm') {
        if($obj->first_name = 'Lee' && $obj->last_name == 'Blue') {
          $success = true;
        }
        else {
          $success = false;
          $name = $obj->first_name . ' ' . $obj->last_name;
          $error = "Looking for Lee Blue but found $name";
        }
      }
      else {
        $success = false;
        $error = 'The class should be R66_Storm but was ' . get_class($obj);
      }
    }
    else {
      $success = false;
      $error = 'failed to find object where id = 1';
    }
    $this->check($success, $error);
  }
  
  public function test_load_storm_with_condition_and_order_by() {
    $success = false;
    $storm = new R66_Storm('my_test_table');
    $storm->load_where('last_name', '=', 'Blue', 'first_name');
    if($storm->last_name == 'Blue' && $storm->first_name == 'Emily') {
      $success = true;
    }
    else {
      $success = false;
      $name = $storm->first_name . ' ' . $storm->last_name;
      $error = "Looking for Emily Blue but found $nane";
    }
    $this->check($success, $error);
  }
  
  public function test_when_load_fails_it_returns_false() {
    $storm = new R66_Storm('my_table');
    $is_loaded = $storm->load(5000);
    $this->check(!$is_loaded, 'Should have been a failed load');
  }
  
  public function test_when_load_where_fails_it_returns_false() {
    $storm = new R66_Storm('my_table');
    $is_loaded = $storm->load_where('last_name', '=', 'missing');
    $this->check(!$is_loaded, 'Should have been a failed load');
  }
  
  public function test_setting_storm_attributes() {
    $success = false;
    $storm =  new R66_Storm('my_test_table');
    $storm->first_name = 'Test';
    $storm->last_name = 'Person';
    if($storm->first_name == 'Test' && $storm->last_name == 'Person') {
      $success = true;
    }
    $this->check($success, 'Unable to set attributes');
  }
  
  public function test_saving_a_new_storm_should_set_the_storm_id() {
    $storm = new R66_Storm('my_test_table');
    $storm->first_name = 'New';
    $storm->last_name = 'Storm';
    $storm->save();
    $this->check($storm->id > 0, 'Unable to save storm');
  }
  
  public function test_updating_a_storm() {
    $success = false;
    $storm = new R66_Storm('my_test_table');
    $storm->first_name = 'Temporary';
    $storm->last_name = 'Storm';
    if($id = $storm->save()) {
      $storm->first_name = 'Updated';
      $storm->save();
      $storm->load($id);
      if($storm->first_name == 'Updated') {
        $success = true;
      }
      else {
        $error = 'The storm appears not to have updated: ' . $storm->first_name;
      }
    }
    else {
      $error = 'Unable to create a storm to update';
    }
    
    $this->check($success, $error);
  }
  
  public function test_erasing_a_storm() {
    $success = false;
    $storm = new R66_Storm('my_test_table');
    $storm->first_name = 'Delete';
    $storm->last_name = 'Me';
    $id = $storm->save();
    
    $storm->load($id);
    $storm->erase();
    
    $found = $storm->find_one('id', '=', $id);
    $this->check(!$found, 'The storm was found but should have been deleted');
  }
  
}

Test_Storm::run_tests();