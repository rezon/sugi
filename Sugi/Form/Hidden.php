<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Sugi\Form\Hidden
 *
 * @extends Sugi\Form\Text
 */
class Hidden extends Text
{
	public function __construct($name, $value)
	{
		parent::__construct($name, false);

		$this->setAttribute("value", $value);
		$this->setAttribute("type", "hidden");
	}

	public function error()
	{
		return false;
	}
}
