<?php
/**
 * Cron Testcases
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
// initialize stdout logger
Sugi\Logger::stdout(array(
	//'filter' => 'all -debug'
));

// start cronjobs
Sugi\Cron::start(array('file' => 'cron.conf'));

?>
<br />
<a href="index.php">back</a>
<br />
</body>
</html>