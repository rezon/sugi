<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi;

/**
 * Module - registry of class, methods, objects with ability to instantiate them
 */
class Module
{

	public static $registry = array();

	/**
	 * Register a loader.
	 * Settings are done based on the type of the $param
	 * alias for strings, callback function for callable types and objects for anything else
	 * 
	 * @param string $alias
	 * @param mixed $param
	 */
	public static function set($alias, $param)
	{
		// 
		if (is_string($param)) { 
			// set an alias
			static::$registry[$alias]["alias"] = $param;
		} elseif (is_callable($param)) {
			// set closure
			static::$registry[$alias]["callback"] = $param;
		} elseif (is_array($param)) {
			static::$registry[$alias]["params"] = $param;
		} else {
			// set an object
			static::$registry[$alias]["instance"] = $param;
		}
	}

	/**
	 * Returns a singleton instance of the required module.
	 * 
	 * @param string $alias
	 * @return mixed
	 */
	public static function get($alias, $arg = null)
	{
		if (!is_null($arg)) {
			return static::factory($alias, $arg);
		}

		// If we have already loaded this module we return it right now
		if (isset(static::$registry[$alias]["instance"])) {
			return static::$registry[$alias]["instance"];
		}
		// instantiate module, add it for next reference and return it
		$params = isset(static::$registry[$alias]["params"]) ? static::$registry[$alias]["params"] : null;
		$instance = static::factory($alias, $params);
		static::$registry[$alias]["instance"] = $instance;
		return $instance;
	}
	
	/**
	 * Creates a new instance of an $alias class.
	 *
	 * @param string $alias
	 * @return mixed
	 * @throws \Exception If the given class is not found
	 */
	public static function factory($alias, $arg = null)
	{
		$args = is_null($arg) ? null : array_slice(func_get_args(), 1);

		// Load it
		if (isset(static::$registry[$alias]['callback'])) {
			return call_user_func_array(static::$registry[$alias]['callback'], (array) $args); 
		}
		
		// Check for configuration arguments and configuration file
		if (is_null($args)) {
			$args = static::findConfig($alias);
		}
		
		// Auto-create it
		try {
			return DI::reflect($alias, $args);
		} catch (\ReflectionException $e) {
			// echo $e;
		}

		if (isset(static::$registry[$alias]['alias'])) {
			return static::factory(static::$registry[$alias]['alias'], $args);
		}

		// Check for \Sugi\$alias
		if (strpos($alias, '\\') === false) {
			return DI::reflect("\Sugi\\$alias", $args);
		}
		throw new \Exception("Could not find $alias class");
	}

	
	protected static function findConfig($alias)
	{
		$file = explode("\\", $alias);
		$file = strtolower(array_pop($file));
		$conf = Config::get($file);
		if (!is_null($conf)) {
			return array($conf);
		}
		if (isset(static::$registry[$alias]['alias'])) {
			return static::findConfig(static::$registry[$alias]['alias']);
		}
	}
}
