<?php namespace Sugi;
/**
 * @package Sugi
 */

include "common.php";

/*
$db = new SQLite3(":memory:");
$db->query("CREATE TABLE test (id integer not null primary key)");
$result = $db->exec("INSERT INTO test (id) VALUES (1)");
var_dump($result);
echo "<hr />";
$result = @$db->exec("INSERT INTO test (id) VALUES (1)");
var_dump($result);
echo "<hr />";
var_dump($result->fetchArray());
echo "<hr />";
$db2 = new SQLite3(":memory:");
$db2->query("CREATE TABLE test (id integer not null)");
$result = $db2->query("INSERT INTO test (id) VALUES (2)");
var_dump($result->fetchArray());
echo "<hr />";
*/


// $db1 = new \Sugi\Database\Sqlite3();
// $db1 = new \Sugi\Database\Sqlite3(array());
// $db1 = new \Sugi\Database\Sqlite3(array("handle" => new StdClass));
// $db1 = new \Sugi\Database\Sqlite3(array("handle" => new SQLite3(":memory:")));
// $db1 = new \Sugi\Database\Sqlite3(array("handle" => new \SQLite3(":memory:"), "database" => ":memory"));
/*
$db = new \Sugi\Database\Sqlite3(array("database" => ":memory:"));
$db->open();
$db->query("CREATE TABLE test (id integer not null primary key, val varchar(255))");
$db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
$db->query("INSERT INTO test(val) VALUES ('Sugi')");
var_dump($res = $db->query("SELECT * FROM test")); // SQLite3Result
var_dump($res = $db->query("SELECT * FROM test WHERE id = 1")); // SQLite3Result
var_dump($res = $db->query("SELECT * FROM test2 WHERE id = 3")); // false (with Warning)
if (!$res) echo $db->error();
var_dump($db->fetch($res));
echo "<hr />";

$db = new \Sugi\Database\Mysql(array("database" => "test", "user" => "test"));
$db->open();
var_dump($res = $db->query("SELECT * FROM test")); // mysqli_result
var_dump($res = $db->query("SELECT * FROM test WHERE id = 3")); // mysqli_result
var_dump($res = $db->query("SELECT * FROM test2 WHERE id = 3")); // false
if (!$res) echo $db->error();
var_dump($db->fetch($res));
echo "<hr />";
*/
/*
$db = new \Sugi\Database\Pgsql(array("host" => "localhost", "database" => "test", "user" => "test", "pass" => "test"));
$db->open();
var_dump($res = $db->query("SELECT * FROM test")); // resource of type (pgsql result)
var_dump($res = $db->query("SELECT * FROM test WHERE id = 3")); // resource of type (pgsql result)
var_dump($res = $db->query("SELECT * FROM test2 WHERE id = 3")); // false (with Warning)
if (!$res) echo $db->error();
var_dump($db->fetch($res));
*/

function postQuery($action, $data)
{
	echo "<p>postQuery is executed with params: $action, $data</p>";
}

$driver = Filter::get_str("driver", 1, 20, "");
if (!$driver) exit;

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
$db = new Database($config);

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
