<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.12.05
 */

use \Sugi\Config;
use \Sugi\Filter;


/**
 * Module - registry of class, methods, objects with ability to instantiate them
 */
class Module
{
	/**
	 * Module aliases
	 * @var array
	 */
	public static $aliases = array();
	
	/**
	 * Module closures
	 * @var array
	 */
	public static $closures = array();

	/**
	 * Loaded modules
	 * @var array
	 */
	protected static $modules = array();
	

	public static function set($alias, $param)
	{
		if (is_string($param)) { 
			// set alias
			static::$aliases[$alias] = $param;
		} 
		elseif (is_callable($param)) {
			// set closure
			static::$closures[$alias] = $param;
		}
		else {
			// sets an object
			static::$modules[$alias] = $param;
		}
	}


	public static function get($alias)
	{
		// If we have already loaded this module we return it right now
		if (isset(static::$modules[$alias])) {
			return static::$modules[$alias];
		}
		// return new module and to the list for next reference
		static::$modules[$alias] = static::factory($alias);
		return static::$modules[$alias];
	}
	
	public static function factory($alias, $params = null)
	{
		// Loader
		if (isset(static::$closures[$alias])) {
			return call_user_func_array(static::$closures[$alias], array($params)); 
		}
		
		$name = (isset(static::$aliases[$alias])) ? static::$aliases[$alias] : $alias;
		if (isset(static::$closures[$name])) {
			return call_user_func_array(static::$closures[$name] , array($params)); 
		}

		// autoloading
		if (!is_null($params)) {
			$conf = $params;
		}
		else {
			// trying with alias as a filename
			$file = explode("\\", $alias);
			$file = strtolower(array_pop($file));
			if (is_null($conf = Config::$file())) {
				// trying with class name as a filename
				$file = explode("\\", $name);
				$file = strtolower(array_pop($file));
				$conf = Config::$file();
			}
		}
		if (is_null($conf)) {
			return new $name();
		}
		return new $name($conf);
	}
}
