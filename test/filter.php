<?php
/**
 * Filter Testcases
 *
 * @package Sugi
 * @version 20121004
 */
include_once "../Sugi/Filter.php";

// custom assertion handler function
function my_assert_handler($file, $line, $code)	{
	echo "<hr /><div style=\"color:red\">$code</div><hr />";
}

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 		1); // (default 1)
assert_options(ASSERT_WARNING, 		0); // issue a PHP warning for each failed assertion (default 1)
assert_options(ASSERT_BAIL, 		0); // terminate execution on failed assertions (default 0)
assert_options(ASSERT_QUIET_EVAL, 	0); // disable error_reporting during assertion expression evaluation (default 0)
assert_options(ASSERT_CALLBACK, 	'Sugi\my_assert_handler'); // Callback to call on failed assertions (default NULL)

function ass($what) {
	if (assert($what)) {
		echo '<div>'.$what.'&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">OK</span></div>';
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<title>Sugi Tests</title>
	<meta charset="utf-8" />
</head>
<body>
<a href="index.php">back</a>
<?php
echo '<h2>Integers</h2>';
ass("\Sugi\Filter::int(1) === 1");
ass("\Sugi\Filter::int('1') === 1");
ass("\Sugi\Filter::int(' 1') === 1");
ass("\Sugi\Filter::int('a') === false");

echo '<h2>Strings</h2>';
ass("\Sugi\Filter::str('a') === 'a'");
ass("\Sugi\Filter::str('1') === '1'");
ass("\Sugi\Filter::str(1) === '1'");
ass("\Sugi\Filter::str(' a ') === 'a'");
ass("\Sugi\Filter::str(' a ') === 'a'");
ass("\Sugi\Filter::str('') === ''");
ass("\Sugi\Filter::str('', 1) === false");
ass("\Sugi\Filter::str('a', 1) === 'a'");
ass("\Sugi\Filter::str(' a ', 1) === 'a'");
ass("\Sugi\Filter::str('ab', 1, 1) === false");
ass("\Sugi\Filter::str('ab', 1, 2) === 'ab'");
ass("\Sugi\Filter::str(' ab ', 1, 2) === 'ab'");
ass("\Sugi\Filter::str(' abc ', 1, 2) === false");
ass("\Sugi\Filter::str(' abc ', 1, 2, 'error') === 'error'");
ass("\Sugi\Filter::str('abc', 1, 2, 'error') === 'error'");
ass("\Sugi\Filter::str('abc', 1, false, 'error') === 'abc'");

echo '<h2>Plain Strings</h2>';

echo '<h2>URLs</h2>'; 
$url = array (
	'igrivi.com' => false,
	'http://igrivi.com' => true,
	'http://IGriVI.COM' => true,
	'http://igrivi.com/' => true,
	'https://igrivi.com' => true,
	'ftp://igrivi.com' => false,
	'http://localhost' => false,
	'http://127.0.0.1' => false,
	'http://8.8.8.8' => false,
	'http://abc' => false,
	'http://abc.c' => false,
	'http://somedomain.com:81' => true,
	'http://somedomain.com:6' => false,
	'http://somedomain.com:123456' => false,
	'http://somedomain.com:123a' => false,
	'http://somedomain.com/:123' => false,
	'http://somedomain.com/test:123' => false,
	'http://somedomain.com:abc' => false,
	'http://somedomain.com:81/' => true,
	'http://somedomain.com:81/test' => true,
	'http://somedomain.com:81/test' => true,
	'http://порно.bg' => true,
	'http://xn--m1abbbg.bg/' => true,
	'http://президент.рф' => true,
	'http://somedomain.com/dir/people/%D0%B4' => true,
	'http://somedomain.com/dir/people/д' => true,
	'http://somedomain.com/dir/people/<' => false,
	'http://somedomain.com/con?s=+&key=test&search=1' => true,
	'http://somedomain.com/con?s=+&key=%D1%82%D0%B5%D0%A1%D1%82&search=1' => true,
	'http://somedomain.com/con?s=+&key=теСт&search=1' => true,
	'http://somedomain.com/con?s=+&key=те,Ст&search=1' => true,
);
foreach ($url as $test => $res) {
	$res = ($res === false) ? 'false' : "'$test'";
	ass("\Sugi\Filter::url('$test') === $res");
}	

echo '<h2>Emails (no DNS check)</h2>';
$emails = array (
	'Sugi@bulinfo.net' => true,
	'tza.ppa@bulinfo.net' => true,
	'Sugi@localhost' => false,
	'@localhost' => false,
	't@.c' => false,
	't@abc.c' => false,
);
foreach ($emails as $test => $res) {
	$res = ($res === false) ? 'false' : "'$test'";
	ass("\Sugi\Filter::email('$test') === $res");
}	


echo '<h2>Skype names</h2>';
$skype = array(
	'a.L-a2b,a_la_.,-' => true,
	'fifth' => false, // too short
	'sixty1' => true,
	'1totot' => false, // starts with digit
	'.alabala' => false, // starts with .
	'_alabala' => false,
	',alabala' => false,
	'_alabala' => false,
);
foreach ($skype as $test => $res) {
	$res = ($res === false) ? 'false' : "'$test'";
	ass("\Sugi\Filter::skype('$test') === $res");
}

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
