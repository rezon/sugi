<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Sugi\Container 
 */
class Container implements \ArrayAccess
{
	protected $items = array();

	/**
	 * Register an object with a given name
	 * 
	 * @param string $key
	 * @param mixed $obj
	 */
	public function set($key, $value)
	{
		if (is_null($key)) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * Returns an item which was previously added or returns default value
	 * 
	 * @param string $key
	 * @param mixed - You can pass second param, which acts as default value if key does not exists
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return ($this->exists($key)) ? $this->items[$key] : $default;
	}

	/**
	 * Check an object has been added
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function exists($key)
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Remove previously added item
	 * @param string $key
	 */
	public function delete($key)
	{
		unset($this->items[$key]);
	}

	/**
	 * Returns item count
	 * @return integer
	 */
	public function count()
	{
		return count($this->items);
	}


	/*
	 * Implementing methods for interface ArrayAccess
	 */

	/**
	 * @see http://www.php.net/manual/en/arrayaccess.offsetset.php
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
	
	/**
	 * @see http://www.php.net/manual/en/arrayaccess.offsetexists.php
	 */
	public function offsetExists($offset)
	{
		return $this->isRegistered($offset);
	}

	/**
	 * @see http://www.php.net/manual/en/arrayaccess.offsetunset.php
	 */
	public function offsetUnset($offset)
	{
		$this->delete($offset);
	}
	
	/**
	 * @see http://www.php.net/manual/en/arrayaccess.offsetget.php
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset, null);
	}
}
