<?php 
/**
 * Logger demo
 *
 * @package Sugi
 * @version 12.12.11
 */

include "common.php";

use Sugi\Logger;

?>
<!doctype html>
<html lang="en">
<head>
	<title>Sugi Tests</title>
	<meta charset="utf-8" />
</head>
<body>
	<a href="index.php">back</a><br />

<?php

Logger::stdout();
Logger::stdout(array('format' => '[{level}] [{Y}.{m}.{d} {H}:{i}:{s}] {message}<br />'));
$c = Logger::console();
Logger::loggly(array('filter' => 'all -debug', 'url' => 'localhost'));
$f = Logger::file(array('filename' => 'log/test.log', 'filter' => 'none +notice'));

Logger::log('some "debug" information', 'debug');
$c->message('this message have to be sholn only in console', 'notice');
Logger::log("somethin'\nwent WRONG!", 'notice');
$f->message('this is only for file, but it shall not be written', 'info');

?>

	<br />
	<a href="index.php">back</a>
	<br />
</body>
</html>
