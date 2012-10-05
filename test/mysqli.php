<?php
/**
 * Database
 *
 * @package Sugi
 * @version 20121005
 */
namespace Sugi;

include_once "../Sugi/Database.php";

function pre_query($param) {
	echo '<br />Pre Query: <strong>The functions hook and unhook will be changed soon, so DO NOT USE THEM!';
	echo '<br /></strong>';
	var_dump($param);
	echo '<br />';
}

$config = array(
	'host' 		=> 'localhost',
	'database' 	=> 'test',
	'user' 		=> 'test',
	'pass' 		=> ''
);


echo '<h2>MySQLi</h2>';
echo 'Database::mysqli(): ';
$db = Database::mysqli($config);
var_dump($db);
echo '<hr />escape(): ';
var_dump($db->escape("Where's John?"));
echo '<hr />BD connection is established automatically: ';
var_dump($db);
//$db->query("INSERT INTO test(val) VALUES ('Sugi')");
//echo '<hr />last_id(): ';
//var_dump($db->last_id($res));
//$db->query("INSERT INTO test(val) VALUES ('PHP is cool!')");
//echo '<hr />last_id(): ';
//var_dump($db->last_id($res));
echo '<hr />query(): ';
var_dump($res = $db->query("SELECT * FROM test"));
echo '<hr />fetch(): ';
var_dump($db->fetch($res));
echo '<hr />fetch_all(): ';
var_dump($db->fetch_all($res));
echo '<hr />affected(): ';
var_dump($db->affected($res));
echo '<hr />all(): ';
var_dump($db->all("SELECT * FROM test"));
echo '<hr />hook pre_query(): ';
var_dump($db->hook('pre_query', 'Sugi\pre_query'));
echo '<hr />single(): ';
var_dump($db->single("SELECT * FROM test"));
echo '<hr />close(): ';
var_dump($db->close());
echo '<hr />open(): ';
var_dump($db->open());
echo '<hr />single_field(): ';
var_dump($res = $db->single_field("SELECT * FROM test"));
echo '<hr />last_id(): ';
var_dump($db->last_id($res));