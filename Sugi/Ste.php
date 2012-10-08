<?php 
/**
 * Sugi Template Engine
 * 
 * @package Sugi
 * @version 20121008
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




class Simplete
{
	/**
	 * Array for all found blocks
	 * @var array
	 */
	private $_blocks = array();
	
	/**
	 * @var array for all variables which are set
	 */
	private $_vars = array();
	
	/**
	 * @var array for all callback functions
	 */
	private $_funcs = array();
	
	/**
	 * @var array contains forcibly hidden blocks
	 */
	private $_hidden = array();
	
	/**
	 * @var array contains forcibly showed blocks
	 */
	private $_showed = array();
	
	/**
	 * @var string
	 */
	public $_current = '';
	
	/**
	 * Absolute path of the loaded file template, which is used for all <!-- INCLUDE --> files
	 * @var string
	 */
	private $_input_path = '';
	
	/**
	 * @var array error messages
	 */
	private $_errors = array(
		'template_not_found' 			=> "Template '%s' not found!", 
		'block_not_found' 				=> "Block '%s' not found!",
		'duplicate_block' 				=> "Block '%s' is found more then once",
		'invalid_callback' 				=> "A Callback function '%s' is invalid!",
	);
	
	
	/**
	 * Regular expressions 
	 */
	private $_blockRegEx = '/<!--\s+BEGIN\s+([0-9A-Za-z._-]+)\s+-->(.*)<!--\s+END\s+\1\s+-->/sm'; // for blocks	
	private $_blockRegExPatern = '/(<!--\s+BEGIN\s+%s\s+-->(.*)<!--\s+END\s+%s\s+-->)/sm'; // for named blocks
	private $_reverseBlockRegEx = '/<!--\s+END\s+([0-9A-Za-z._-]+)\s+-->(.*)<!--\s+BEGIN\s+\1\s+-->/sm'; // checking for block duplicates
	private $_includeRegEx = '/<!--\s+INCLUDE\s+(\S+)\s+-->/ime'; // include files
	private $_includeRegExPatern = '#<!--\s+INCLUDE\s+%s\s+-->#im'; // include files patern for replacing include block with a file content
	private $_funcRegEx  = '/{([_a-zA-Z]+[0-9A-Za-z_]*)\([^}{]*\)}/m'; // for functions
	private $_paramRegEx = '/\((.*)\)/'; // getting all paramethers of the functions
	
	/**
	 * Constructor
	 * @param string $input_path - search path of the templates
	 */
	public function __construct($input_path = '') {
		$this->_input_path = $input_path;
	}
	
	/**
	 * Sets a template
	 * @param string $template
	 * @param string $block
	 * @return void 
	 */
	public function setTemplate($template) {
		// clear old data, except _funcs
		$this->_blocks = array();
		$this->_vars = array();
		$this->_hidden = array();
		$this->_showed = array();
		$this->_current = '__main__';

		$this->_addBlock($template, '__main__', '');
		$this->_showed['__main__'] = TRUE;
	}
	
	/**
	 * Returns a parsed template
	 */
	public function getTemplate() {
		return $this->parse('__main__');
	}
	
	/**
	 * Sets a variable to a specific value.
	 * @param mixed $var - variable name (string) or array
	 * @param mixed $value - value string, or array of values 
	 */
	public function set($var, $value = '') {
		if (is_array($var)) {
			$this->_vars = array_merge($this->_vars, $var);
		}
		elseif (is_array($value)) {
			$this->_vars = array_merge($this->_vars, $this->_dot_notation($var, $value));
		} 
		else {
			$this->_vars[$var] = $value;
		}
	}
	
	/**
	 * Unset a variable
	 * @param string $var
	 */
	public function reset($var) {
		unset($this->_vars[$var]);
	}

	/**
	 * Sets a callback function. 
	 * The callback function is invoked each time the template engine
	 * finds a call to it. In template it should be like {functionname(functionparamethers)}
	 * @param string function name
	 * @param callback
	 * @return bool is the setCallbackFunction succeeded
	 */
	public function setFunction($name, $callback) {
		if (!is_callable($callback)) {
			$this->_trigger_error(sprintf($this->_errors['invalid_callback'], $name));
			return FALSE;
		}
		$this->_funcs[$name] = $callback;
		return TRUE;
    }
    
	/**
	 * Forces a block to be shown
	 * @param string $block
	 */
	public function show($block) {
		if (!isset($this->_blocks[$block])) {
			$this->_trigger_error(sprintf($this->_errors['block_not_found'], $block));
			return FALSE;
		}
		if (isset($this->_hidden[$block])) {
			unset($this->_hidden[$block]);
		}
		$this->_showed[$block] = TRUE;
	}
	
	/**
	 * Forces a block hidden
	 * @param unknown_type $block
	 */
	public function hide($block) {
		if (!isset($this->_blocks[$block])) {
			$this->_trigger_error(sprintf($this->_errors['block_not_found'], $block));
			return FALSE;
		}
		$this->_hidden[$block] = TRUE;
	}

	public function hasBlock($block) {
		return (isset($this->_blocks[$block]));
	}
	
	public function parse($block = '__main__') {
		if (!isset($this->_blocks[$block])) {
			$this->_trigger_error(sprintf($this->_errors['block_not_found'], $block));
			return FALSE;
		}
		$this->_current = $block;
		$old = $this->_blocks[$block]['parsed'];
		$subject = $this->_blocks[$block]['template'];
		$subject = preg_replace_callback($this->_varRexEx, array(&$this, '_var_prc'), $subject);
		$subject = preg_replace_callback($this->_funcRegEx, array(&$this, '_func_prc'), $subject);
		$this->_clear_child_blocks($block);
		// after parsing no matter what always set current to __main__
		$this->_current = '__main__';
		if (($subject === FALSE) && ($old === FALSE)) return FALSE;
		$subject = $old.$subject;
		$this->_blocks[$block]['parsed'] = $subject;
		return (isset($this->_showed[$block]) && !isset($this->_hidden[$block])) ? $subject : FALSE;
	}
	
