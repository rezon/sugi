<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Sugi\Form\Text
 *
 * @extends  Sugi\Form\Input
 * @implements Sugi\Form\IControl
 */
class Text extends Input implements IControl
{
	public function __construct($name, $label)
	{
		parent::__construct($name);
		$this->setAttribute("type", "text");
		$this->label = $label;
	}

	public function error()
	{
		if ($this->required and !$this->value()) {
			return $this->error = $this->required;
		}
		return false;
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
		$input = "<input";
		foreach ($this->attributes as $attr => $value) {
			if ($this->error and ($attr == 'class')) {
				$value .= " error";
				$classAdded = true;
			}
			$input .= " {$attr}=\"{$value}\"";
		}
		if ($this->error and !$classAdded) {
			$input .= ' class="error"';
		}
		$input .= " />";

		$error = $this->error ? "<span class=\"error\">{$this->error}</span>" : "";
		
		return "{$label}\t{$input}{$error}<br />\n";
	}

}
