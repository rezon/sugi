<?php namespace Sugi;
/**
 * URI
 * Functions that helps to determine some of the request information
 *
 * @package Sugi
 * @version 12.11.07
 */

class URI 
{
	protected static $uri;

	public static function current()
	{
		if (static::$uri) return static::$uri;

		// determine URI from Request
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
			(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : 
				(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 
					(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '')));
		
		// remove unnecessarily slashes, like doubles and leading
		$uri = preg_replace('|//+|', '/', $uri);
		$uri = ltrim($uri, '/');
		// remove get params
		if (strpos($uri, '?') !== false) $uri = substr($uri, 0, strpos($uri, '?'));
		// $uri = trim($uri, '/');
		// add / only on emptry URI - not good, because this will not work: 
		// 		Route::uri('(<controller>(/<action>(/<param>*)))', function ($params) {
		// since we have no "/", this is OK, but it's more complicated:
		//		Route::uri('(/)(<controller>(/<action>(/<param>*)))', function ($params) {
		//
		// if (!$uri) $uri = '/';

		// cache and return
		return static::$uri = $uri;
	}

	public static function segments($uri = null)
	{
		if (is_null($uri)) $uri = static::current();
		return explode('/', trim($uri, '/'));
	}

	public static function segment($index, $default = null)
	{
		static::current();
		$segments = static::segments(static::$uri);
		return empty($segments[$index - 1]) ? $default : $segments[$index - 1];
	}
}
