<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Facade class.
 * Extending this class gives us access to object methods with static calls
 */
abstract class Facade 
{
	/**
	* Get instance of facaded class
	*/
	abstract protected static function _getInstance();

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
