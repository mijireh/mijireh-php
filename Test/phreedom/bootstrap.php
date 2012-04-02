<?php 
date_default_timezone_set('America/New_York');

if(!defined('R66_LOG_FILE')) {
  define('R66_LOG_FILE', dirname(__FILE__) . '/log.txt');
}

function class_loader($class_name) {
  $root = dirname(__FILE__);
  $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
  require_once($root . DIRECTORY_SEPARATOR . $path . '.php');
}

spl_autoload_register('class_loader');

$db_credentials = new stdClass();
$db_credentials->username = 'database_username';
$db_credentials->password = 'database_password';
$db_credentials->database = 'database_name';
$db_credentials->host = 'localhost';