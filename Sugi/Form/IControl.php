<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.19
 */


interface IControl
{
	/**
	 * Sets control's value
	 * 
	 * @param string
	 */
	public function setValue($value);

	/**
	 * Returns control's value
	 * 
	 * @return string
	 */
	public function getValue();

	/**
	 * Returns errors corresponding to the control
	 * 
	 * @return array
	 */
	public function getErrors();

	/**
	 * Text
	 * 
	 * @return string [description]
	 */
	public function __toString();
}
