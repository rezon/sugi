<?php namespace Sugi;
/**
 * File Testcases
 *
 * @package Sugi
 * @version 20121013
 */

include_once "../Sugi/File.php";

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
ass("\Sugi\File::exists('privatefile.txt') === true");
ass("\Sugi\File::exists('readonlyfile.txt') === true");
ass("\Sugi\File::exists('nonexistsingfile.txt') === false");
ass("\Sugi\File::exists('path') === false");
ass("\Sugi\File::readable('readonlyfile.txt') === true");
ass("\Sugi\File::readable('privatefile.txt') === false");
ass("\Sugi\File::readable('nonexistsingfile.txt') === false");
ass("\Sugi\File::readable('path') === false");
ass("\Sugi\File::get('readonlyfile.txt') === 'R'");
ass("\Sugi\File::get('privatefile.txt') === null");
ass("\Sugi\File::get('privatefile.txt', 'file is not readable') === 'file is not readable'");
ass("\Sugi\File::get('path') === null");
ass("\Sugi\File::ext('privatefile.txt') === 'txt'");
ass("\Sugi\File::ext('path') === ''");
ass("\Sugi\File::put('writable.txt', 'RW') === 2");
ass("\Sugi\File::put('readonlyfile.txt', 'R') === false");
ass("\Sugi\File::put('emptyfile.txt', '') === 0");
ass("\Sugi\File::put('path', 'hi there') === false");
ass("\Sugi\File::append('readonlyfile.txt', 'R') === false");
ass("\Sugi\File::append('emptyfile.txt', 'foo') === 3");
ass("\Sugi\File::append('emptyfile.txt', 'bar') === 3");
ass("\Sugi\File::get('emptyfile.txt') === 'foobar'");
ass("\Sugi\File::append('path', 'hi there') === false");
ass("\Sugi\File::chmod('nonexistsingfile.txt', 0666) === false");
ass("\Sugi\File::chmod('path', 0775) === false");
ass("\Sugi\File::chmod('emptyfile.txt', 0666) === true");

?>
</body>
</html>
