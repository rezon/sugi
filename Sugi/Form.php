<?php namespace Sugi; 
/**
 * @package Sugi
 * @version 12.12.21
 */

/**
 * Form
 */
class Form
{
	protected $attributes = array();
	protected $controls = array();
	protected $submits = array();
	protected $submitted;
	protected $errors;


	protected $errorClass = 'error';
	protected $formErrorTemplate = "<span class=\"error\">{error}</span>";
	protected $controlTemplate = "{label}\t{control}{error}\n";

	/**
	 * Form Constuctor
	 * 
	 * @param string $name
	 */
	public function __construct($name = '', $options = array())
	{
		// This is also used for child controls (prefix for ID's)
		if ($name) $this->attributes["name"] = $name;
		// Sets default action attribute (form request URI)
		$this->attributes["action"] = "";
		// Set default method attribute (form request method)
		$this->attributes["method"] = "POST";

		if (isset($options['error_class']) && !(empty($options['error_class'])) ) {
			$this->errorClass($options['error_class']);
		}

		if (isset($options['form_error_tpl']) && !(empty($options['form_error_tpl'])) ) {
			$this->formErrorTemplate($options['form_error_tpl']);
		}

		if (isset($options['control_tpl']) && !(empty($options['control_tpl'])) ) {
			$this->controlTemplate($options['control_tpl']);
		}
	}


	public function errorClass($class = null) {
		if (!is_null($class)) {
			$this->errorClass = $class;
			return $this;
		}
		return $this->errorClass;
	}

	public function formErrorTemplate($tpl = null) {
		if (!is_null($tpl)) {
			$this->formErrorTemplate = $tpl;
			return $this;
		}
		return $this->formErrorTemplate;
	}

	public function controlTemplate($tpl = null) {
		if (!is_null($tpl)) {
			$this->controlTemplate = $tpl;
			return $this;
		}
		return $this->controlTemplate;
	}


	/**
	 * Sets form name
	 * 
	 * @param string $name
	 * @return \Sugi\Form
	 */
	protected function setName($name)
	{
		return $this->setAttribute("name", $action);
	}

	/**
	 * Returns form name attribute
	 * 
	 * @return string
	 */
	protected function getName()
	{
		return $this->getAttribute("name");
	}

	/**
	 * Sets/gets form name
	 */
	public function name($name = null)
	{
		if (is_null($name)) return $this->getName();
		return $this->setName($name);
	}

	/**
	 * Sets form submit URI (action attribute)
	 * 
	 * @param string
	 * @return \Sugi\Form
	 */
	protected function setAction($action)
	{
		return $this->setAttribute("action", $action);
	}

	/**
	 * Returns action attribute (URI) of the form
	 * 
	 * @return string
	 */
	protected function getAction()
	{
		return $this->getAttribute("action");
	}

	/**
	 * Sets/gets form action
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
	protected function setMethod($method)
	{
		return $this->setAttribute("method", $method);
	}

	/**
	 * Returns form request method
	 * 
	 * @return string
	 */
	protected function getMethod()
	{
		return $this->getAttribute("method");
	}

	/**
	 * Sets/gets form request method
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
	protected function setAttribute($name, $value)
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
	protected function getAttribute($name)
	{
		return Filter::key($name, $this->attributes);
	}

	/**
	 * Sets/gets form attribute
	 */
	public function attribute($name, $value = null)
	{
		if (is_null($value)) return $this->getAttribute($name);
		return $this->setAttribute($name, $value);
	}

	/**
	 * Generates unique identifier for the form.
	 * Used to check the form is submitted.
	 * 
	 * @return string
	 */
	protected function uid()
	{
		if ($n = $this->getName()) return $n;
		$str = "";
		foreach ($this->controls as $name => $c) {
			$str .= $name;
		}
		return "form_" . abs(crc32($str));
	}

	/**
	 * Checks if the form was submitted.
	 * 
	 * @return boolean
	 */
	public function submitted()
	{
		if (!is_null($this->submitted)) return $this->submitted;
		if (strcasecmp(Request::method(), $this->method())) return $this->submitted = false;
		if (!count($this->controls)) return $this->submitted = false;

		$arr = (strcasecmp($this->method(), "post") == 0) ? $_POST : $_GET;
		if (isset($arr[$this->uid()])) {
			$this->readHttpData($arr);
			return $this->submitted = true;
		}
		return $this->submitted = false;
	}

	
	/**
	 * Checks if the form was submitted.
	 * 
	 * @return Corntrol
	 */
	public function submitter()
	{
		foreach ($this->data() as $name => $value) {
			if ($this->controls[$name]->attribute('type') == 'submit') return $this->controls[$name];			
		}
		return null;
	}

