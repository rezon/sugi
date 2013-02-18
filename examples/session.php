<?php namespace Sugi;
/**
 * @package Sugi
 */

include "common.php";

// Register DB
Module::set("db", function ()
{
	$db = Module::get("Database", array("type" => "sqlite3", "database" => __DIR__."/tmp/test.sqllite3"));
	$db->query('
	CREATE TABLE IF NOT EXISTS sessions (
		session_id VARCHAR(40) NOT NULL PRIMARY KEY,
		session_time INTEGER NOT NULL,
		session_data TEXT,
		session_lifetime INTEGER NOT NULL DEFAULT 0
	)');
	return $db;
});

$config = array(
	// set session driver
	"type" => Filter::get_str("type", 0, 20, false),
	
	// Sugi\Session\File driver
	"file" => array(
		"path" => __DIR__."/tmp/",
	)
);
// Sugi\Session\Database driver
if ($config["type"] == "database") {
	$config["database"] = array(
		"db" => Module::get("db"),
	);	
}

Session::singleton($config);

session_start();
$_SESSION['count'] = isset($_SESSION['count']) ? $_SESSION['count'] + 1 : 0;

var_dump($_SESSION['count']);
