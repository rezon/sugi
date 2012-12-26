<?php
include "common.php";
use Sugi\Form;

$login = new Form("login");
// $login->method('GET');
//$login->action('form.php')->method('post')->setAttribute('id', 'loginid')->setAttribute('class', 'left');
$login->addText('username', 'Username:')->setRequired("Enter your username")->attribute('id', "bla");
$login->addPassword('password', 'Password:')->setRequired("Enter your password");
$login->addHidden("security", md5(time()));
$login->addSubmit('submit', 'Login');//->setAttribute('', "Vhod");//->id("loginbutton");

$reg = new Form();
// $reg->method('GET');
$reg->addText('username', "Username:")->setRequired("Please choose username");
$reg->addText("fname", "First Name:");
$reg->addText("lname", "Last Name:");
$reg->addText("email", "Your email:")->setRequired("Enter your email address");
$reg->addPassword("password", "Password:")->setRequired("Please choose password");
$reg->addPassword("password2", "Retype Password:")->setRequired("Please verify your password");
$reg->addSubmit("submit", "Register");

?>
<!doctype html>
<html>
<head>
	<style>
label.required  { color: maroon; }
input.error { background: #fdd; }
span.error {color: red;}
	</style>
</head>
<body>
<?php


if (!$login->submitted()) {
	echo $login;
} elseif (!$login->valid()) {
	echo "errors: "; var_dump($login->errors());
	echo $login;
}
else {
//	echo $login->submitted()->getValue();
	echo "STORE IN DB values: "; var_dump($login->data());
}
echo "<pre><code>" . htmlspecialchars($login) . "</code></pre>";

echo "<hr />";

//echo "<pre><code>" . htmlspecialchars($reg) . "</code></pre>";

if ($reg->valid()) {
	var_dump($reg->data());
} elseif ($reg->submitted()) {
	echo "values: "; var_dump($reg->data());
	echo "errors: "; var_dump($reg->errors());
	echo $reg;
} else {
	var_dump($reg->data());
	echo $reg;
}


?>
</body>
</html>
