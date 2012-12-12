<?php
	include "common.php";
	setcookie('cs', 'alabalacookie');
	setcookie('ci', 1);
?>
<!doctype html>
<html lang="en">
<head>
	<title>Sugi Tests</title>
	<meta charset="utf-8" />
</head>
<body>
	<form method="post" action="filter.php?gs=alabala&amp;gi=1">
		<input name="ps" value="postalabala" readonly="readonly" hidden="hidden" />
		<input name="pi" value="1" readonly="readonly" hidden="hidden" />
		<input type="submit" value="Filter tests" />
		<input type="button" value="Translations" onclick="document.location='lang.php'" />
		<input type="button" value="Request" onclick="document.location='request.php?test=key&amp;more=tests'" />
		<input type="button" value="URI" onclick="document.location='uri.php'" />
		<input type="button" value="Route" onclick="document.location='route.php'" />
		<input type="button" value="Session (default)" onclick="document.location='session.php'" />
		<input type="button" value="Session (file)" onclick="document.location='session.php?type=file'" />
		<input type="button" value="Session (database)" onclick="document.location='session.php?type=database'" />
		<input type="button" value="Database::sqlite" onclick="document.location='sqlite.php'" />
		<input type="button" value="Database::sqlite3" onclick="document.location='sqlite3.php'" />
		<input type="button" value="Database::mysqli" onclick="document.location='mysqli.php'" />
		<input type="button" value="Database::pgsql" onclick="document.location='pgsql.php'" />
		<input type="button" value="Logger" onclick="document.location='logger.php'" />
		<input type="button" value="Cron" onclick="document.location='cron.php'" />
		<input type="button" value="STE" onclick="document.location='ste.php'" />
	</form>
</body>
</html>
