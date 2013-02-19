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
	 * @param array $args - passed arguments
	 */
	public static function reflect($class, array $args = null)
	{
		// Reflection
		$reflection = new \ReflectionClass($class);

		// Check we can create an instance of the class
		if ($reflection->isInterface()) {
			throw new \Exception("Could not reflect interface $class");
		}			
		if ($reflection->isAbstract()) {
			throw new \Exception("Could not reflect abstract class $class");
		}

		// getting constructor
		$constructor = $reflection->getConstructor();

		// Checking for constructor
		if (!$constructor) {
			return new $class();
		}

		// if the __construct() is private or protected we'll check for factory method
		if ($constructor and !$constructor->isPublic()) {
			$instance = static::callFactory($reflection, $args);
			if (!is_null($instance)) {
				return $instance;
			}
		}

		// do we need this?
		if (!$reflection->isInstantiable()) {
			throw new \Exception("Could not instantiate class $class");
		}

		// TODO: we have to choose which of the FACTORY or CONSTRUCT methods to be first!
		// Currently not to break \Sugi\Database I will use factory()
		
		// factory()
		$instance = static::callFactory($reflection, $args);
		if (!is_null($instance)) {
			return $instance;
		}

		// __construct()
		// Checking for constructor parameters
		$params = $constructor->getParameters();
		// Preparing dependencies
		$deps = static::injectParameters($params, $args);

		// Injecting dependencies in the constructor
		$instance = $reflection->newInstanceArgs($deps);

		return $instance;
	}

	protected static function callFactory(\ReflectionClass $reflection, $args)
	{
		if (!$reflection->hasMethod("factory")) {
			return null;
		}

		$factoryMethod = $reflection->getMethod("factory");
		
		// TODO check required arguments. If they do not match with args return false
		$params = $factoryMethod->getParameters();

		if (is_null($params)) {
			return $factoryMethod->invoke(null);
		}

		$deps = static::injectParameters($params, $args);
		return $factoryMethod->invokeArgs(null, $deps);
	}	

	protected static function injectParameters(array $params, $args)
	{
		// Creating dependencies for injection
		$injections = array();
		foreach ($params as $param) {
			$class = $param->getClass();
			if (!is_null($class)) {
				// recursion for each class that is required
				$injections[] = static::reflect($class->name, $args);
			}
			else {
				// parameter name
				$paramName = $param->getName();
				// Checking for default parameter value
				if ($param->isOptional()) {
					$default = $param->getDefaultValue();
					$injections[] = isset($args[$paramName]) ? $args[$paramName] : $param->getDefaultValue();
				}
				// If passed parameters are named like the method parameters, they will
				// be injected. If they are not found - default value will be used if any,
				// otherwise NULL will be passed 
				$injections[] = isset($args[$paramName]) ? $args[$paramName] : (isset($args[0]) ? $args[0] : null);
			}
		}

		return $injections;
	}
}
