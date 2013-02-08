<?php
/**
 * Request demo
 *
 * @package Sugi
 * @version 12.12.18
 */
include "common.php";
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

use Sugi\Request;

echo "method() = " . Request::method() . '<br />';
echo "protocol() = " . Request::protocol() . '<br />';
echo "host() = " .  Request::host() . '<br />';
echo "base() = " .  Request::base() . '<br />';
echo "uri() = " . Request::uri() . '<br />';
echo "current() = " . Request::current() . '<br />';
echo "queue() = " . Request::queue() . '<br />';
echo "full() = " . Request::full() . '<br />';
echo "ip() = " . Request::ip() . '<br />';
echo "cli() = "; var_dump(Request::cli()); echo '<br />';
echo "ajax() = "; var_dump(Request::ajax()); echo '<br />';
?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>
