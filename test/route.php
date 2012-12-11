<?php
/**
 * URI demo
 *
 * @package Sugi
 * @version 12.12.11
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

Sugi\Route::add('<path>/test/route', function() {
	echo 'route';
});

Sugi\Route::add('<path>/test/route.php', function() {
	echo 'route.php is OK';
});

if (!Sugi\Route::process_request()) {
	echo '404';
};

?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>
