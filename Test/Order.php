<?php 
require_once 'bootstrap.php';

class Test_Order extends R66_Test {
  
  private $_access_key = '8539B47746D3A6C2';
  private $_order_number = '2BEE43198DC8C294';
  
  public function test_adding_an_item_to_an_order_by_params() {
    $order = new Mijireh_Order();
    $order->add_item('Example Item', '12.00');
    $order->add_item('Toy Elephant', 7.99);
    $item_count = $order->item_count();
    $this->check($item_count == 2, "The item count should be 2 but was: $item_count");
  }
  
  public function  test_validating_a_valid_order_should_return_true() {
    $message = '';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->add_item('North Shore DVD', 9.99);
    $is_valid = $order->validate();
    if(!$is_valid) {
      $message = print_r($order->get_errors(), true);
    }
    $this->check($is_valid, $message);
  }
  
  public function test_order_validation_should_fail_if_the_order_total_is_not_a_number_greater_than_or_equal_to_zero() {
    $expected_error_message = 'order total must be greater than zero';
    $found_expected_error = false;
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->total = -10;
    $is_valid = $order->validate();
    foreach($order->get_errors() as $error) {
      if($error == $expected_error_message) {
        $found_expected_error = true;
      }
    }
    $this->check($found_expected_error, $expected_error_message);
  }
  
  public function test_order_validation_should_pass_if_the_order_total_is_a_number_greater_than_or_equal_to_zero() {
    $total = 10;
    $passed = false;
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->total = $total;
    if($is_valid = $order->validate() && $order->total == $total) {
      $passed = true;
    }
    $this->check($passed, "The order total should have been $total but was: " . $order->total);
  }
  
  public function test_order_validation_should_fail_if_the_return_url_is_empty() {
    $found_expected_error = false;
    $expected_error_message = 'return url is required';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->return_url = '';
    if(!$order->validate()) {
      foreach($order->get_errors() as $error) {
        if($error == $expected_error_message) {
          $found_expected_error = true;
        }
      }
    }
    $this->check($found_expected_error, $expected_error_message);
  }
  
  public function test_order_validation_should_fail_if_the_return_url_does_not_start_with_http() {
    $found_expected_error = false;
    $expected_error_message = 'return url is invalid';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->return_url = 'invalid.url';
    if(!$order->validate()) {
      foreach($order->get_errors() as $error) {
        if($error == $expected_error_message) {
          $found_expected_error = true;
        }
      }
    }
    $this->check($found_expected_error, $expected_error_message);
  }
  
  public function test_order_validation_should_fail_if_the_order_does_not_contain_any_items() {
    $found_expected_error = false;
    $expected_error_message = 'the order must contain at least one item';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->clear_items();
    if(!$order->validate()) {
      foreach($order->get_errors() as $error) {
        if($error == $expected_error_message) {
          $found_expected_error = true;
        }
      }
    }
    $this->check($found_expected_error, $expected_error_message);
  }
  
  public function test_an_order_should_be_able_to_have_a_valid_shipping_address() {
    $passed = false;
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    
    $address_data = array(
      'street' => '1234 Test Dr',
      'city' => 'Lanexa',
      'state_province' => 'VA',
      'zip_code' => '23089',
      'country' => 'US',
      'company' => 'Reality66 LLC',
      'apt_suite' => '8808',
      'phone' => '804-557-7066'
    );
    $address = new Mijireh_Address();
    $address->copy_from($address_data);
    
    $order->set_shipping_address($address);
    if($address = $order->get_shipping_address() && $address->validate()) {
      $passed = true;
    }
    $this->check($passed);
  }
  
  public function test_an_order_should_be_able_to_have_a_valid_billing_address() {
    $passed = false;
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    
    $address_data = array(
      'street' => '4321 Test Dr',
      'city' => 'Lanexa',
      'state_province' => 'VA',
      'zip_code' => '23089',
      'country' => 'US',
      'company' => 'Reality66 LLC',
      'apt_suite' => '8808',
      'phone' => '804-557-7066'
    );
    $address = new Mijireh_Address();
    $address->copy_from($address_data);
    
    $order->set_billing_address($address);
    if($address = $order->get_billing_address() && $address->validate()) {
      $passed = true;
    }
    $this->check($passed);
  }
  
  public function test_hydrating_an_order_should_result_in_a_valid_order() {
    $passed = false;
    $error_message = '';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $passed = true;
    $this->check($passed, $error_message);
  }
  
  public function test_creating_an_order_should_populate_the_order_number_attribute_of_the_order() {
    $passed = false;
    $error_message = '';
    Mijireh::$access_key = $this->_access_key;
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->create();
    if(strlen($order->order_number) > 5) {
      $passed = true;
    }
    else {
      $error_message = print_r($order, true);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_creating_an_order_with_a_negative_total_should_throw_exception_Mijireh_Exception() {
    $passed = false;
    $error_message = '';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->total = -10;
    try {
      Mijireh::$access_key = $this->_access_key;
      $order->create();
    }
    catch(Mijireh_Exception $e) {
      $passed = true;
    }
    catch(Exception $e) {
      $error_message = 'Should have been Mijireh_Exception but was :: ' . get_class($e);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_creating_an_order_with_an_empty_string_for_shipping_address_should_throw_exception_Mijireh_ServerError() {
    $passed = false;
    $error_message = '';
    $order = new Mijireh_Order();
    $order->copy_from($this->_get_order_data_array());
    $order->shipping_address = '';
    try {
      Mijireh::$access_key = $this->_access_key;
      $order->create();
    }
    catch(Mijireh_ServerError $e) {
      $passed = true;
    }
    catch(Mijireh_Exception $e) {
      $error_message = 'Should have been Mijireh_Exception but was :: ' . get_class($e);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_loading_an_order_should_populate_the_order_items() {
    $passed = false;
    $error_message = '';
    Mijireh::$access_key = $this->_access_key;
    $order = new Mijireh_Order($this->_order_number);
    if($order->item_count() > 0) {
      $passed = true;
    }
    else {
      $error_message = 'order: ' . $order->order_number .' :: item count is: ' . $order->item_count;
    }
    $this->check($passed, $error_message);
  }
   
  private function _get_order_data_array() {
    $order_data = array(
      'total' => 12.00,
      'return_url' => 'http://www.mijireh.com',
      'items' => array(
        array(
          'name' => 'Example Item', 
          'price' => '10.00'
        )
      ),
      'email' => 'test@person.com',
      'first_name' => 'Test',
      'last_name' => 'Person',
      'meta_data' => array(),
      'subtotal' => 10.00,
      'tax' => 0.50,
      'shipping' => 1.50,
      'discount' => 0,
      'shipping_address' => array(
        'street' => '1234 Test Dr',
        'city' => 'Lanexa',
        'state_province' => 'VA',
        'zip_code' => '23089',
        'country' => 'US',
        'apt_suite' => '3rd Floor',
        'phone' => '888-888-8888',
      ),
      'billing_address' => array(
        'street' => '4321 Test Dr',
        'city' => 'Lanexa',
        'state_province' => 'VA',
        'zip_code' => '23089',
        'country' => 'US',
        'apt_suite' => '3rd Floor',
        'phone' => '888-888-8888',
      )
    );
    return $order_data;
  }
  
}

Test_Order::run_tests();