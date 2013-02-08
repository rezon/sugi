<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once ("../Sugi/Registry.php");
use Sugi\Registry;

Registry::set('one', 1);
Registry::set('two', 2);

var_dump(Registry::get('one'));
var_dump(Registry::get('two'));
var_dump(Registry::isRegistered('one'));
Registry::remove('one');
var_dump(Registry::isRegistered('one'));
var_dump(Registry::get('five', 5));
try {
	var_dump(Registry::get('five'));
}
catch (\Exception $e) {
	echo "five does not exists";
} 
