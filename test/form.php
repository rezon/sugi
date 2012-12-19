<?php
include "common.php";
use Sugi\Form;

$login = new Form('login');
// $login->method('GET');
$login->addText('username', 'Username:')->setRequired("Enter your username");
$login->addPassword('password', 'Password:')->setRequired("Enter your password");
$login->addSubmit('submit', 'Login');//->id("loginbutton");
//$login->action('form.php')->method('post')->setAttribute('id', 'loginid')->setAttribute('class', 'left');

$reg = new Form('reg');
$reg->addText('username', "Username:")->setRequired("Please choose username");
$reg->addText("fname", "First Name:");
$reg->addText("lname", "Last Name:");
$reg->addText("email", "Your email:")->setRequired("Enter your email address");
$reg->addPassword("password", "Password:")->setRequired("Please choose password");
$reg->addPassword("password2", "Retype Password:")->setRequired("Please verify your password");
$reg->addSubmit("register", "Register");

?>
<!doctype html>
<html>
<head>
	<style>
label.required  { color: maroon; }
input.error { color: red; }
	</style>
</head>
<body>
<?php

// echo "<pre><code>" . htmlspecialchars($login) . "</code></pre>";

if ($login->isValid()) {
	var_dump($login->getValues());
} elseif ($login->isSubmitted()) {
	//echo "values: "; var_dump($login->getValues());
	echo "errors: "; var_dump($login->getErrors());
	echo $login;
} else {
	echo $login;
}

echo "<hr />";

//echo "<pre><code>" . htmlspecialchars($reg) . "</code></pre>";

if ($reg->isValid()) {
	var_dump($reg->getValues());
} elseif ($reg->isSubmitted()) {
	// echo "values: "; var_dump($reg->getValues());
	echo "errors: "; var_dump($reg->getErrors());
	echo $reg;
} else {
	echo $reg;
}


?>
</body>
</html>
