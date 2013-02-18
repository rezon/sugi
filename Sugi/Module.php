<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use \Sugi\Config;

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
			return static::factory("\Sugi\\$alias", $arg);
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
			if ($ref->isInterface()) {
				throw new \Exception("$alias is an interface");
			}			
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

			// TODO: check $params and $args match before checking for "factory" method
			
			if ($factory = $ref->getMethod("factory")) {
				if (is_null($args)) {
					return call_user_func(array($alias, "factory"));
				}
				return call_user_func_array(array($alias, "factory"), $args);
			}

			$params = $constructor->getParameters();
			$deps = static::inject($params, $args);
			
			return $ref->newInstanceArgs($deps, $args);
		} catch (\ReflectionException $e) {
			// echo $e;
		}
	}

	protected static function inject($params, $args)
	{
		$deps = array();
		foreach ($params as $param) {
			$class = $param->getClass();
			if (is_null($class)) {
				if (isset($GLOBALS[$class])) {
					$deps[] = $GLOBALS[$class];
				}
				else {
					throw new \Exception("Could not inject $param");
				}
			}

			$deps[] = static::factory($class->name, $args);
		}

		return $deps;
	}
}
