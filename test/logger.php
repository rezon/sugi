<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

use \Sugi\Logger;

include '../Sugi/Logger.php';

Logger::stdout();
Logger::stdout(array('format' => '[{level}] [{Y}.{m}.{d} {H}:{i}:{s}] {message}<br />'));
$c = Logger::console();
Logger::loggly(array('filter' => 'all -debug', 'url' => 'localhost'));
$f = Logger::file(array('filename' => 'log/test.log', 'filter' => 'none +notice'));

Logger::log('some "debug" information', 'debug');
$c->message('this message have to be sholn only in console', 'notice');
Logger::log("somethin'\nwent WRONG!", 'notice');
$f->message('this is only for file, but it shall not be written', 'info');
