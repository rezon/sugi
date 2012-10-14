<?php namespace Sugi;
/**
 * Route
 * Simple Routing - currently not much functionality
 *
 * @package Sugi
 * @version 20121014
 */

class Route
{
	// <segment> RegExt
	const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

	// What <segment> accepts
	const REGEX_SEGMENT = '[^/.,;?\n<>]++';

	// Those should be escaped
	const REGEX_ESCAPE  = '[.\\+?[^\\]${}=!|]';

	/**
	 * Executing callback function if current uri matches given one
	 * 
	 * @param  string $uri
	 * @param  function $callback
	 */
	public static function uri($uri, $callback)
	{
		$pattern = (is_array($uri)) ? static::compile($uri[0], isset($uri[1]) ? $uri[1] : null) : static::compile($uri);
		//echo '<pre>'.htmlspecialchars($pattern).'</pre>';
		if (preg_match($pattern, URI::current(), $matches)) {
			$params = array();
			if (preg_match_all('#<('.static::REGEX_SEGMENT.')?#uD', $uri, $segments)) {
				//echo '<pre>'.htmlspecialchars(var_export($segments, true)).'</pre>';

				$params = array_fill_keys($segments[1], '');
				//echo '<pre>'.htmlspecialchars(var_export($params, true)).'</pre>';
			}

			foreach ($matches as $key => $value) {
				// We need only named params
				if (!is_int($key)) {
					$params[$key] = $value;
				}
			}

			/*if (empty($params)) call_user_func($callback, null);
			else*/ call_user_func_array($callback, array($params));
		}
	}
	
	/**
	 * Compile regular expression for the route
	 *
	 * @param string $uri
	 * @param array $segment_pattern
	 * @return string - regex pattern
	 */
	private static function compile($uri, $segment_pattern = array())
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
