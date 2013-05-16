<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
 */
class Radio extends BaseControl implements IControl
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

		$this->setAttribute('name', $name);
		$this->setAttribute('type', 'radio');
		$this->label = $label;
		$this->values = $values;

	}
	
	public function readHttpData($data)
	{
		$this->setValue(Filter::key($this->getName(), $data));
	}

	public function __toString()
	{
		$label = $this->getLabel();

		$classAdded = false;

		$control = "";

		foreach ($this->values as $key => $val) {
			$selected = ($key == $this->getValue()) ? "checked='checked'" : '' ;

			$control .= "<label><input";
			foreach ($this->attributes as $attr => $value) {
				if ($attr != 'value') {
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

			$control .= " value =\"{$key}\"";
			$control .= " {$selected} />{$val}</label>\n";

		}
		
		$error = $this->error ? $this->error : "";
		
		return $this->renderControl(compact('label','control','error'));
	}
}
