<?php namespace Sugi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../Sugi/Config.php";

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

ass("Sugi\Config::get('test') === NULL");
ass("Sugi\Config::get('test', 'foo') === 'foo'");
ass("Sugi\Config::get('test.test') === NULL");
ass("Sugi\Config::get('test.test', 'foo') === 'foo'");
ass("Sugi\Config::get('configfile.key1', 'foo') === 'value1'");
ass("Sugi\Config::get('configfile.key1a') === NULL");
ass("is_array(Sugi\Config::get('configfile.key2')) === true");
//ass("Sugi\Config::get('configfile.key2.subkey1') === 'subvalue1'");