/*
 * 
 * 
 * PRIVATE FUNCTIONS
 * 
 * 
 */
	
	/**
	 * Triggers Error
	 * @param string $error_msg
	 * @param E_ERROR $error_type
	 */
	private function _trigger_error($error_msg, $error_type = E_USER_ERROR) {
		// TODO: trigger some callback function if it set
		trigger_error($error_msg, $error_type);
	}
	
	private function _clear_child_blocks($block) {
		foreach ($this->_blocks[$block]['children'] as $child) {
			$this->_blocks[$child]['parsed'] = '';
			unset($this->_showed[$child]);
			//$this->_clear_child_blocks($child);
		}
	}
	
	private function _var_prc($matches) {
		$var = $matches[1];
		if (isset($this->_vars[$var])) {
			// Show current block since there is some set variables
			$this->_showed[$this->_current] = TRUE;
			return $this->_vars[$var];
		}
		if ((substr($var, 0, 2) === '__') && (substr($var, -2) === '__')) {
			$block = substr($var, 2, -2);
			if ($this->_blocks[$block]['parsed'] !== FALSE) {
				return $this->_blocks[$block]['parsed'];
			}
			return $this->parse($block);
		}
		return FALSE;
	}
	
	private function _func_prc($matches) {
		preg_match($this->_paramRegEx, $matches[0], $arg_match);
		$args = $this->_split_args($arg_match[1]);
		if (isset($this->_funcs[$matches[1]])) {
			//$this->_showed[$this->_current] = TRUE;
			if ($ret = call_user_func_array($this->_funcs[$matches[1]], $args)) {
				$this->_showed[$this->_current] = TRUE;
			}
			return $ret;
		}
		return FALSE;
	}
	
	private function _addBlock($template, $block, $parent) {
		// Include files
		if (preg_match_all($this->_includeRegEx, $template, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if ($t = $this->_load_file($match[1])) {
					//preg_replace
					$template = preg_replace(sprintf($this->_includeRegExPatern, $match[1]), $t, $template);
				}
			}
			// if there is other INCLUDE tags inside of included file
			return $this->_addBlock($template, $block, $parent);
		}
		// Check some of the blocks are not unique
		// This will not find all duplicates, so aditional work will be done below
		if (preg_match_all($this->_reverseBlockRegEx, $template, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$this->_trigger_error(sprintf($this->_errors['duplicate_block'], $match[1]));
				return FALSE;
			}
		}
		$children = array();
		// Find all blocks
		preg_match_all($this->_blockRegEx, $template, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// Check again for duplicates
			if (!empty($this->_blocks[$block])) {
				$this->_trigger_error(sprintf($this->_errors['duplicate_block'], $match[1]));
				return FALSE;
			}
			// recursive call for sub blocks
			$template = preg_replace(sprintf($this->_blockRegExPatern, $match[1], $match[1]), '{'.'__' . $match[1] . '__'.'}', $template);
			$this->_addBlock($match[2], $match[1], $block);
			$children[] = $match[1];
		}
		$this->_blocks[$block] = array(
			'template' => $template,
			'parsed' => FALSE,
			'parent' => $parent,
			'children' => $children  // currently not in use
		);
		return $template;
	}
	
	private function _dot_notation($name, $array){
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, $this->_dot_notation("$name.$key", $value));
			}
			else {
				$result["$name.$key"] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * Split a string at a comma, but only if it is not escaped with backslash
	 * @param string
	 * @return array
	 */
	private function _split_args($string) {
		$regex = "/\,((?:[^\\\,]|\\\\.)*)/";
		preg_match_all($regex, ",$string", $matches, PREG_PATTERN_ORDER);
		$matches = $matches[1];
		foreach ($matches as $idx => $match) {
	  		$matches[$idx] = preg_replace("/\\\\(.)/s", "$1", $match);
		}
		return $matches;
	}
}
