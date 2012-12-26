<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Interface for the Form Controls
 */
interface IControl
{
	/**
	 * sets/gets data (value) for the control
	 * 
	 * @return string
	 */
	public function value($value = null);

	/**
	 * Reads GET/POST data
	 *
	 * @param array $data - GET or POST data
	 */
	public function readHttpData($data);

	/**
	 * Return first error corresponding to the control
	 * 
	 * @return string
	 */
	public function error();

	/**
	 * Text
	 * 
	 * @return string [description]
	 */
	public function __toString();
}
