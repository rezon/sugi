<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.19
 */

use Sugi\Filter;

/**
 * \Sugi\Form\BaseControl
 */
class BaseControl
{
	protected $form;
	protected $attributes = array();
	protected $label;
	protected $required;
	protected $value;

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
	public function setValue($value)
	{
		return $this->setAttribute("value", $value);
	}

	/**
	 * Returns submitted data
	 * 
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
		// return $this->getAttribute("value");
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
	public function setAttribute($name, $value)
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
	public function getAttribute($name)
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

	public function readHttpData($method)
	{
		$arr = (strcasecmp($method, "post") == 0) ? $_POST : $_GET;
		$this->value = Filter::key($this->getName(), $arr);
		$this->setValue($this->value);
		return $this->value;
	}

	public function setRequired($message = 'Required field')
	{
		$this->required = $message;

		return $this;
	}
}
