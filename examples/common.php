<?php namespace Sugi;
/**
 * @package Sugi
 */

include "../vendor/autoload.php";
App::configure();

// custom assertion handler function
function my_assert_handler($file, $line, $code)	{
	echo "<hr /><div style=\"color:red\">$code</div><hr />";
}

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 		1); // (default 1)
assert_options(ASSERT_WARNING, 		0); // issue a PHP warning for each failed assertion (default 1)
assert_options(ASSERT_BAIL, 		0); // terminate execution on failed assertions (default 0)
assert_options(ASSERT_QUIET_EVAL, 	0); // disable error_reporting during assertion expression evaluation (default 0)
assert_options(ASSERT_CALLBACK, 	'my_assert_handler'); // Callback to call on failed assertions (default NULL)

function ass($what) {
	if (assert($what)) {
		echo '<div>'.$what.'&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">OK</span></div>';
	}
}
