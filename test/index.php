<?php
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
<form method="post" action="filter.php?gs=alabala&gi=1">
	<input name="ps" value="postalabala" readonly="readonly" hidden="hidden" />
	<input name="pi" value="1" readonly="readonly" hidden="hidden" />
	<input type="submit" value="Filter tests" />
	<input type="button" value="File tests" onclick="document.location='file.php'" />
	<input type="button" value="Request" onclick="document.location='request.php'" />
	<input type="button" value="URI" onclick="document.location='uri.php'" />
	<input type="button" value="Route" onclick="document.location='route.php'" />
	<input type="button" value="App" onclick="document.location='app.php'" />
	<input type="button" value="Database::sqlite" onclick="document.location='sqlite.php'" />
	<input type="button" value="Database::sqlite3" onclick="document.location='sqlite3.php'" />
	<input type="button" value="Database::mysqli" onclick="document.location='mysqli.php'" />
	<input type="button" value="Database::pgsql" onclick="document.location='pgsql.php'" />
</form>
</body>
</html>
