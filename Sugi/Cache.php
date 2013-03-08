<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class Cache
{
	/**
	 * Creates a new variable in the data store under new key
	 * add() is similar to set(), but the operation fails if the key already exists
	 * 
	 * @param  string $key The key under which to store the value
	 * @param  mixed $value The value to store
	 * @param  integer $ttl Expiration time in seconds, after which the value is invalidated (deleted)
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public static function add($key, $value, $ttl = 0)
	{

	}

	/**
	 * Stores an item in the data store
	 * set() is similar to add(), but if the key exists replaces the value in the cache
	 * 
	 * @param  string $key The key under which to store the value
	 * @param  mixed $value The value to store
	 * @param  integer $ttl Expiration time in seconds, after which the value is invalidated (deleted)
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public static function set($key, $value, $ttl = 0)
	{

	}

	/**
	 * Fetches a stored variable from the cache
	 * 
	 * @param  string $key The key used to store the value
	 * @return mixed Returns FALSE if the key does not exist in the store or the value was expired (see $ttl)
	 */
	public static function get($key)
	{

	}

	/**
	 * Checks if the key exists
	 * 
	 * @param  string $key 
	 * @return boolean TRUE if the key exists, otherwise FALSE
	 */
	public static function has($key)
	{

	}

	/**
	 * Removes a stored variable from the cache
	 * 
	 * @param string $key
	 */
	public static function delete($key)
	{

	}

	public static function inc($key, $offset = 1, $initialValue = 0, $ttl = 0)
	{

	}

	public static function dec($key, $offset = 1, $initialValue = 0, $ttl = 0)
	{

	}

	/**
	 * Invalidate all items in the cache
	 */
	public static function flush()
	{

	}
}
