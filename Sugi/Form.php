<?php namespace Sugi; 
/**
 * @package Sugi
 * @version 12.12.19
 */

/**
 * Form
 */
class Form
{
	public $name;
	protected $attributes = array();
	protected $controls = array();
	protected $submitControls = array();

	/**
	 * Form Constuctor
	 */
	public function __construct($name)
	{
		// currently this is only used for child controls (prefix for ID's)
		$this->name = $name;
		$this->attributes["name"] = $name;
		// Sets default action attribute (form request URI)
		$this->attributes["action"] = "";
		// Set default method attribute (form request method)
		$this->attributes["method"] = "POST";
	}

	/**
	 * Sets form submit URI (action attribute)
	 * 
	 * @param string
	 * @return \Sugi\Form
	 */
	public function setAction($action)
	{
		return $this->setAttribute("action", $action);
	}

	/**
	 * Returns action attribute (URI) of the form
	 * 
	 * @return string
	 */
	public function getAction()
	{
		return $this->getAttribute("action");
	}

	/**
	 * Sets/gets form action
	 * 
	 * @param null or string
	 * @return string or \Sugi\Form
	 */
	public function action($action = null)
	{
		if (is_null($action)) return $this->getAction();
		return $this->setAction($action);
	}

	/**
	 * Sets form request method
	 * 
	 * @param string
	 * @return \Sugi\Form
	 */
	public function setMethod($method)
	{
		return $this->setAttribute("method", $method);
	}

	/**
	 * Returns form request method
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getAttribute("method");
	}

	/**
	 * Sets/gets form request method
	 * 
	 * @param null or string
	 * @return string or \Sugi\Form
	 */
	public function method($method = null)
	{
		if (is_null($method)) return $this->getMethod();
		return $this->setMethod($method);
	}

	/**
	 * Sets form attribute
	 * 
	 * @param string $name
	 * @param string $value
	 * @return \Sugi\Form
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	/**
	 * Returns form attribute
	 * 
	 * @param string
	 * @return string
	 */
	public function getAttribute($name)
	{
		return Filter::key($name, $this->attributes);
	}	

	public function submitted($controlName = null)
	{
		if (strcasecmp(Request::method(), $this->method())) return false;
		if (!count($this->controls)) return false;

		$res = false;
		$arr = (strcasecmp($this->method(), "post") == 0) ? $_POST : $_GET;
		if (!is_null($controlName)) {
			if (Filter::key($controlName, $arr)) {
				$res = true;
			}
		}
		elseif (count($this->submitControls)) {
			foreach ($this->submitControls as $c) {
				if (Filter::key($c, $arr)) {
					$res = true;
					$controlName = $c;
					break;
				}
			}
		}
		else {
			if (Filter::key('submit', $arr)) {
				$res = true;
				$controlName = 'submit';
			}
		}

		if ($res) {
			$this->readHttpData($this->method());
			return $this->getControl($controlName);
		}

		return false;
	}

	public function isValid()
	{
		if (!$this->submitted()) return false;
		foreach ($this->controls as $name => $control) {
			if ($control->getErrors()) return false;
		}

		return true;
	}

	public function getValues()
	{
		$values = array();
		if ($this->submitted()) {
			foreach ($this->controls as $name => $control) {
				$values[$name] = $control->getValue();
			}
		}
		return $values;
	}

	// ??
	public function getErrors()
	{
		$errs = array();
		foreach ($this->controls as $name => $control) {
			if ($e = $control->getErrors()) {
				$errs[$name] = $e;
			}
		}
		return count($errs) ? $errs : false;
	}

	public function readHttpData($method)
	{
		foreach ($this->controls as $control) {
			$control->readHttpData($method);
		}
	}

	public function setValues($values)
	{
		foreach ($this->controls as $name => $control) {
			if (isset($values[$name])) $control->setValue($values[$name]);
		}
	}

	public function getControl($name)
	{
		return $this->controls[$name];
	}

	public function addControl(Form\Icontrol $control)
	{
		$this->controls[$control->getName()] = $control;
		return $control;
	}

	public function addText($name, $label = false)
	{
		return $this->addcontrol(new Form\TextInput($name, $label))->form($this);
	}

	public function addPassword($name, $label = false)
	{
		return $this->addcontrol(new Form\PasswordInput($name, $label))->form($this);
	}

	public function addSubmit($name, $value = false)
	{
		$this->submitControls[] = $name;
		return $this->addcontrol(new Form\Submit($name, $value))->form($this);
	}

	/**
	 * Simple HTML form rendering
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$form = "<form";
		foreach ($this->attributes as $attr => $value) {
			$form .= " {$attr}=\"{$value}\"";
		}
		$form .= ">\n";
		foreach ($this->controls as $control)	{
			$form .= $control;
		}
		$form .= "</form>";

		return $form;
	}
}
