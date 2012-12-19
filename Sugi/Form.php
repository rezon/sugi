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
		// currently this is only used for child controlls (prefix for ID's)
		$this->name = $name;
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

	public function isSubmitted()
	{
		if (strcasecmp(Request::method(), $this->method())) return false;
		if (!count($this->controls)) return false;

		if (count($this->submitcontrols)) {
			$arr = (strcasecmp($this->method(), "post") == 0) ? $_POST : $_GET;
			foreach ($this->submitcontrols as $c) {
				if (Filter::key($c, $arr)) return true;
			}
		}
		return false;
	}

	public function isValid()
	{
		if (!$this->isSubmitted()) return false;
		// populate values with submitted data
		$this->setValues($this->getHttpData());

		foreach ($this->controls as $name => $control) {
			if ($control->getErrors()) return false;
		}

		return true;
	}

	public function getValues()
	{
		$values = array();
		foreach ($this->controls as $name => $control) {
			$values[$name] = $control->getValue();
		}
		return $values;
	}

	public function getHttpData()
	{
		$values = array();
		if ($this->isSubmitted()) {
			$arr = (strcasecmp($this->method(), "post") == 0) ? $_POST : $_GET;
			foreach ($this->controls as $name => $control) {
				$values[$name] = Filter::key($name, $arr);
			}
		}
		return $values;
	}

	public function setValues($values)
	{
		foreach ($this->controls as $name => $control) {
			if (isset($values[$name])) $control->setValue($values[$name]);
		}
	}

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
		$this->submitcontrols[] = $name;
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
