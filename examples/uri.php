<?php
/**
 * URI demo
 *
 * @package Sugi
 * @version 12.12.11
 */

include "common.php";

use Sugi\URI;

?>
<!doctype html>
<html lang="en">
<head>
	<title>Sugi Tests</title>
	<meta charset="utf-8" />
</head>
<body>
	<a href="index.php">back</a><br />

<?php

echo "current() = " . URI::current() . '<br />';
echo "segments() = "; var_dump(URI::segments()); echo '<br />';
echo "segments('foo/bar/sugi') = "; var_dump(URI::segments('foo/bar/sugi')); echo '<br />';
echo "segment(1) = " .  URI::segment(1) . '<br />';
echo "segment(22) = "; var_dump(URI::segment(22)); echo '<br />';
echo "segment(23, 'defalutsegment') = "; var_dump(URI::segment(23, 'defaultsegment')); echo '<br />';

?>

	<br />
	<a href="index.php">back</a>
	<br />
</body>
</html>
