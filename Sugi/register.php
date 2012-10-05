<?php
/**
 * Register file will application autoload function
 * This file will be invoked with composer autoload file directive
 *
 * @package Sugi
 * @version 20121004
 */
namespace Sugi;

include_once 'App.php';

// Application __autoload function
App::register();
