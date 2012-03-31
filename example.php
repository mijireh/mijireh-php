<?php 

// require the mijireh php library
require 'mijireh-php';

Mijireh::$access_key = '8539B47746D3A6C2';

// get store info
$store_info = Mijireh::get_store_info();

// slurp a page
$job_id = Mijireh::slurp('http://www.mysite.com/store/mijireh-secure-checkout');

// create a basic order
$order = new Mijireh_Order();
$order->add_item('Example Product', 9.99, 1, 'example_sku');
$order->return_url('http://www.mysite.com/store/receipt');
$order->shipping = 5.00;
$order->total = 14.99;
$order->create();

// load an order
$order = new Mijireh_Order('<order_number>');