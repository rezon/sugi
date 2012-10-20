<?php namespace Sugi; 
/**
 * Sugi Template Engine
 * <code>
 * 		$tpl = new Ste();
 * 		$tpl->load(path/to/template);
 * 		
 * 		$tpl->set('varname', 'value');
 * 		$tpl->set('varname', array('subvar1'=> 'value', 'subvar2' => 'onothervalue'));
 * 		$tpl->set(array('varname1' => 'value1', 'varname2' => 'value2'));
 * 		$tpl->loop('blockname', array( array('var1' => 'val1', 'var2' => 'val2'), array('var1' => 'otherval') ));
 * 		$tpl->hide('unwantendblock');
 * 		$tpl->unhide('someblockthatwashidden');
 *
 * 		echo $tpl->parse();
 * </code>
 * 
 * @package Sugi
 * @version 20121019
 */

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
	protected $blockRegEx = '/<!--\s+BEGIN\s+([0-9A-Za-z._-]+)\s+-->(.*)<!--\s+END\s+\1\s+-->/s';

	/**
	 * Regular Expression for file inclusion
	 * <code>
	 * 		<!-- INLUDE filename.html -->
	 * </code>
	 */
	protected $includeRegEx = '#<!--\s+INCLUDE\s+([_a-zA-Z0-9\-\.\/]+)\s+-->#sm';
	// protected $includeRegEx = '#{@include\s+([_a-zA-Z0-9\-\.\/]+)\s+}#sm'; // include with {@include filename.html}


	/**
	 * Regular Expression Pattern for variables
	 * <code>
	 * 		{varname}
	 * </code>
	 */
	protected $varRegEx = '#{([_a-zA-Z][_a-zA-Z0-9]*)}#sm';

	/**
	 * Regular Expression Pattern for array keys
	 * <code>
	 * 		{array.key.subkey}
	 * </code>
	 */
	protected $arrRegEx = '#{([_a-zA-Z][_a-zA-Z0-9]*\.[_a-zA-Z][_a-zA-Z0-9\.]*)}#sm';

	/**
	 * Template extensions that are allowed. 
	 * 
	 * @var array
	 */
	protected $allowedExt = array('html', 'tpl', 'txt');

	/**
	 * Loaded template
	 * 
	 * @var string
	 */
	protected $tpl;

	/**
	 * Variables set with set() method
	 * 
	 * @var array
	 */
	protected $vars = array();

	/**
	 * Variables for loops
	 * 
	 * @var array
	 */
	protected $loops = array();

	/**
	 * Which blocks are set not to be rendered
	 * 
	 * @var array
	 */
	protected $hide = array();

	/**
	 * Current include path based on last proceeded file
	 * 
	 * @var string
	 */
	protected $include_path;


	/**
	 * Loads a template file
	 * 
	 * @param string filename
	 * @return string
	 */
	public function load($template_file)
	{
		$template = $this->_load($template_file);

		return $this->template($template);
	}

	/**
	 * Sets raw template. If used with no parameter only returns raw template
	 * 
	 * @param string $template
	 * @return string
	 */
	public function template($template = null)
	{
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
	public function set($var, $value = null)
	{
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

	/**
	 * Loops a block (copies) several times replacing all nested variables with $values
	 * 
	 * @param  string $blockname name of the block
	 * @param  array  $values  array of array of values
	 */
	public function loop($blockname, $values = array())
	{
		$this->loops[$blockname] = $values;
		//$this->vars[$blockname] = $values;
	}

	/**
	 * Hides (removes) a block
	 * 
	 * @param  string $blockname
	 */
	public function hide($blockname)
	{
		$this->hide[$blockname] = true;
	}

	/**
	 * Unhides a block
	 * @param string $blockname
	 */
	public function unhide($blockname)
	{
		unset($this->hide[$blockname]);	
	}

	/**
	 * Parses and returns prepared template
	 * 
	 * @return string
	 */
	public function parse()
	{
		return $this->_parse($this->tpl);
	}




	protected function _load($template_file)
	{
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

		return $template;
	}

	protected function _parse($subject)
	{
		// blocks
		$subject = preg_replace_callback($this->blockRegEx, array(&$this, '_replaceBlockCallback'), $subject);
		// replace variables
		$subject = preg_replace_callback($this->varRegEx, array(&$this, '_replaceVarCallback'), $subject);
		// replace arrays
		$subject = preg_replace_callback($this->arrRegEx, array(&$this, '_replaceArrCallback'), $subject);
		// check for dynamically included files
		$subject = preg_replace_callback($this->includeRegEx, array(&$this, '_replaceIncludesCallback'), $subject);

		return $subject;
	}

	protected function _replaceIncludesCallback($matches)
	{
		return $this->_parse($this->_load($this->include_path.$matches[1]));
	}

	protected function _replaceVarCallback($matches)
	{
		return isset($this->vars[$matches[1]]) ? $this->vars[$matches[1]] : false;
	}

	protected function _replaceArrCallback($matches)
	{
		$keys = explode('.', $matches[1]);
		$vars = $this->vars;
		foreach ($keys as $key => $val) {
			if (!isset($vars[$val])) {
				return false;
			}
			$vars = $vars[$val];
		}
		return $vars;
	}

	protected function _replaceBlockCallback($matches)
	{
		static $inloop = false;

		// check the block is hidden
		if (!empty($this->hide[$matches[1]])) {
			return false;
		}

		// if we have no registered loop
		if (!isset($this->loops[$matches[1]])) {
			// return false;
			if ($inloop) {
				$inloop = false;
				return false;
			}
			// parse inside
			return $this->_parse($matches[2]);
		}

		// loop
		$return = '';
		foreach ($this->loops[$matches[1]] as $key => $match) {
			$inloop = true;
			foreach ($match as $k=>$m) {
				if (is_array($m)) {
					$this->loops[$k] = $m;
				}
			}
			$this->vars[$matches[1]] = $match;
			$return .= $this->_parse($matches[2]);

			// cleanup
			unset($this->loops[$k]);
			unset($this->vars[$matches[1]]);
			$inloop = false;
		}
		return $return;
	}
}

class SteException extends \Exception {}
