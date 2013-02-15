<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
 */
class Input extends BaseControl
{
	
	/**
	 * Sets control value
	 * 
	 * @param string
	 */
	protected function setValue($value)
	{
		return $this->setAttribute("value", $value);
	}

	/**
	 * Returns submitted data
	 * 
	 * @return string
	 */
	protected function getValue()
	{
		return $this->getAttribute("value");
	}

	public function readHttpData($data)
	{
		$this->setValue(Filter::key($this->getName(), $data));
	}

}
