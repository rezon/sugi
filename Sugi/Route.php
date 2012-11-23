<?php namespace Sugi;
/**
 * Route
 *
 * @package Sugi
 * @version 12.11.23
 */

use \Sugi\Filter;

include_once __DIR__ . '/Filter.php';

/**
 * \Sugi\Route
 */
class Route
{
	// <segment> RegEx
	const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

	// What <segment> accepts by default
	const REGEX_SEGMENT = '[^/.,;?<>]++';

	// Those should be escaped
	const REGEX_ESCAPE  = '[.\\+?[^\\]${}=!]';

	protected static $routes = array();

	public static $uri = false;

	/**
	 * Creating Rule for routing
	 * Executing callback function if current URI matches given pattern
	 * 
	 * <code>
	 * Route::add('(<lang>/)(<controller>(/<method>(/<param>)))', function ($segments) {
	 * {
	 * 		define('LANG', ($lang = Filter::key('lang', $segments, 'en')) ? $lang : 'en');
	 * 		$controller = Filter::key('controller', $segments, 'home');
	 * 		$method = Filter::key('method', $segments, 'index');
	 * 		$arguments = Filter::key('arguments', $segments, array());
	 * 		
	 * 		App::execute("Controller_$controller", "action_$method", $arguments) and exit;
	 * })->name('mvc')->segment('lang', 'en|bg')->segment('param', '.*');
	 * </code>
	 * 
	 * @param string $pattern - simplified pattern
	 * @param function $callback
	 * @return \Sugi\Route\Router
	 */
	public static function add($pattern, $callback)
	{
		// echo '<pre>' . htmlspecialchars($pattern) . '</pre>';
		$route = new Route($pattern, $callback);
		static::$routes[] = $route;
		return $route;
	}

	/**
	 * Processing Request
	 * 
	 * @todo: add this to App::run();
	 * @return boolean
	 */
	public static function process_request()
	{
		$uri = (static::$uri !== false) ? static::$uri : URI::current();
		$match = false;

		foreach (static::$routes as $route) {
			if ($route->match($uri)) $match = true;
		}

		return $match;
	}

	/**
	 * Finds a named route
	 * 
	 * @param string $name
	 * @return \Sugi\Route
	 */
	public static function find($name)
	{
		foreach (static::$routes as $route) {
			if ($route->name == $name) return $route;
		}

		return false;
	}

	/**
	 * Makes a URL based on the pattern provided by the named route
	 *
	 * @param string $name
	 * @param array $segments
	 * @return string
	 */
	public static function make($name, $segments = array())
	{
		if (!$route = static::find($name)) return '/';

		$uri = static::replace_segments($route->pattern, $segments);

		// add leading '/'
		$uri = '/' . ltrim($uri, '/');

		return $uri;
	}

	/**
	 * Replaces segments in the route pattern with the given values
	 * 
	 * @param string $pattern
	 * @param array $segments
	 * @return string
	 */
	protected static function replace_segments($pattern, $segments)
	{
		if (preg_match('#\(([^()]+)\)#uD', $pattern, $match)) {
			//echo "<pre>" . htmlspecialchars(var_export($match, true)) . "</pre>";
			$found = false;
			$replace = $match[1];
			if (preg_match_all('#'.static::REGEX_KEY.'#uD', $replace, $segmatch)) {
				//echo "<pre>" . htmlspecialchars(var_export($segmatch, true)) . "</pre>";
				foreach ($segmatch[1] as $key => $seg) {
					if ($value = Filter::key($seg, $segments)) {
						$replace = str_replace("<$seg>", $value, $replace);
						$found = true;
					}
					else {
						$replace = str_replace("<$seg>", $seg, $replace);
					}
				}
			}
			if (!$found) $replace = '';
			$pattern = str_replace($match[0], $replace, $pattern);
			return static::replace_segments($pattern, $segments);
		}

		return $pattern;
	}

	/****************
	 *     Route    *
	 ****************/
	protected $pattern;
	protected $callback;
	protected $regex;
	protected $name;
	public $segments;

	/**
	 * Sets custom pattern for the segment
	 * 
	 * @param string $segment
	 * @param string $pattern
	 */
	public function segment($segment, $pattern)
	{
		$this->segments[$segment] = $pattern;

		return $this;
	}

	public function name($value)
	{
		$this->name = $value;

		return $this;
	}

	/**
	 * Constructor which is invoked by \Sugi\Route class
	 * 
	 * @param string $pattern
	 * @param Closure $callback
	 */
	protected function __construct($pattern, $callback)
	{
		$this->pattern  = $pattern;
		$this->callback = $callback;
	}

	/**
	 * Check the given URI matches the router's pattern and execute callback function.
	 * 
	 * @param string $uri
	 * @return boolean
	 */
	protected function match($uri)
	{
		$this->compile();
		if (preg_match($this->regex, $uri, $matches)) {
			$segments = array();
			foreach ($matches as $key => $value) {
				// We need only named params
				if (!is_int($key)) {
					$segments[$key] = $value;
				}
			}

			call_user_func_array($this->callback, array($segments));
			return true;
		}

		return false;
	}
	
	/**
	 * Compile regular expression for the router
	 */
	protected function compile()
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for: ( ) < >
		$regex = preg_replace('#'.static::REGEX_ESCAPE.'#', '\\\\$0', $this->pattern);

		// Make optional parts of the URI non-capturing and optional
		$regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);

		// Transform segments and segment patterns
		// $this is not available in Closures before PHP 5.4, so we'll use function instead of Closure
		$regex = preg_replace_callback('#'.static::REGEX_KEY.'#uD', array($this, 'regex_key_prc'), $regex); 

		$this->regex = '#^'.$regex.'$#siuD';
	}

	protected function regex_key_prc($match)
	{
		return "(?P<{$match[1]}>" . Filter::key($match[1], $this->segments, static::REGEX_SEGMENT) . ')';
	}
}
