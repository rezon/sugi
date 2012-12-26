<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Sugi\Form\Password
 * 
 * @extends Sugi\Form\Text
 */
class Password extends Text
{
	public function __construct($name, $label)
	{
		parent::__construct($name, $label);
		
		$this->setAttribute("type", "password");
	}
}
