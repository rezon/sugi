<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.11.22
 */

/**
 * Facade class.
 * Extending this class will give you access to object's methods with static calls
 */
abstract class Facade 
{
	/**
	* Get instance of facaded class
	*/
	protected static function _getInstance() {
		throw new \Exception("Implement _getInstance method in facaded class.");
	}

	/**
	* Handle dynamic static calls to the object
	*
	* @param string $method
	* @param array $parameters
	* @return mixed
	*/
	public static function __callStatic($method, $parameters)
	{
		$instance = static::_getInstance();

		return call_user_func_array(array($instance, $method), $parameters);
	}
}
