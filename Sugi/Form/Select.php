<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;
use Sugi\Form\SelectOptions;

/**
 * \Sugi\Form\Select
 */
class Select extends BaseControl implements IControl
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


	public function readHttpData($data)
	{
		$this->setValue(Filter::key($this->getName(), $data));
	}

	public function __toString()
	{
		$label = $this->getLabel();

		$classAdded = false;
		$control = "<select";
		foreach ($this->attributes as $attr => $value) {
			if ($this->error and ($attr == 'class')) {
				$value .= " ".$this->form->errorClass();
				$classAdded = true;
			}
			$control .= " {$attr}=\"{$value}\"";
		}
		if ($this->error and !$classAdded) {
			$control .= " class=\"{$this->form->errorClass()}\"";
		}
		$control .= ">\n";

		$this->values->getOption($this->getValue())->setSelected();
		$control .= $this->values;
		$control .= "	</select>";

		$error = $this->error ? $this->error : "";
		
		return $this->renderControl(compact('label','control','error'));
	}
}
