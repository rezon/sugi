<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 12.12.21
 */

use Sugi\Filter;

/**
 * \Sugi\Form\Input
 */
class Textarea extends BaseControl implements IControl
{ 

	protected $value = null;

	/**
	 * Can't instantiate BaseControl
	 * 
	 * @param string
	 */
	public function __construct($name, $label)
	{
		$this->attributes['name'] = $name;
		$this->label = $label;
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
		$textarea = "<textarea";
		foreach ($this->attributes as $attr => $value) {
			if ($this->error and ($attr == 'class')) {
				$value .= " error";
				$classAdded = true;
			}
			$textarea .= " {$attr}=\"{$value}\"";
		}
		if ($this->error and !$classAdded) {
			$textarea .= ' class="error"';
		}
		$textarea .= ">{$this->getValue()}</textarea>";

		$error = $this->error ? "<span class=\"error\">{$this->error}</span>" : "";
		
		return "{$label}\t{$textarea}{$error}<br />\n";
	}
}
