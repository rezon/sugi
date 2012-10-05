<?php
/**
 * Route
 *
 * @package Sugi
 * @version 20121004
 */
namespace Sugi;

include_once "../Sugi/Route.php";
include_once "../Sugi/URI.php";

Route::uri('(*)test/route', function() {
	echo 'route';
});

Route::uri('(*)test/route.php', function() {
	echo 'route.php is OK';
});
