<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Sugi\Form\Submit
 *
 * @extends Sugi\Form\Text
 */

use Sugi\Filter;

class Checkbox extends Text
{

	private $val;

	public function __construct($name, $label, $value = true)
	{
		parent::__construct($name, $label);
		$this->val = $value;
		$this->setAttribute("value", $value);
		$this->setAttribute("type", "checkbox");
	}

	public function error()
	{
		if ($this->error) return $this->error;
		
		if ($this->required and is_null($this->getAttribute("checked"))) {
			return $this->error = $this->required;
		}
		return false;
	}

	public function setValue($value) {
		if ($value == $this->getAttribute("value"))	$this->setAttribute('checked','checked');
		parent::setValue($value);
	}

	public function getValue() {
		return ($this->getAttribute("checked") == 'checked') ? $this->val : null;
	}

	public function readHttpData($data)
	{
		$data = Filter::key($this->getName(), $data);
		if (!is_null($data)) {
			$this->setAttribute('checked','checked');
		}
	}


	public function __toString()
	{
		if (!is_null($this->getValue())) $this->setAttribute('checked','checked');
		return parent::__toString();
	}

}
