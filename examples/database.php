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

$config = array(
	"type"     => $driver,
	"mysql"    => array(
		"host"     => "localhost",
		"database" => "test",
		"user"     => "test",
		"pass"     => ""
	),
	"pgsql"    => array(
		"host"     => "localhost",
		"database" => "test",
		"user"     => "test",
		"pass"     => "test"
	),
	"sqlite3"  => array(
		"database" => "db.sqlite3",
		"database" => ":memory:",
	),
	"sqlite"   => array(
		"database" => ":memory:",
	),
);

echo "<h2>$driver</h2>";

// first method - creating driver first, and passing it to the Database
// $dbd = new \Sugi\Database\Sqlite3($config[$driver]);
// $db = new Database($dbd);

// second method - invoking static factory method
// $db = Database::factory($config);

// third method - using factory method with Module
// Module::set("Database", function() use ($config) {
//  	return Database::factory($config);
// });
// $db = Module::get("Database");

// forth method - Setting config params in Module. Factory will be auto invoked 
// This is preferred method if Module::get is invoked in several files 
// Module::set("Database", $config);
// $db = Module::get("Database");

// direct Module creation
$db = Module::get("Database", $config);

$db->hook("post_open", function ($action, $data) use ($db) {
	global $driver;
	echo "<p>BD connection is established automatically</p>";
	// MySQL specific routines
	if ($driver == "mysql") {
		$db->query("SET NAMES utf8");
		$db->query("CREATE TABLE IF NOT EXISTS test (id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY, val VARCHAR(255))");
		// $res = $db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
		// $res = $db->query($db->bindParams("INSERT INTO test(val) VALUES (:val)", array("val" => 'Sugi')));
	}
	// SQLite specific routines
	elseif ($driver == "sqlite" or $driver == "sqlite3") {
		// since we use MEMORY table we need them to be created on every DB connection
		$db->query("CREATE TABLE test (id integer not null primary key, val varchar(255))");
		$res = $db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
		$res = $db->query($db->bindParams("INSERT INTO test(val) VALUES (:val)", array("val" => 'Sugi')));
	}
	elseif ($driver == "pgsql") {
		$db->query("CREATE TABLE IF NOT EXISTS test (id SERIAL NOT NULL PRIMARY KEY, val VARCHAR(255))");
		// $res = $db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
		// $res = $db->query($db->bindParams("INSERT INTO test(val) VALUES (:val)", array("val" => 'Sugi')));
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
$res = $db->query("INSERT INTO test (val) VALUES ('err')");
$v = $db->lastId($res);
echo $v;
$db->query("DELETE FROM test WHERE id = $v");
echo "<hr />";
