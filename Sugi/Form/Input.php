<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
 */
class Input
{
	protected $form;
	protected $attributes = array();
	protected $label;
	protected $required;
	protected $error = false;

	/**
	 * Can't instantiate BaseControl
	 * 
	 * @param string
	 */
	protected function __construct($name)
	{
		$this->attributes['name'] = $name;
	}

	/**
	 * Returns name attribute
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->getAttribute('name');
	}

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

	/**
	 * Sets/gets control's value
	 */
	public function value($value = null)
	{
		if (is_null($value)) return $this->getValue();
		return $this->setValue($value);
	}

	/**
	 * Sets form attribute
	 * 
	 * @param string $name
	 * @param string $value
	 * @return \Sugi\Form\IControl
	 */
	protected function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	/**
	 * Returns form attribute
	 * 
	 * @param string
	 * @return string
	 */
	protected function getAttribute($name)
	{
		return Filter::key($name, $this->attributes);
	}

	/**
	 * Sets/gets control's attribute
	 */
	public function attribute($name, $value = null)
	{
		if (is_null($value)) return $this->getAttribute($name);
		return $this->setAttribute($name, $value);
	}

	/**
	 * Sets/gets control's parent form
	 */
	public function form($form = null)
	{
		if (is_null($form)) return $this->form;
		$this->form = $form;
		return $this;
	}

	public function readHttpData($data)
	{
		$this->setValue(Filter::key($this->getName(), $data));
	}

	public function setRequired($message = 'Required field')
	{
		$this->required = $message;

		return $this;
	}
}
