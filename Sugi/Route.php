<?php namespace Sugi;
/**
 * Route
 *
 * @package Sugi
 * @version 12.11.07
 */

/**
 * /Sugi/Route
 */
class Route
{
	// <segment> RegExt
	const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

	// What <segment> accepts
	const REGEX_SEGMENT = '[^/.,;?\n<>]++';

	// Those should be escaped
	const REGEX_ESCAPE  = '[.\\+?[^\\]${}=!]';

	public static $registry = array();

	public static $uri = false;

	/**
	 * Regular Expression based routing
	 * Executing callback function if current URI matches given pattern
	 * 
	 * @example
	 * <code>
	 * Route::match('#^(?<lang>en|bg)?/?(?<controller>[^/]*)/?(?<method>[^/]*)/?(?<arguments>.*)$#uD', function ($params)
	 * {
	 * 		define('LANG', !empty($params['lang']) ? $params['lang'] : 'bg');
	 *   	$controller = !empty($params['controller']) ? $params['controller'] : 'home';
	 *    	$method = !empty($params['method']) ? $params['method'] : 'index';
	 *     	$arguments = !empty($params['arguments']) ? $params['agruments'] : array();
	 *      App::execute("Controller_$controller", "action_$method", $arguments) and exit;
	 * });
	 * </code>
	 * 
	 * @param string $pattern regex pattern
	 * @param Closure $callback
	 * @return boolean
	 */
	public static function match($pattern, $callback)
	{
		if (static::$uri === false) static::$uri = URI::current();
		$segments = array();
		$match = false;
		if (preg_match_all('#<('.static::REGEX_SEGMENT.')?#uD', $pattern, $segs)) {
			$segments = array_fill_keys($segs[1], '');
		}
		if (preg_match($pattern, static::$uri, $matches)) {
			foreach ($matches as $key => $value) {
				// We need only named params
				if (!is_int($key)) {
					$segments[$key] = $value;
				}
			}
			$match = true;
		}
		$registry[] = compact('pattern', 'callback', 'segments', 'match');

		if ($match) call_user_func_array($callback, array($segments));
		return $match;
	}

	/**
	 * Simplified based routing
	 * Executing callback function if current URI matches given pattern
	 * 
	 * @example
	 * <code>
	 * Route::uri('(en(/))(<controller>(/<action>(/<param>*)))', function ($params) {
	 * {
	 * 		extract($params);
	 * 		define('LANG', !empty($lang) ? $lang : 'bg');
	 * 		App::execute("Controller_" . ($controller ? $controller : 'home'), "action_" . ($action ? $action : 'index'), array($param)) and exit;
	 * });
	 * </code>
	 * 
	 * @param string $pattern - simplified pattern
	 * @param function $callback
	 * @return boolean
	 */
	public static function uri($pattern, $callback)
	{
		$pattern = static::compile($pattern);
		return static::match($pattern, $callback);
	}
	
	/**
	 * Compile regular expression for the route
	 *
	 * @param string $uri
	 * @return string - regex pattern
	 */
	private static function compile($uri)
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for: ( ) < >
		$regex = preg_replace('#'.static::REGEX_ESCAPE.'#', '\\\\$0', $uri);

		if (strpos($regex, '(') !== FALSE) {
			// Make optional parts of the URI non-capturing and optional
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
		}

		// Insert default regex for keys
		$regex = str_replace(array('<', '>'), array('(?P<', '>'.static::REGEX_SEGMENT.')'), $regex);
		// Wildcard `*`
		$regex = str_replace('>'.static::REGEX_SEGMENT.')*', '>*)', $regex);
		$regex = str_replace('*', '.*', $regex);

		return '#^'.$regex.'$#uD';
	}
}
