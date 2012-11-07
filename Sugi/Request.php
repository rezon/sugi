<?php namespace Sugi;
/**
 * Request
 * methods witch determines request parameters from $_SERVER superglobal
 *
 * @package Sugi
 * @version 12.11.07
 */

class Request
{
	protected static $uri 	= false;
	protected static $queue = false;
	protected static $ip 	= false;

	/**
	 * Returns protocol: "http" or "https"
	 *
	 * @return string
	 */
	public static function protocol()
	{
		return (!empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? 'https' : 'http';
	}

	/**
	 * Returns host name like "subdomain.example.com"
	 *
	 * @return string
	 */
	public static function host()
	{
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Returns request protocol+host
	 * 
	 * @return string
	 */
	public static function base()
	{
		return static::protocol() . '://' .  static::host();
	}

	/**
	 * Get the URI for the current request.
	 *
	 * @return string
	 */
	public static function uri()
	{
		// check cache
		if (static::$uri !== false) return static::$uri;

		// determine URI from Request
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
			(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : 
				(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 
					(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '')));
		
		// remove unnecessarily slashes, like doubles and leading
		$uri = preg_replace('|//+|', '/', $uri);
		$uri = ltrim($uri, '/');
		// remove get params
		if (strpos($uri, '?') !== false) {
			$e = explode('?', $uri, 2);
			$uri = $e[0];
			if (isset($e[1])) static::$queue = $e[1];
		}
		// $uri = trim($uri, '/');
		// add / only on empty URI - not good, because this will not work: 
		// 		Route::uri('(<controller>(/<action>(/<param>*)))', function ($params) {
		// since we have no "/", this is OK, but it's more complicated:
		//		Route::uri('(/)(<controller>(/<action>(/<param>*)))', function ($params) {
		//
		// if (!$uri) $uri = '/';

		// cache and return
		return static::$uri = $uri;
	}

	/**
	 * Returns request (protocol+host+uri)
	 * 
	 * @return string
	 */
	public static function current()
	{
		return static::base() . '/' . static::uri();
	}

	/**
	 * The part of the url which is after the ?
	 * 
	 * @return string
	 */
	public static function queue()
	{
		// be sure it's initialized
		static::uri();

		return static::$queue;
	}

	/**
	 * Returns request protocol+host+uri+queue
	 * 
	 * @return string
	 * @todo: maybe shold place user/pass and/or get params
	 */
	public static function full()
	{
		return static::protocol() . '://' .  static::host() . '/' . static::uri() . (static::$queue ? "?" . static::$queue : '');
	}

	/**
	 * Client IP
	 *
	 * @return string
	 */
	public static function ip()
	{
		// check cache
		if (static::$ip) return static::$ip;

		if (PHP_SAPI == 'cli') return static::$ip = 'command line';// The request was started from the command line
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return static::$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // If the server is behind proxy
		if (isset($_SERVER['HTTP_CLIENT_IP'])) return static::$ip = $_SERVER['HTTP_CLIENT_IP'];
		if (isset($_SERVER['REMOTE_ADDR'])) return static::$ip = $_SERVER['REMOTE_ADDR'];
		return static::$ip = '0.0.0.0';
	}

	/**
	 * Request from CLI
	 *
	 * @return boolean
	 */
	public static function cli()
	{
		return (PHP_SAPI == 'cli');
	}

	/**
	 * Is the request AJAX or not
	 *
	 * @return boolean
	 */
	public static function ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
	}
}
