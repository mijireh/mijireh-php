<?php 
require_once 'bootstrap.php';

class Test_Item extends R66_Test {
  
  private function _get_item_data() {
    $data = array(
      'name' => 'Example Item',
      'price' => 10.00,
      'quantity' => 1,
      'sku' => 'ex-1'
    );
    return $data;
  }
  
  public function test_copy_from_an_array_into_an_item() {
    $passed = false;
    $error_message = '';
    $item = new Mijireh_Item();
    $item->copy_from($this->_get_item_data());
    if($item->validate()) {
      $passed = true;
    }
    else {
      $error_message = $item->get_error_lines();
    }
    $this->check($passed, $error_message);
  }
  
  public function test_item_validation_should_fail_if_name_is_empty() {
    $passed = false;
    $error_message = '';
    $item = new Mijireh_Item();
    $item->copy_from($this->_get_item_data());
    $item->name = null;
    if(!$item->validate()) {
      $errors = $item->get_errors();
      foreach($errors as $error) {
        if($error == 'item name is required.') {
          $passed = true;
          break;
        }
      }
    }
    else {
      $error_message = 'An item should require a name: ' . print_r($item->get_data(), true);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_item_validation_should_fail_if_price_is_not_numeric() {
    $passed = false;
    $error_message = '';
    $item = new Mijireh_Item();
    $item->copy_from($this->_get_item_data());
    $item->price = 'invalid';
    if(!$item->validate()) {
      $errors = $item->get_errors();
      foreach($errors as $error) {
        if($error == 'price must be a number.') {
          $passed = true;
          break;
        }
      }
    }
    else {
      $error_message = 'An item should have a numeric price: ' . print_r($item->get_data(), true);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_item_total_should_be_calculated_upon_retrieval() {
    $passed = false;
    $error_message = '';
    $item = new Mijireh_Item();
    $item->name = 'Test Item';
    $item->price = 500.50;
    $item->quantity = 2;
    $total = $item->total;
    $this->check($total === '1001.00', "Item total should be 1001.00 but was: " . $total);
  }
  
  public function test_item_data_should_include_a_calculated_total() {
    $passed = false;
    $error_message = '';
    $item = new Mijireh_Item();
    $item->price = 5;
    $item->quantity = 2;
    $data = $item->get_data();
    $this->check($data['total'] === '10.00', 'Item data does not include the correct total');
  }
  
}

Test_Item::run_tests();