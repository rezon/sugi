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
	protected $labelAttributes = array();
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
	 * Sets/gets label's attribute
	 */
	public function labelAttribute($name, $value = null)
	{
		if (is_null($value)) return Filter::key($name, $this->labelAttributes);
		$this->labelAttributes[$name] = $value;
		return $this;
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


	public function renderControl($params) {
		$params['error_class'] = $this->form->errorClass();
		if (isset($params['error']) && !empty($params['error'])) {
			$params['error'] = preg_replace_callback(
				'/\{(error|error_class)\}/',function ($m) use ($params) {return $params[$m[1]];}, 
				$this->form->formErrorTemplate()
			);
		}
		return preg_replace_callback('/\{(\w+)\}/',
			function ($m) use ($params) {return $params[$m[1]];}, 
			$this->form->controlTemplate());
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

	public function rule($type, $error = null) {

		if (empty($error)) {
			switch ($type) {
				case 'required' : $error = 'Required Field'; break;
				case 'email'    : $error = 'Invalid email'; break;
				case 'url'      : $error = 'Invalid url'; break;
				default: $error = 'Unknown error'; break;
			}				
		}

		$args = array_slice(func_get_args(),2);

		if (!empty($type)) {
			$this->rules[] = array(
				'type'  => $type,
				'error' => $error,
				'condition' => $args
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
			if (is_callable(array($this,$func)) && !call_user_func_array(array($this,$func), $rule['condition'])) {
				$errors[] = $rule['error'];
			}
		}
		return (empty($errors)) ? false : implode(',<br/>', $errors);
	}


	/*
	 * validate required
	 */

	protected function _validate_required() {
		$val = $this->value();
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


	/*
	 * validate min length 
	 */
	protected function _validate_min_length($minLength = false) {
		return (!empty($minLength) AND (mb_strlen($this->value(), "UTF-8") >= $minLength));
	}

	/*
	 * validate max length
	 */
	protected function _validate_max_length($maxLength = false) {
		return (!empty($maxLength) AND (mb_strlen($this->value(), "UTF-8") <= $maxLength));
	}

	/*
	 * validate length
	 */
	protected function _validate_length($min = false, $max = false)	{
	  return (mb_strlen($this->value(), "UTF-8")  >= $min AND mb_strlen($this->value(), "UTF-8") <= $max);
	}

	/*
	 * validate min 
	 */
	protected function _validate_min($min = false) {
		return (!empty($min) AND ((float)$this->value() >= $min));
	}

	/*
	 * validate max
	 */
	protected function _validate_max($max = false) {
		return (!empty($max) AND ((float)$this->value() <= $max));
	}

	/*
	 * validate range
	 */
	protected function _validate_range($min = false, $max = false)	{
	  return ((float)$this->value() >= $min AND (float)$this->value() <= $max);
	}

	/*
	 * get label string
	 */

	protected function getLabel() {
		if ($this->label) {
			if (!$this->getAttribute("id")) $this->setAttribute("id", $this->form->name() ? $this->form->name() . "_" . $this->getName() : $this->getName());
			
			$attrs = "";
			unset($this->labelAttributes['for']);
			foreach ($this->labelAttributes as $attr => $value) {
				if ($attr == 'class' && $this->required) {
					$value .= " required";
				}
				$attrs .= " {$attr}=\"{$value}\"";
			}

			$label = "\t<label for=\"".$this->getAttribute("id")."\"{$attrs}>{$this->label}</label>\n";
		}
		else {
			$label = "";
		}
		return $label;
	}

}