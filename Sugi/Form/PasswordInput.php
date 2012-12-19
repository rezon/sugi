<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.19
 */

/**
 * Sugi\Form\PasswordInput
 * 
 * @extends TextInput
 */
class PasswordInput extends TextInput
{
	public function __construct($name, $label)
	{
		parent::__construct($name, $label);
		
		$this->setAttribute("type", "password");
	}
}
