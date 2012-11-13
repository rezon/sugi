<?php namespace Sugi;
/**
 * Route
 *
 * @package Sugi
 * @version 12.11.13
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
	 * @example
	 * <code>
	 * Route::add('(<lang>(/))(<controller>(/)(<method>(/)(<param>)))', function ($segments) {
	 * {
	 * 		define('LANG', ($lang = Filter::key('lang', $segments, 'en')) ? $lang : 'en');
	 * 		$controller = Filter::key('controller', $segments, 'home');
	 * 		$method = Filter::key('method', $segments, 'index');
	 * 		$arguments = Filter::key('arguments', $segments, array());
	 * 		
	 * 		App::execute("Controller_$controller", "action_$method", $arguments) and exit;
	 * })->segment('lang', 'en|bg')->segment('param', '.*');
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


	/****************
	 *     Route    *
	 ****************/
	protected $pattern;
	protected $callback;
	protected $segments;
	protected $regex;

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
		$regex = preg_replace_callback(
			'#'.static::REGEX_KEY.'#uD', 
			function($match) {
				return "(?P<{$match[1]}>" . Filter::key($match[1], $this->segments, static::REGEX_SEGMENT) . ')';
			},
			$regex
		);

		$this->regex = '#^'.$regex.'$#siuD';
	}
}
