<?php 
require_once 'bootstrap.php';

class Test_Address extends R66_Test {
  
  public function test_copy_from_should_copy_data_from_an_array_into_the_model() {
    $data = array(
      'first_name' => 'Test',
      'last_name' => 'Person',
      'street' => '1234 Test Dr',
      'city' => 'Lanexa',
      'state_province' => 'VA',
      'zip_code' => '23089',
      'country' => 'US',
      'company' => 'Reality66 LLC',
      'apt_suite' => '8808',
      'phone' => '804-557-0766'
    );
    
    $address = new Mijireh_Address();
    $address->copy_from($data);
    $internal_data = $address->get_data();
    $message = 'Expected data: ' . print_r($data, true) . ' Actual data: ' . print_r($internal_data, true);
    $this->check($data == $internal_data, $message);
  }
  
  public function test_missing_required_field_should_fail_validation() {
    $message = '';
    $is_valid = false;
    $address = new Mijireh_Address();
    if(!$is_valid = $address->validate()) {
      $message = implode("\n", $address->get_errors());
    }
    $this->check(!$is_valid, $message);
  }
  
}

Test_Address::run_tests();