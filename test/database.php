<?php namespace Sugi;
/**
 * @package Sugi
 */

include "common.php";

function postQuery($action, $data)
{
	echo "<p>postQuery is executed with params: $action, $data</p>";
}

$driver = Filter::get_str("driver", 1, 20, "mysql");

$config["mysql"] = array(
	"type"     => "mysql",
	"host"     => "localhost",
	"database" => "test",
	"user"     => "test",
	"pass"     => ""
);
$config["pgsql"] = array(
	"type"     => "pgsql",
	"host"     => "localhost",
	"database" => "test",
	"user"     => "test",
	"pass"     => "pass"
);
$config["sqlite3"] = array(
	"type"     => "sqlite3",
	"database" => "test/db.sqlite3",
	"database" => ":memory:",
);
$config["sqlite"] = array(
	"type"     => "sqlite",
	"database" => ":memory:",
);

echo "<h2>$driver</h2>";
$db = new Database($config[$driver]);

$db->hook("post_open", function ($action, $data) use ($db) {
	global $driver;
	echo "<p>BD connection is established automatically</p>";
	// MySQL specific routines
	if ($driver == "mysql") {
		$db->query("SET NAMES utf8");
		$db->query("CREATE TABLE IF NOT EXISTS test (id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, val VARCHAR(255))");
		// $res = $db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
		// echo "<hr />lastId(): ";
		// var_dump($db->lastId($res));
		// $res = $db->query($db->bindParams("INSERT INTO test(val) VALUES (:val)", array("val" => 'Sugi')));
		// echo "<hr />lastId(): ";
		// var_dump($db->lastId($res));
	}
	// SQLite specific routines
	elseif ($driver == "sqlite" or $driver == "sqlite3") {
		// since we use MEMORY table we need them to be created on every DB connection
		$db->query("CREATE TABLE test (id integer not null primary key, val varchar(255))");
		$res = $db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
		echo "<hr />lastId(): ";
		var_dump($db->lastId($res));
		$res = $db->query($db->bindParams("INSERT INTO test(val) VALUES (:val)", array("val" => 'Sugi')));
		echo "<hr />lastId(): ";
		var_dump($db->lastId($res));
	}
});

$db->hook("pre_query", function ($action, $data) use ($db) {
	echo "<p>SQL: $data</p>";
});

echo "<hr />escape(): ";
var_dump($db->escape("Where's John?"));
echo "<hr />query(): ";
var_dump($res = $db->query("SELECT * FROM test"));
echo "<hr />fetch(): ";
var_dump($db->fetch($res));
echo "<hr />fetchAll(): ";
var_dump($db->fetchAll($res));
echo "<hr />affected(): ";
var_dump($db->affected($res));
echo "<hr />all(): ";
var_dump($db->all("SELECT * FROM test"));
echo "<hr />hook postQuery(): ";
var_dump($db->hook("post_query", "Sugi\postQuery"));
echo "<hr />single(): ";
var_dump($db->single("SELECT * FROM test"));
echo "<hr />close(): ";
$db->unhook("post_query", "Sugi\postQuery");
var_dump($db->close());
echo "<hr />open(): ";
var_dump($db->open());
echo "<hr />singleField(): ";
var_dump($res = $db->singleField("SELECT * FROM test"));
echo "<hr />lastId(): ";
var_dump($db->lastId($res));
