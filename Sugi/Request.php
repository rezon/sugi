<?php
/**
 * Request
 * function to determine request parameters mainly from $_SERVER array
 *
 * @package Sugi
 * @version 20121004
 */
namespace Sugi;

class Request
{

	/**
	 * Returns protocol: "http" or "https"
	 *
	 * @return str
	 */
	public static function protocol() {
		return (!empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? 'https' : 'http';
	}

	/**
	 * Returns host name like "subdomain.example.com"
	 *
	 * @return str
	 */
	public static function host() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * Returns request protocol+host
	 * 
	 * @return string
	 */
	public static function base() {
		return static::protocol() . '://' .  static::host();
	}

	/**
	 * Get the URI for the current request.
	 *
	 * @return string
	 */
	public static function uri() {
		return URI::current();
	}

	/**
	 * Returns request protocol+host+uri
	 * 
	 * @return string
	 * @todo: maybe shold place user/pass and/or get params
	 */
	public static function full() {
		return static::protocol() . '://' .  static::host() . '/' . static::uri();
	}

	/**
	 * Client IP
	 *
	 * @return str
	 */
	public static function ip() {
		if (PHP_SAPI == 'cli') return 'command line';// The request was started from the command line
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR']; // If the server is behind proxy
		if (isset($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
		if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
		return '0.0.0.0';
	}

	/**
	 * Request from CLI
	 *
	 * @return bool
	 */
	public static function cli() {
		return (PHP_SAPI == 'cli');
	}

	/**
	 * Is the request AJAX or not
	 *
	 * @return bool
	 */
	public static function ajax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
	}
}