	/**
	 * Checks the form was submitted and the submitted data meets all criteria
	 * 
	 * @return boolean
	 */
	public function valid()
	{
		if (!$this->submitted()) return false;
		return count($this->errors()) === 0;
	}

	/**
	 * Returns field errors
	 * 
	 * @return array
	 */
	public function errors()
	{
		if (!is_null($this->errors)) return $this->errors;
		$this->errors = array();
		foreach ($this->controls as $name => $control) {
			if ($e = $control->error()) {
				$this->errors[$name] = $e;
			}
		}
		return $this->errors;
	}

	/**
	 * Returns form data
	 * 
	 * @return array
	 */
	public function data()
	{
		$values = array();
		if ($this->submitted()) {
			$arr = (strcasecmp($this->method(), "post") == 0) ? $_POST : $_GET;
			foreach ($this->controls as $name => $control) {
				if ($control->attribute('type') != 'submit' || isset($arr[$name])) {
					$values[$name] = $control->value();
				}
			}
		}
		return $values;
	}

	protected function readHttpData($data)
	{
		foreach ($this->controls as $control) {
			$control->readHttpData($data);
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
		return Filter::key($name, $this->controls);
	}

	public function getControls()
	{
		return $this->controls;
	}

	public function addControl(Form\Icontrol $control)
	{
		$this->controls[$control->getName()] = $control;
		return $control;
	}

	public function addText($name, $label = false)
	{
		return $this->addcontrol(new Form\Text($name, $label))->form($this);
	}

	public function addPassword($name, $label = false)
	{
		return $this->addcontrol(new Form\Password($name, $label))->form($this);
	}

	public function addSubmit($name, $value)
	{
		return $this->addcontrol(new Form\Submit($name, $value))->form($this);
	}

	public function addHidden($name, $value)
	{
		return $this->addControl(new Form\Hidden($name, $value))->form($this);
	}

	public function addCheckbox($name, $label = false, $value = true)
	{
		return $this->addControl(new Form\Checkbox($name, $label, $value))->form($this);
	}

	public function addCheckboxList($name, $label = false, $values = array())
	{
		return $this->addControl(new Form\CheckboxList($name, $label, $values))->form($this);
	}

	public function addSelect($name, $label = false, $values = array())
	{
		return $this->addControl(new Form\Select($name, $label, $values))->form($this);
	}

	public function addMultipleSelect($name, $label = false, $values = array())
	{
		return $this->addControl(new Form\MultipleSelect($name, $label, $values))->form($this);
	}

	public function addRadio($name, $label = false, $values = array())
	{
		return $this->addControl(new Form\Radio($name, $label, $values))->form($this);
	}

	public function addTextarea($name, $label = false, $values = array())
	{
		return $this->addControl(new Form\Textarea($name, $label))->form($this);
	}

	public function addUpload($name, $label = false)
	{
		$this->setAttribute("enctype","multipart/form-data");
		return $this->addControl(new Form\Upload($name, $label))->form($this);
	}

	public function addError($name, $e) {
		if ($c = $this->getControl($name)) $c->setError($e);
	}

	/**
	 * Simple HTML form rendering
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$form = $this->header();
		foreach ($this->controls as $control)	{
			$form .= $control;
		}
		$form .= $this->footer();

		return $form;
	}


	/**
	 * return array of rendered form component
	 * 
	 * @return array
	 */
	public function toArray()
	{

		return array(
			'header'  => $this->header(),
			'control' => $this->controls,
			'footer'  => $this->footer()
		);
	}


	private function header() {
		$form = "<form";
		foreach ($this->attributes as $attr => $value) {
			$form .= " {$attr}=\"{$value}\"";
		}
		$form .= ">\n";
		$form .= "\t<input type=\"hidden\" name=\"".$this->uid()."\" value=\"\" />\n";
		return $form;
	}

	private function footer() {
		return "</form>";
	}
}
