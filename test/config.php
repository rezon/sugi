<?php
/**
 * Config Testcases
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

ass("Sugi\Config::get('test') === NULL");
ass("Sugi\Config::get('test', 'foo') === 'foo'");
ass("Sugi\Config::set('test', 'bar') === NULL");
ass("Sugi\Config::get('test', 'foo') === 'bar'");
ass("Sugi\Config::get('key1.subkey1') === NULL");
ass("Sugi\Config::get('key1.subkey1', 'foo') === 'foo'");
Sugi\Config::set('test', 'bar');
Sugi\Config::set('key1', array('subkey1' => 'bar', 'subkey2' => 'foobar', 'subkey3' => array('sub' => 'one')));
ass("is_array(Sugi\Config::get()) === true");
ass("is_array(Sugi\Config::get('key1.')) === true");
ass("Sugi\Config::get('key1.subkey1') === 'bar'");
ass("Sugi\Config::get('key1.subkey1', 'foo') === 'bar'");
ass("Sugi\Config::get('key1.subkey1.subsubkey') === NULL");
ass("Sugi\Config::get('key1.subkey3.sub') === 'one'");
ass("is_array(Sugi\Config::get('key1')) === true");
ass("Sugi\Config::get('key1.subkey99') === NULL");
ass("Sugi\Config::get('key1.subkey99', 'foo') === 'foo'");

ass("Sugi\Config::test('key99') === NULL");
ass("Sugi\Config::test('key99', 'foo') === 'foo'");
ass("Sugi\Config::test('key1') === 'value1'");
ass("Sugi\Config::test('key1', 'foo') === 'value1'");
ass("is_array(Sugi\Config::test('key2')) === true");
ass("Sugi\Config::test('key2.subkey1') === 'subvalue1'");
ass("Sugi\Config::file('test', 'key2.subkey1') === 'subvalue1'");
ass("Sugi\Config::test('key2.subkey1.subsubkey') === NULL");

ass("Sugi\Config::test2('key99') === NULL");
ass("Sugi\Config::test2('key99', 'foo') === 'foo'");
ass("Sugi\Config::test2('key1') === 'value1'");
ass("Sugi\Config::test2('key1', 'foo') === 'value1'");
ass("is_array(Sugi\Config::test2('key2')) === true");
ass("Sugi\Config::test2('key2.subkey1') === 'subvalue1'");
ass("Sugi\Config::test2('key2.subkey1.subsubkey') === NULL");

ass("Sugi\Config::test99() === NULL");
?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>
