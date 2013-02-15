<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * \Sugi\Form\BaseControl
 */

use Sugi\Filter;

class BaseControl
{

	protected $form;
	protected $attributes = array();
	protected $label;
	protected $required;
	protected $error = false;
	protected $rules =array();

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
	 * Sets control attribute
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
	 * Returns control attribute
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

	/**
	 * Sets control value
	 * 
	 * @param string
	 */
	protected function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Returns submitted data
	 * 
	 * @return string
	 */
	protected function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets/gets control's value
	 */
	public function value($value = null)
	{
		if (is_null($value)) {
			return $this->getValue();
		} else {
			$this->setValue($value);
			return $this;
		}
	}

	public function setError($e)
	{
		return $this->error = $e;
	}

	public function error()
	{
		if ($this->error) return $this->error;

		$val = $this->value();

		if (!empty($val)) {
			if ($errors = $this->validate()) {
				return $this->error = $errors;
			}
		}

		if (empty($val) && $this->required) return $this->error = $this->required;

		return false;
	}

	public function rule($type, $error = null, $condition = null) {

		if (empty($error)) {
			switch ($type) {
				case 'required' : $error = 'Required Field'; break;
				case 'email'    : $error = 'Invalid email'; break;
				case 'url'      : $error = 'Invalid url'; break;
				default: $error = 'Unknown error'; break;
			}				
		}

		if (!empty($type)) {
			$this->rules[] = array(
				'type'  => $type,
				'error' => $error,
				'condition' => $condition
			);
			if ($type == 'required') $this->required = $error;
		}
		return $this;
	}

	/**
	 * validations
	 */
	protected function validate() {
		$errors = array();
		foreach ($this->rules as $rule) {
			$func = "_validate_".$rule['type'];
			if (is_callable(array($this,$func)) && !$this->$func($rule['condition'])) {
				$errors[] = $rule['error'];
			}
		}
		return (empty($errors)) ? false : implode(',<br/>', $errors);
	}


	/*
	 * validate required
	 */

	protected function _validate_required($condition) {
		$val = $this->value();
		var_dump($this->getName(),$val);
		return !empty($val);
	}


	/*
	 * validate with regexp
	 */

	protected function _validate_regexp($condition) {
		$result  = (false !== filter_var($this->value(), FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=> $condition))));
		return $result;
	}

	/*
	 * validate with callback
	 */

	protected function _validate_callback($condition) {
		$result = call_user_func($condition,$this->value());
		if (gettype($result) != 'boolean') $result = false; 
		return $result;
	}


	/*
	 * validate email
	 */

	protected function _validate_email($check_mx_record = false) {
		return (false !== Filter::email($this->value(), false, $check_mx_record));
	}

	/*
	 * validate url
	 */

	protected function _validate_url() {
		return (false !== Filter::url($this->value(), false));
	}

}