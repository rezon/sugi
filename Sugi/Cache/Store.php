<?php namespace Sugi\Cache;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class Store
{
	protected $driver;

	/**
	 * Class constructor
	 *
	 * @param array $config Cache configuration
	 */
	public function __construct(StoreInterface $driver)
	{
		$this->driver = $driver;
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
	public function set($key, $value, $ttl = 0)
	{
		return $this->driver->set($key, $value, $ttl);
	}

	/**
	 * Fetches a stored variable from the cache
	 * 
	 * @param  string $key The key used to store the value
	 * @return mixed Returns NULL if the key does not exist in the store or the value was expired (see $ttl)
	 */
	public function get($key, $defaultValue = null)
	{
		$result = $this->driver->get($key);

		return is_null($result) ? $defaultValue : $result;
	}

	/**
	 * Checks if the key exists
	 * 
	 * @param  string $key 
	 * @return boolean TRUE if the key exists, otherwise FALSE
	 */
	public function has($key)
	{
		return $this->driver->has($key);
	}

	/**
	 * Removes a stored variable from the cache
	 * 
	 * @param string $key
	 */
	public function delete($key)
	{
		$this->driver->delete($key);
	}

	/**
	 * Invalidate all items in the cache
	 */
	public function flush()
	{
		$this->driver->flush();
	}

	/**
	 * Increment numeric item's value.
	 * If there is no such key it will create a key with initialValue + $offset.
	 * If the stored value is non numeric - false will be returned.
	 * 
	 * @param  string $key
	 * @param  integer $offset
	 * @param  integer $initialValue
	 * @param  integer $ttl
	 * @return integer or FALSE on failure
	 */
	public function inc($key, $offset = 1, $initialValue = 0, $ttl = 0)
	{
		if (method_exists($this->driver, "inc")) {
			return $this->driver->inc($key, 1, $initialValue, $ttl);
		}

		$oldValue = $this->driver->get($key);
		if (is_null($oldValue)) {
			$newValue = $initialValue + $offset;
		} elseif (is_numeric($oldValue)) {
			$newValue = $oldValue + $offset;
		} else {
			return false;
		}
		$res = $this->driver->set($key, $newValue, $ttl);

		return is_null($res) ? false : $newValue;
	}

	/**
	 * Decrements numeric item's value.
	 */
	public function dec($key, $offset = 1, $initialValue = 0, $ttl = 0)
	{
		if (method_exists($this->driver, "dec")) {
			return $this->driver->dec($key, $offst, $initialValue, $ttl);
		}

		$oldValue = $this->driver->get($key);
		if (is_null($oldValue)) {
			$newValue = $initialValue - $offset;
		} elseif (is_numeric($oldValue)) {
			$newValue = $oldValue - $offset;
		} else {
			return false;
		}
		$res = $this->driver->set($key, $newValue, $ttl);

		return is_null($res) ? false : $newValue;
	}

	/**
	 * Creates a new variable in the data store under new key
	 * add() is similar to set(), but the operation fails if the key already exists
	 * 
	 * @param  string $key The key under which to store the value
	 * @param  mixed $value The value to store
	 * @param  integer $ttl Expiration time in seconds, after which the value is invalidated (deleted)
	 * @return boolean TRUE on success or FALSE on failure
	 */
	// public function add($key, $value, $ttl = 0)
	// {
	// 	return $this->driver->add($key, $value, $ttl);
	// }
}
