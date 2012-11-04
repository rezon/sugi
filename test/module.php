<?php 
use Sugi\Module;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once ("../Sugi/Module.php");
include_once ("../Sugi/Lang.php");

Module::register('help', function () {
	return "Help wanted!";
});

Module::register('error', function ($msg) {
	echo "Error: $msg";
});

var_dump(Module::get('help'));
echo '<hr>';
Module::get('error', 'error message');
echo '<hr>';

class foo
{
	public $arr = array();
	public function bar()
	{
		echo 'bar';
	}
	public function foobar($param)
	{
		echo "param: $param";
	}
}

class fruit
{
	public function __construct($apples = 5, $oranges = 4)
	{
		$this->apples = $apples;
		$this->oranges = $oranges;
	}

	public function eat_apple()
	{
		$this->apples--;
		echo "<p>An apple was eaten. {$this->apples} apples left</p>";
	}

	public function eat_orange()
	{
		$this->oranges--;
		echo "<p>An orange was eaten. {$this->oranges} oranges left</p>";
	}
}

$f = new Foo();
Module::register('foo', $f);
Module::get('foo')->bar();
echo '<hr>';
Module::get('foo')->foobar('one');
echo '<hr>';
Module::register('foobar', array($f, 'foobar'));
Module::get('foobar', 'two');
echo '<hr>';

// note we have not been registered "fruit"
var_dump(Module::get('fruit'));
Module::get('fruit')->eat_apple();
// shortcut for fruit->eat_apple();
Module::register('apple', array(Module::get('fruit'), 'eat_apple'));
Module::get('fruit')->eat_apple();
Module::get('apple');
Module::get('fruit')->eat_orange();
echo '<hr>';

var_dump(Module::get('\Sugi\Lang'));
echo '<hr>';

class human
{
	public $sex;
	public function __construct($sex)
	{
		$this->set = $sex;
	}
}

var_dump(Module::get('human', 'female'));
echo '<hr>';
Module::set('me', 'human');
var_dump(Module::get('me', 'male'));

echo '<hr>';
echo '<pre>';
var_dump(Module::$registry);
echo '</pre>';
