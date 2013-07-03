<?php namespace Sugi\Form;
/**
 * @package Sugi
 * @version 13.03.21
 */


/**
 * \Sugi\Form\SelectOptions
 */
class SelectOptions {

	protected $options;

	public function __construct($optArray = array()) {
		foreach ($optArray as $key => $opt) {
			if (self::isOptGroup($key,$opt)) {
				$this->options[] = new SelectOptionGroup($key,$opt);	
			}	else {
				$this->options[] = new SelectOption($key,$opt);	
			}
		}
	}

	public function __toString() {
		return implode('', $this->options);
	}

	private static function isOptGroup($key,$opt) {
		return is_string($key) && !empty($key) && is_array($opt);
	}

	public function getOption($value) {

		foreach ($this->options as $option) {
			if (get_class($option) == 'Sugi\Form\SelectOptionGroup') {
				foreach ($option->options as $opt) {
					if ((string)$opt->getValue() === (string)$value)
						return $opt;	
				}
			} else {
				if ((string)$option->getValue() === (string)$value)
					return $option;	
			}
		}
		return null;
	}	

	public function size() {
		$size = 0; 
		foreach ($this->options as $option) {
			if (get_class($option) == 'Sugi\Form\SelectOptionGroup') {
				$size += (count($option->options)+1);
			} else {
				$size += 1;
			}	
		}
		return $size;
	}	

}


class SelectOptionGroup {

	protected $label;

	public $options;

	public function __construct($label = "", $optArray = array()) {
		$this->label = $label;
		foreach($optArray as $key => $opt) {
			$this->options[] = new SelectOption($key,$opt);	
		}
	}

	public function __toString() {
		$optgrp = '<optgroup label="'.$this->label.'">';
		$optgrp .= implode('', $this->options);
		return $optgrp.'</optgroup>';
	}
	
}


class SelectOption {

	protected $value;
	protected $label;
	protected $attributes = array();

	public function getValue() {
		return $this->value;
	}	

	public function __construct($key,$option) {

		if (is_string($option)) {
			$this->value = $key;
			$this->label = $option;
		} else if (is_array($option)) {
			$this->value      = $key;
			$this->label      = $option['label'];
			unset($option['label']);
			$this->attributes = $option;
		} else {
			// ???
		}

	}

	public function setSelected() {
		$this->attributes["selected"] = 'selected';
		return $this;
	}

	public function setDisabled() {
		$this->attributes["disabled"] = 'disabled';
		return $this;
	}

	protected function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	protected function getAttribute($name)
	{
		return Filter::key($name, $this->attributes);
	}

	public function attribute($name, $value = null)
	{
		if (is_null($value)) return $this->getAttribute($name);
		return $this->setAttribute($name, $value);
	}

	public function __toString() {
		if (empty($this->value) && empty($this->label))	{
			return '';
		} else {
			$attr = '';
			foreach($this->attributes as $attKey => $attVal) {
				$attr .= " {$attKey}=\"{$attVal}\"";
			}
			return "<option value=\"{$this->value}\" {$attr}>{$this->label}</option>\n";
		}
	}
}
