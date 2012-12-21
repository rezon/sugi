<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.19
 */

class Submit extends TextInput
{
	public function __construct($name, $label)
	{
		parent::__construct($name, $label);
		$this->label = false;

		$this->setAttribute("value", $label);
		$this->setAttribute("type", "submit");
	}

	/**
	 * Sets control value
	 * 
	 * @param string
	 */
	public function setValue($value)
	{
		// cannot change value
		return $this;
	}

	public function getErrors()
	{
		return false;
	}
}
