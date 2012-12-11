<?php
/**
 * Translations Testcases
 *
 * @package Sugi
 * @version 12.12.11
 */
include "common.php";
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

Sugi\Lang::configure(array('lang' => 'bg', 'path' => dirname(__FILE__).'/lang/'));
Sugi\Lang::load('test');
echo Sugi\Lang::get('test') . '<br />';
echo __('Hi, :user', array(':user' => 'Joe'));
?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>
