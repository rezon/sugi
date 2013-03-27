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
		if ($this->label) {
			if (!$this->getAttribute("id")) $this->setAttribute("id", $this->form->name() ? $this->form->name() . "_" . $this->getName() : $this->getName());
			$class = ($this->required) ? ' class="required"' : '';
			$label = "\t<label for=\"".$this->getAttribute("id")."\"{$class}>{$this->label}</label>\n";
		}
		else {
			$label = "";
		}

		$classAdded = false;

		$input = "";

		foreach ($this->values as $key => $val) {
			$selected = ($key == $this->getValue()) ? "checked='checked'" : '' ;

			$input .= "<label><input";
			foreach ($this->attributes as $attr => $value) {
				if ($attr != 'value') {
					if ($this->error and ($attr == 'class')) {
						$value .= " error";
						$classAdded = true;
					} 
					$input .= " {$attr}=\"{$value}\"";
				}
			}

			if ($this->error and !$classAdded) {
				$input .= ' class="error"';
			}

			$input .= " value =\"{$key}\"";
			$input .= " {$selected} />{$val}</label>\n";

		}
		
		$error = $this->error ? "<span class=\"error\">{$this->error}</span>" : "";

		return "{$label}\t{$input}{$error}<br />\n";
	}
}
