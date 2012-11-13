<?php
namespace Sugi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../Sugi/Route.php";
include_once "../Sugi/URI.php";

Route::add('<path>/test/route', function() {
	echo 'route';
});

Route::add('<path>/test/route.php', function() {
	echo 'route.php is OK';
});

if (!Route::process_request()) {
	echo '404';
};
