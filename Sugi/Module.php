<?php namespace Sugi;
/**
 * Module - registry of class, methods, objects with ability to instantiate them
 * 
 * @package Sugi
 * @version 12.11.04
 */

/**
 * \Sugi\Module
 */
class Module
{
	public static $registry = array();

	/**
	 * Register an object with a given name
	 * 
	 * @param string $key
	 * @param mixed $obj
	 */
	public static function register($key, $obj)
	{
		static::$registry[$key] = $obj;
	}

	/**
	 * An alias to register() method
	 */
	public static function set($key, $obj)
	{
		static::register($key, $obj);
	}

	/**
	 * Check an object has been registered
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public static function isRegistered($key)
	{
		return array_key_exists($key, static::$registry);
	}

	/**
	 * Returns an item which was previously registered with register() .
	 * If the item is callable function (closure) it will be fired and the result will be returned.
	 * If there is no such item assume it is a class name. A class will be created as a singleton.
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key)
	{
		$args = array_slice(func_get_args(), 1);

		// check we have it registered
		if (static::isRegistered($key)) {
			$obj = static::$registry[$key];

			// if it is callable function (closure)
			if (is_callable($obj)) {
				// fire it
				return call_user_func_array($obj, $args);
			}

			// return registered object
			if (!is_string($obj)) {
				return $obj;
			}

			// we suppose $key is an alias of $obj
			$key = $obj;
		}

		$obj = static::reflect($key, $args);

		// reigster it for further use
		static::register($key, $obj);

		return $obj;
	}

	/**
	 * Unregister previously registered item
	 * 
	 * @param string $key
	 */
	public static function unregister($key)
	{
		unset(static::$registry[$key]);
	}

	/**
	 * An alias to unregister() metdhod
	 */
	public static function remove($key)
	{
		static::unregister($key);
	}
	
	/**
	 * Check we can create an instance of a given class
	 * 
	 * @param string $key - class name
	 * @param array $args - arguments to be passed to the constructor
	 */
	protected static function reflect($key, $args) {
		$ref = new \ReflectionClass($key);

		// Check we can create an instance of the class
		if ($ref->isAbstract()) {
			throw new \Exception("Class $key is abstract.");
		}

		if (!$ref->isInstantiable()) {
			throw new \Exception("Class $key is not instantiable.");
		}

		// Try to create it
		$constructor = $ref->getConstructor();
		if (is_null($constructor)) {
			return new $key;
		}

		return $ref->newInstanceArgs($args);
	}
}
