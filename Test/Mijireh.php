<?php 

require_once 'bootstrap.php';


class Test_Mijireh extends R66_Test {
  
  private $_access_key = '8539B47746D3A6C2';
  private $_order_number = '2BEE43198DC8C294';
  
  public function test_checkout_preview_link_should_return_valid_url() {
    $expected = 'http://mist.mijireh.com/checkout/' . $this->_access_key;
    Mijireh::$access_key = $this->_access_key;
    $url = Mijireh::preview_checkout_link();
    $this->check($expected == $url, "Expecing $expected but got $url");
  }
  
  public function test_getting_store_info_with_correct_access_key_should_return_store_data_array() {
    $passed = false;
    $error_message = '';
    Mijireh::$access_key = $this->_access_key;
    $store_data = Mijireh::get_store_info();
    if(is_array($store_data)) {
      $expected_keys = array('name', 'mode', 'status', 'time_zone', 'paid_order_count','monthly_sales','total_sales','slurped');
      if($expected_keys == array_keys($store_data)) {
        $passed = true;
      }
      foreach($expected_keys as $key) {
        if(!isset($store_data[$key])) {
          $error_message .= "\nexpected key missing: $key";
        }
      }
    }
    else {
      $error_message = 'store data was not returned as an array';
    }
    $this->check($passed, $error_message);
  }
 
  public function test_slurping_a_page_should_return_a_job_id() {
    $passed = false;
    $url = 'http://leehblue.com/john-316-in-php/';
    $error_message = "unable to slurp the page: $url";
    
    Mijireh::$access_key = $this->_access_key;
    $job_id = Mijireh::slurp($url);
    if(!empty($job_id) && strlen($job_id) > 5) {
      $passed = true;
    }
    else {
      $message .= " : job id: $job_id";
    }
    $this->check($passed, $error_message);
  }
  
  public function test_slurping_without_passing_a_url_should_throw_exception_Mijireh_ServerError() {
    $passed = false;
    $error_message = '';
    $url = '';
    try {
      Mijireh::$access_key = $this->_access_key;
      $job_id = Mijireh::slurp($url);
    }
    catch(Mijireh_Exception $e) {
      $passed = true;
    }
    catch(Exception $e) {
      $error_message = 'Expecting Mijireh_ServerError but got ' . get_class($e) . print_r($e, true);
    }
    $this->check($passed, $error_message);
  }
  
  public function test_slurping_an_invalid_url_should_throw_exception_Mijireh_NotFound() {
    $passed = false;
    $error_message = '';
    $url = 'nothingtobeseenhere';
    try {
      Mijireh::$access_key = $this->_access_key;
      $job_id = Mijireh::slurp($url);
      $error_message = "Created a new slurp job id: $job_id";
    }
    catch(Mijireh_NotFound $e) {
      $passed = true;
    }
    catch(Exception $e) {
      $error_message = 'Expecting Mijireh_ServerError but got ' . get_class($e) . print_r($e, true);
    }
    $this->check($passed, $error_message);
  }
  
  private function _get_order_data() {
    $order_data = array(
      'total' => 22.00,
      'return_url' => 'http://www.mijireh.com',
      'items' => array(
        array('name' => 'First Item', 'price' => 10.00, 'quantity' => 1),
        array('name' => 'Second Item', 'price' => 10.00, 'quantity' => 1),
      ),
      'email' => 'test@person.com',
      'first_name' => 'Test',
      'last_name' => 'Person',
      'meta_data' => array(),
      'subtotal' => 20.00,
      'tax' => 0.50,
      'shipping' => 1.50,
      'discount' => 0,
      'shipping_address' => array(
        'street' => '1234 Test Dr',
        'city' => 'Lanexa',
        'state_province' => 'VA',
        'zip_code' => '23089',
        'country' => 'US',
        'company' => 'Reality66 LLC',
        'apt_suite' => '8808',
        'phone' => '804-557-7066'
      )
    );
    return $order_data;
  }
}

Test_Mijireh::run_tests();