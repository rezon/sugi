<?php

include "common.php";

class A
{
	public function __toString()
	{
		return "A";
	}
}

class B
{
	protected function __construct(A $a, $p1, $p2 = "1", $p3 = "!")
	{
		echo "<p>Constructor B({$a}, {$p1} {$p2}{$p3})</p>";
	}

	public static function factory(A $a)
	{
		echo "<p>B::factory($a)</p>";
		return new self($a, "I am number");
	}

	public function __toString()
	{
		return "B";
	}
}

class C
{
	public function __construct(A $a, B $b, $p1, $p2)
	{
		echo "<p>Constructor C({$a}, {$b}, {$p1} {$p2})</p>";
	}

	public function __toString()
	{
		return "C";
	}
}

$c = \Sugi\DI::reflect("C", array("p1" => "Formula", "p2" => "One"));
