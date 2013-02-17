<?php namespace Sugi;
/**
 * Filter Testcases
 *
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
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
echo '<h2>$_GET</h2>';
ass("is_null(\Sugi\Filter::get('w'))"); 
ass("is_null(\Sugi\Filter::get('w', null))");
ass("\Sugi\Filter::get('w', false) === false");
ass("\Sugi\Filter::get('w', 1) === 1");
ass("\Sugi\Filter::get('gs') === 'alabala'");
ass("\Sugi\Filter::get('gi') === '1'");
ass("\Sugi\Filter::get('gi', 2) == 1");
ass("\Sugi\Filter::get('gi', false) == 1");

echo '<h2>String from $_GET</h2>';
ass("\Sugi\Filter::get_str('gs') === 'alabala'");
ass("\Sugi\Filter::get_str('gs', 10) === false");
ass("\Sugi\Filter::get_str('gs', 1, 3) === false");
ass("\Sugi\Filter::get_str('gs', 1, 100) === 'alabala'");
ass("\Sugi\Filter::get_str('gs', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::get_str('notexistingkey', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::get_str('notexistingkey', 1, 3, false) === false");
ass("\Sugi\Filter::get_str('gi') === '1'");

echo '<h2>Integers from $_GET</h2>';
ass("\Sugi\Filter::get_int('gs') === false");
ass("\Sugi\Filter::get_int('gs', false, false, 2) === 2");
ass("\Sugi\Filter::get_int('gi') === 1");
ass("\Sugi\Filter::get_int('gi', -100, 100) === 1");
ass("\Sugi\Filter::get_int('gi', 100) === false");
ass("\Sugi\Filter::get_int('gi', 1, 1, 2) === 1");
ass("\Sugi\Filter::get_int('notexistingkey', 1, 1, 'error') === 'error'");

echo '<h2>$_POST</h2>';
ass("is_null(\Sugi\Filter::post('w'))"); 
ass("is_null(\Sugi\Filter::post('w', null))");
ass("\Sugi\Filter::post('w', false) === false");
ass("\Sugi\Filter::post('w', 1) === 1");
ass("\Sugi\Filter::post('ps') === 'postalabala'");
ass("\Sugi\Filter::post('pi') === '1'");
ass("\Sugi\Filter::post('pi', 2) === '1'");
ass("\Sugi\Filter::post('pi', false) === '1'");

echo '<h2>String from $_POST</h2>';
ass("\Sugi\Filter::post_str('ps') === 'postalabala'");
ass("\Sugi\Filter::post_str('ps', 30) === false");
ass("\Sugi\Filter::post_str('ps', 1, 3) === false");
ass("\Sugi\Filter::post_str('ps', 1, 100) === 'postalabala'");
ass("\Sugi\Filter::post_str('ps', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::post_str('notexistingkey', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::post_str('notexistingkey', 1, 3, false) === false");

echo '<h2>Integers from $_POST</h2>';
ass("\Sugi\Filter::post_int('ps') === false");
ass("\Sugi\Filter::post_int('ps', false, false, 2) === 2");
ass("\Sugi\Filter::post_int('pi') === 1");
ass("\Sugi\Filter::post_int('pi', -100, 100) === 1");
ass("\Sugi\Filter::post_int('pi', 100) === false");
ass("\Sugi\Filter::post_int('pi', 1, 1, 2) === 1");
ass("\Sugi\Filter::post_int('notexistingkey', 1, 1, 'error') === 'error'");

echo '<h2>$_COOKIE</h2>';
ass("is_null(\Sugi\Filter::cookie('w'))"); 
ass("is_null(\Sugi\Filter::cookie('w', null))");
ass("\Sugi\Filter::cookie('w', false) === false");
ass("\Sugi\Filter::cookie('w', 1) === 1");
ass("\Sugi\Filter::cookie('cs') === 'alabalacookie'");
ass("\Sugi\Filter::cookie('ci') === '1'");
ass("\Sugi\Filter::cookie('ci', 2) == 1");
ass("\Sugi\Filter::cookie('ci', false) == 1");

echo '<h2>String from $_COOKIE</h2>';
ass("\Sugi\Filter::cookie_str('cs') === 'alabalacookie'");
ass("\Sugi\Filter::cookie_str('cs', 30) === false");
ass("\Sugi\Filter::cookie_str('cs', 1, 3) === false");
ass("\Sugi\Filter::cookie_str('cs', 1, 100) === 'alabalacookie'");
ass("\Sugi\Filter::cookie_str('cs', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::cookie_str('notexistingkey', 1, 3, 'tralala') === 'tralala'");
ass("\Sugi\Filter::cookie_str('notexistingkey', 1, 3, false) === false");

echo '<h2>Integers from $_COOKIE</h2>';
ass("\Sugi\Filter::cookie_int('cs') === false");
ass("\Sugi\Filter::cookie_int('cs', false, false, 2) === 2");
ass("\Sugi\Filter::cookie_int('ci') === 1");
ass("\Sugi\Filter::cookie_int('ci', -100, 100) === 1");
ass("\Sugi\Filter::cookie_int('ci', 100) === false");
ass("\Sugi\Filter::cookie_int('ci', 1, 1, 2) === 1");
ass("\Sugi\Filter::cookie_int('notexistingkey', 1, 1, 'error') === 'error'");

?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>
