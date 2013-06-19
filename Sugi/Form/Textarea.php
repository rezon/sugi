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
		$label = $this->getLabel();

		$classAdded = false;
		$control = "<textarea";
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
		$control .= ">{$this->getValue()}</textarea>";

		$error = $this->error ? $this->error : "";
		
		return $this->renderControl(compact('label','control','error'));
	}
}
