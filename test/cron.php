<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Sofia');

include_once "../Sugi/Cron.php";
include_once "../Sugi/File.php";
include_once "../Sugi/Filter.php";
include_once "../Sugi/Logger.php";

use \Sugi\Logger;
use \Sugi\Cron;

// initialize stdout logger
Logger::stdout(array(
	//'filter' => 'all -debug'
));

// start cronjobs
Cron::start(array('file' => 'cron.conf'));
