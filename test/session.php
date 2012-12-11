<?php
/**
 * Session
 *
 * @package Sugi
 * @version 12.12.11
 */

include "common.php";

// Register DB
Sugi\Module::set('database', function ()
{
	$db = Sugi\Database::sqlite3(array('database' => __DIR__.'/tmp/test.sqllite3'));
	$db->query('
	CREATE TABLE IF NOT EXISTS sessions (
		session_id VARCHAR(40) NOT NULL PRIMARY KEY,
		session_time INTEGER NOT NULL,
		session_data TEXT,
		session_lifetime INTEGER NOT NULL DEFAULT 0
	)');
	return $db;
});

Sugi\Session::singleton(
	array(
		// set session driver
		'type' => 'database', // custom database driver
		'type' => false, // default
		'type' => 'file', // custom file driver
		'type' => Sugi\Filter::key('type', $_GET, 0, 20, false),
		
		// Sugi\Session\File driver
		'file' => array(
			'path' => __DIR__.'/tmp/',
		),

		// Sugi\Session\Database driver
		'database' => array(
			'db' => Sugi\Module::get('database'),
		),
	)
);

session_start();
$_SESSION['count'] = isset($_SESSION['count']) ? $_SESSION['count'] + 1 : 0;

var_dump($_SESSION['count']);
