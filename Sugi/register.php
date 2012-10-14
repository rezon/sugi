<?php namespace Sugi;
/**
 * Register file will application autoload function
 * This file will be invoked with composer autoload file directive
 *
 * @package Sugi
 * @version 20121013
 */

include_once 'App.php';

// Application __autoload function
App::register();
