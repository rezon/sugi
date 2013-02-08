<?php 
use Sugi\Module;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once ("../Sugi/Facade.php");
include_once ("../Sugi/Files.php");
include_once ("../Sugi/Config.php");
include_once ("../Sugi/Module.php");

function println($text) { 
	if (PHP_SAPI === 'cli') {
		echo($text."\n");
	} else {
		echo "<p>{$text}</p>";
	}
}


class fruit
{
	public function __construct($conf)
	{
		println(' !!! fruit constructor');
		$this->apples = $conf['apples'];
		$this->oranges = $conf['oranges'];
	}

	public function eat_apple()
	{
		$this->apples--;
		println("An apple was eaten. {$this->apples} apples left");
	}

	public function eat_orange()
	{
		$this->oranges--;
		println("An orange was eaten. {$this->oranges} oranges left</p>");
	}
}

println("Module::get('fruit')");
$m = Module::get('fruit');
$m->eat_apple();

println("Module::get('fruit')");
$m = Module::get('fruit');
$m->eat_apple();

println("Module::factory('fruit')");
$m = Module::factory('fruit');
$m->eat_apple();

Module::set('apples', function () {
	return new fruit(array('oranges' => 0, 'apples'=>12));
});

echo "\n\n";
$m = Module::get('apples');
$m->eat_apple();
echo "\n\n";
$m = Module::get('apples');
$m->eat_apple();
echo "\n\n";
$m = Module::get('apples');
$m->eat_apple();
echo "\n\n";
$m = Module::factory('apples');
$m->eat_apple();
echo "\n\n";

Module::set('oranges', 'fruit');
$m = Module::get('oranges');
$m->eat_apple();
echo "\n\n";



println("Module::set('fruit', closure)");
Module::set('fruit', function($a = 100, $b = 100) {
	return new fruit(array('apples' => $a, 'oranges' => $b ));
});
$m = Module::factory('fruit');
$m->eat_apple();

println("Module::get('fruit')");
$m = Module::get('fruit');
$m->eat_apple();


println("Module::factory('fruit', array(150,150))");
$m = Module::factory('fruit', array(150,150));
$m->eat_apple();


println("Module::get('fruit')");
$m = Module::get('fruit');
$m->eat_apple();