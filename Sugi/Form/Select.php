<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
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
		$select = "<select";
		foreach ($this->attributes as $attr => $value) {
			if ($this->error and ($attr == 'class')) {
				$value .= " error";
				$classAdded = true;
			}
			$select .= " {$attr}=\"{$value}\"";
		}
		if ($this->error and !$classAdded) {
			$select .= ' class="error"';
		}
		$select .= ">\n";

		foreach ($this->values as $key => $value) {
			$selected = ($key == $this->getValue()) ? "selected='selected'" : '' ;
			$select .= "		<option value=\"{$key}\" {$selected}>{$value}</option>\n";
		}

		$select .= "	</select>";

		$error = $this->error ? "<span class=\"error\">{$this->error}</span>" : "";
		
		return "{$label}\t{$select}{$error}<br />\n";
	}
}
