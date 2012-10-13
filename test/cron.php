<?php
/**
 * Hash
 *
 * @package Sugi
 * @version 20121008
 */
namespace Sugi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../Sugi/Cron.php";
include_once "../Sugi/File.php";
include_once "../Sugi/Filter.php";
include_once "../Sugi/Logger.php";

// initialize stdout logger
Logger::stdout(array(
	//'filter' => 'all -debug'
));

// start cronjobs
Cron::start(array('file' => 'cron.conf'));
