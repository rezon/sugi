<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.12.05
 */

use \Sugi\Config;

/**
 * Module - registry of class, methods, objects with ability to instantiate them
 */
class Module
{

	public static $registry = array();

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
			static::$registry[$alias]['alias'] = $param;
		} elseif (is_callable($param)) {
			// set closure
			static::$registry[$alias]['callback'] = $param;
		} else {
			// set an object
			static::$registry[$alias]['instance'] = $param;
		}
	}

	/**
	 * Returns a singleton instance of the required module.
	 * 
	 * @param string $alias
	 * @return mixed
	 */
	public static function get($alias)
	{
		// If we have already loaded this module we return it right now
		if (isset(static::$registry[$alias]['instance'])) {
			return static::$registry[$alias]['instance'];
		}
		// instantiate module, add it for next reference and return it
		$instance = static::factory($alias);
		static::$registry[$alias]['instance'] = $instance;
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
			return call_user_func_array(static::$registry[$alias]['callback'], array($args)); 
		}
		
		// Check for configuration arguments
		if (!is_null($args)) {
			$conf = $args;
		} else {
			// Check for configuration file
			$conf = static::findConfig($alias);
		}
		
		// Auto-create it
		if ($instance = static::reflect($alias, $conf)) {
			return $instance;
		}

		if (isset(static::$registry[$alias]['alias'])) {
			return static::factory(static::$registry[$alias]['alias'], $args);
		}

		// Check for \Sugi\$alias
		if (strpos($alias, '\\') === false) {
			return static::factory("\Sugi\\$alias", $args);
		}
		throw new \Exception("Could not find $alias class");
	}

	
	protected static function findConfig($alias)
	{
		$file = explode("\\", $alias);
		$file = strtolower(array_pop($file));
		$conf = Config::$file('', null);
		if (!is_null($conf)) {
			return $conf;
		}
		if (isset(static::$registry[$alias]['alias'])) {
			return static::findConfig(static::$registry[$alias]['alias']);
		}
	}

	/**
	 * Check we can create an instance of a given class
	 *
	 * @param string $alias - class name
	 * @param array $args - arguments to be passed to the constructor
	 */
	protected static function reflect($alias, $args)
	{
		try {
			$ref = new \ReflectionClass($alias);

			// Check we can create an instance of the class
			if ($ref->isAbstract()) {
				throw new \Exception("Class $alias is abstract.");
			}

			if (!$ref->isInstantiable()) {
				throw new \Exception("Class $alias is not instantiable.");
			}

			// Try to create it
			$constructor = $ref->getConstructor();

			if (is_null($constructor)) {
				return new $alias;
			}

			return $ref->newInstanceArgs(array($args));
		}
		catch (\ReflectionException $e) {
			//
		}
	}
}
