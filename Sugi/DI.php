<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Dependency Injection Class
 */
class DI
{
	/**
	 * Check we can create an instance of a given class
	 *
	 * @param string $class - class name
	 * @param array $args - arguments
	 */
	public static function reflect($class, array $args = null)
	{
		// Reflection
		$reflection = new \ReflectionClass($class);

		// Check we can create an instance of the class
		if ($reflection->isInterface()) {
			throw new \Exception("$class is an interface");
		}			
		if ($reflection->isAbstract()) {
			throw new \Exception("Class $class is abstract.");
		}
		if (!$reflection->isInstantiable()) {
			// TODO if the __construct() is protected we are checking for factory method
			// if ($instance = static::callFactory($reflection, $args)) {
			// 	return $instance;
			// }
			throw new \Exception("Class $class is not instantiable.");
		}

		// Checking for constructor
		if (!$constructor = $reflection->getConstructor()) {
			// create it
			$instance = new $class();

			return $instance;
		}

		// TODO check $params and $args match before checking for "factory" method
			
		// checking for factory method
		$instance = static::callFactory($reflection, $args);
		if (!is_null($instance)) {
			return $instance;
		}

		$params = $constructor->getParameters();
		$deps = static::inject($params, $args);
			
		return $reflection->newInstanceArgs($deps);
	}

	protected static function callFactory(\ReflectionClass $reflection, $args)
	{
		if (!$reflection->hasMethod("factory")) {
			return null;
		}

		$factoryMethod = $reflection->getMethod("factory");
		
		// TODO check required arguments. If they do not match with args return false
		
		if (is_null($args)) {
			return $factoryMethod->invoke(null);
		}
		
		return $factoryMethod->invokeArgs(null, $args);
	}	

	protected static function inject($params, $args)
	{
		// Geting dependencies
		$deps = array();
		foreach ($params as $key => $param) {
			$class = $param->getClass();
			if (is_null($class)) {
				$deps[] = isset($args[$key]) ? $args[$key] : null;
			}
			else {
				$deps[] = Module::get($class->name, $args);
			}
		}

		return $deps;
	}
}
