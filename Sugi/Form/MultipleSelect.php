<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
 */
class MultipleSelect extends BaseControl implements IControl
{ 

	protected $values = array();
	protected $value = null;

	/**
	 * Can't instantiate BaseControl
	 * 
	 * @param string
	 */
	public function __construct($name, $label, $values = array())
	{
		$this->attributes['name'] = $name;
		$this->label = $label;
		$this->values = new SelectOptions($values);
	}
	
	public function getOption($value) {
		return $this->values->getOption($value);
	}

	protected function getValue()
	{
		return is_null($this->value) ? array() : $this->value;
	}

	public function readHttpData($data)
	{
		$this->setValue(Filter::key($this->getName(), $data));
	}

	public function __toString()
	{
		$label = $this->getLabel();

		if (!$this->getAttribute("size")) {
			$this->setAttribute("size", $this->values->size());
		}

		$classAdded = false;
		$control = "<select multiple=\"multiple\"";
		foreach ($this->attributes as $attr => $value) {
			if ($attr != 'name') {
				if ($this->error and ($attr == 'class')) {
					$value .= " ".$this->form->errorClass();
					$classAdded = true;
				}
				$control .= " {$attr}=\"{$value}\"";
			}
		}
		if ($this->error and !$classAdded) {
			$control .= " class=\"{$this->form->errorClass()}\"";
		}
		$control .= "name=\"{$this->getName()}[]\"";
		$control .= ">\n";

		foreach ($this->value as $key => $value) {
			$this->values->getOption($value)->setSelected();
		}
				
		$control .= $this->values;
		$control .= "	</select>";


		$error = $this->error ? $this->error : "";
		
		return $this->renderControl(compact('label','control','error'));
	}
}
