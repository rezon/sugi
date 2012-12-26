<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Sugi\Form\Submit
 *
 * @extends Sugi\Form\Text
 */
class Submit extends Text
{
	public function __construct($name, $label)
	{
		parent::__construct($name, false);
		$this->label = false;

		$this->setAttribute("value", $label);
		$this->setAttribute("type", "submit");
	}

	public function setValue($value)
	{
		// cannot change value
		return $this;
	}

	public function error()
	{
		return false;
	}
}
