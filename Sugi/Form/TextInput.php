<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.19
 */

class TextInput extends BaseControl implements IControl
{
	public function __construct($name, $label)
	{
		parent::__construct($name);
		$this->setAttribute("type", "text");
		$this->label = $label;
	}

	public function __toString()
	{
		if ($this->label and !$this->getAttribute("id")) {
			$this->setAttribute("id", $this->form->name.'_'.$this->getName());
		}

		if ($this->label) {
			$class = ($this->required) ? ' class="required"' : '';
			$label = "\t<label for=\"".$this->getAttribute("id")."\"{$class}>{$this->label}</label>\n";
		}
		else {
			$label = "";
		}

		$input = "<input";
		foreach ($this->attributes as $attr => $value) {
			$input .= " {$attr}=\"{$value}\"";
		}
		$input .= " />";

		
		return "{$label}\t{$input}<br />\n";
	}

	public function getErrors()
	{
		$errs = array();
		if ($this->required and !$this->value()) {
			$errs[] = $this->required;
		}
		return $errs;
	}

}
