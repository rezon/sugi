<?php namespace Sugi;
/**
 * Registry
 * 
 * @package Sugi
 * @version 12.11.03
 */

/**
 * \Sugi\Registry
 */
class Registry
{
	protected static $items;

	/**
	 * Register an object with a given name
	 * 
	 * @param string $key
	 * @param mixed $obj
	 */
	public static function set($key, $obj)
	{
		static::$items[$key] = $obj;
	}

	/**
	 * Check an object has been added
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public static function isRegistered($key)
	{
		return array_key_exists($key, static::$items);
	}

	/**
	 * Returns an item which was previously added or returns default value
	 * 
	 * @param string $key
	 * @param mixed - You can pass second param, which acts as default value if key does not exists
	 * @return mixed
	 * @throws \Exception If the key is not registered and a default value was not set
	 */
	public static function get($key)
	{
		// check we have it
		if (!static::isRegistered($key)) {
			// if we have passed second argument it will be returned
			$args = func_get_args();
			if (count($args) > 1) {
				return $args[1];
			}
			throw new \Exception("Item $key does not exists in the Registry.");
		}

		return static::$items[$key];
	}

	/**
	 * Remove previously added item
	 * 
	 * @param string $key
	 */
	public static function remove($key)
	{
		unset(static::$items[$key]);
	}
}
