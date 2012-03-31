<?php 
require '../Mijireh.php';

define('R66_LOG_FILE', dirname(__FILE__) . '/log.txt');

function mijireh_class_loader($class_name) {
  if(substr($class_name, 0, 5) == 'Test_') {
    $class_name = str_replace('Test_', '', $class_name);
    $root = dirname(__FILE__);
    $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
    require_once($root . DIRECTORY_SEPARATOR . $path . '.php');
  }
  elseif(substr($class_name, 0, 8) == 'Mijireh_') {
    $class_name = str_replace('Mijireh_', '', $class_name);
    $root = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Mijireh';
    $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
    require_once($root . DIRECTORY_SEPARATOR . $path . '.php');
  }
}

spl_autoload_register('mijireh_class_loader');

include 'phreedom/bootstrap.php';