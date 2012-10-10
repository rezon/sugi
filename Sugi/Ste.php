<?php 
/**
 * Sugi Template Engine
 * 
 * @package Sugi
 * @version 20121010
 */
namespace Sugi;

/**
 * Sugi Template Engine
 */
class Ste
{
	/**
	 * Regular Expression for blocks
	 * <code>
	 * 		<!-- BEGIN blockname -->
	 *   	...
	 *    	<!-- END blockname -->
	 * </code>
	 */
	protected $blockRegEx = '/<!--\s+BEGIN\s+([0-9A-Za-z._-]+)\s+-->(.*)<!--\s+END\s+\1\s+-->/sm';

	/**
	 * Regular Expression for file inclusion
	 * <code>
	 * 		<!-- INLUDE filename.html -->
	 * </code>
	 */
	protected $includeRegEx = '|<!--\s+INCLUDE\s+([_a-zA-Z0-9\-\.\/]+)\s+-->|sm';
	// include with {@include filename.html}
	// protected $includeRegEx = '|{@include\s+([_a-zA-Z0-9\-\.\/]+)\s+}|sm';


	/**
	 * Regular Expression Pattern for variables
	 * <code>
	 * 		{varname}
	 * </code>
	 */
	protected $varRegEx = '|{([_a-zA-Z][_a-zA-Z0-9]*)}|sm';

	/**
	 * Regular Expression Pattern for array keys
	 * <code>
	 * 		{array.key.subkey}
	 * </code>
	 */
	protected $arrRegEx = '|{([_a-zA-Z][_a-zA-Z0-9]*\.[_a-zA-Z][_a-zA-Z0-9\.]*)}|sm';

	/**
	 * Template extensions that are allowed. 
	 * 
	 * @var array
	 */
	private $allowedExt = array('html', 'tpl', 'txt');

	/**
	 * Loaded template
	 * 
	 * @var string
	 */
	private $tpl;

	/**
	 * Variables set with set() method
	 * 
	 * @var array
	 */
	private $vars = array();

	private $include_path;



	/**
	 * Loads a template file
	 * 
	 * @param string filename
	 */
	public function load($template_file) {
		$template = $this->_load($template_file);

		return $this->template($template);
	}

	/**
	 * Sets raw template. If used with no parameter only returns raw template
	 * 
	 * @param string $template
	 * @return string
	 */
	public function template($template = null) {
		$this->vars = array();

		if (!is_null($template)) {
			$this->tpl = $template;
		}

		return $this->tpl;
	}

	/**
	 * Sets a parameter, or key=>value list
	 * examples:
	 * <code>
	 * 		set('title', 'My Title'); // sets the title key
	 * 		set('title'); // unsets the title key
	 * 		set(array('title' => 'My Title', 'description' => 'My Description')); // sets 2 keys: title and description
	 *   	set('home', array('link' => '/', 'title' => 'Home')); // sets a key 'home' witch is an array and can be accessed with {home.link} and {home.title}
	 * </code>
	 * 
	 * @param mixed $var
	 * @param mixed $value
	 */
	public function set($var, $value = null) {
		if (is_array($var)) {
			$this->vars = array_merge($this->vars, $var);
		}
		elseif (is_null($value)) {
			if (isset($this->vars[$var])) unset($this->vars[$var]);
		}
		else {
			$this->vars[$var] = $value;
		}
	}

	protected function _load($template_file) {
		// check file exists and is readable
		if (!File::readable($template_file)) {
			throw new SteException("Could not read template file $template_file");
		}

		// check file extension
		$ext = File::ext($template_file);
		if (!in_array($ext, $this->allowedExt)) {
			throw new SteException("File $template_file has extension that is not allowed template extension");
		}

		// try to load a file
		$template = File::get($template_file, false);
		if ($template === false) {
			throw new SteException("Could not load template file $template_file");
		}

		$this->include_path = realpath(dirname($template_file) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		// check for included files
		$template = preg_replace_callback($this->includeRegEx, array(&$this, 'replaceIncludesCallback'), $template);

		return $template;
	}

	protected function replaceIncludesCallback($matches) {
		return $this->_load($this->include_path.$matches[1]);
	}

	public function parse() {
		$subject = $this->tpl;

		$subject = preg_replace_callback($this->blockRegEx, array(&$this, 'replaceBlockCallback'), $subject);
		
		// replace variables
		$subject = preg_replace_callback($this->varRegEx, array(&$this, 'replaceVarCallback'), $subject);
		// replace arrays
		$subject = preg_replace_callback($this->arrRegEx, array(&$this, 'replaceArrCallback'), $subject);

		return $subject;
	}

	protected function replaceVarCallback($matches) {
		return isset($this->vars[$matches[1]]) ? $this->vars[$matches[1]] : false;
	}

	protected function replaceArrCallback($matches) {
		$keys = explode('.', $matches[1]);
		$vars = $this->vars;
		foreach ($keys as $k) {
			if (!isset($vars[$k])) {
				return false;
			}
			$vars = $vars[$k];
		}
		return $vars;
	}

	protected function replaceBlockCallback($matches) {
		//var_dump($matches);
		//exit;
		return false;
	}
}

class SteException extends \Exception {}
