<?php
include "common.php";
use Sugi\Form;

$login = new Form('login');
$login->method('GET');
//$login->action('form.php')->method('post')->setAttribute('id', 'loginid')->setAttribute('class', 'left');
$login->addText('username', 'Username:')->setRequired("Enter your username")->setAttribute('id', "bla");
$login->addPassword('password', 'Password:')->setRequired("Enter your password");
//$login->addSubmit('submit', 'Login');//->setAttribute('', "Vhod");//->id("loginbutton");

$reg = new Form('reg');
$reg->method('GET');
$reg->addText('username', "Username:")->setRequired("Please choose username");
$reg->addText("fname", "First Name:");
$reg->addText("lname", "Last Name:");
$reg->addText("email", "Your email:")->setRequired("Enter your email address");
$reg->addPassword("password", "Password:")->setRequired("Please choose password");
$reg->addPassword("password2", "Retype Password:")->setRequired("Please verify your password");
$reg->addSubmit("submit3", "Register");

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


//echo "<pre><code>" . htmlspecialchars($login) . "</code></pre>";

if (!$login->submitted()) {
	echo $login;
} elseif (!$login->isValid()) {
	echo "errors: "; var_dump($login->getErrors());
	echo $login;
}
else {
//	echo $login->submitted()->getValue();
	echo "STORE IN DB values: "; var_dump($login->getValues());
}

echo "<hr />";

//echo "<pre><code>" . htmlspecialchars($reg) . "</code></pre>";

if ($reg->isValid()) {
	var_dump($reg->getValues());
} elseif ($reg->submitted()) {
	echo "values: "; var_dump($reg->getValues());
	echo "errors: "; var_dump($reg->getErrors());
	echo $reg;
} else {
	var_dump($reg->getValues());
	echo $reg;
}


?>
</body>
</html>
