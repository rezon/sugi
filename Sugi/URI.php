<?php namespace Sugi;
/**
 * URI.
 * Working with URL paths.
 *
 * @package Sugi
 * @version 12.11.07
 */

include_once __DIR__.'/Request.php';

class URI 
{
	public static function current()
	{
		return Request::uri();
	}

	public static function segments($uri = null)
	{
		if (is_null($uri)) $uri = static::current();
		return explode('/', trim($uri, '/'));
	}

	public static function segment($index, $default = null)
	{
		static::current();
		$segments = static::segments(static::current());
		return empty($segments[$index - 1]) ? $default : $segments[$index - 1];
	}
}
