<?php 
use Sugi\Session;
use Sugi\Filter;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once ("../Sugi/Session.php");
include_once ("../Sugi/Filter.php");

$config = array(
	'type' => 'file', 
	'file' => array(
		'path' => __DIR__.'/tmp/'
	)
);

Session::singleton($config);
session_start();

$inc = Filter::session('test', 0);
$inc++;
var_dump($inc);

$_SESSION['test'] = $inc;
