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

	public function __toString()
	{

		$label = $this->getLabel();

		$classAdded = false;
		$control = "<input";
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
		$control .= " />";

		$error = $this->error ? $this->error : "";
		
		return $this->renderControl(compact('label','control','error'));
	}




}
