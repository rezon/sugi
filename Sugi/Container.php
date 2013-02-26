<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Sugi\Container 
 */
class Container implements \IteratorAggregate, \ArrayAccess, \Countable
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
		return ($this->has($key)) ? $this->items[$key] : $default;
	}

	/**
	 * Check an object exists
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * @see has()
	 */
	public function exists($key)
	{
		return $this->has($key);
	}

	/**
	 * Remove previously added item
	 * @param string $key
	 */
	public function remove($key)
	{
		unset($this->items[$key]);	
	}

	/**
	 * @see remove()
	 */
	public function delete($key)
	{
		$this->remove($key);
	}

	/**
	 * Returns all saved items
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Returns the parameter keys
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->items);
	}

	/**
	 * Add a set of items
	 * @param array $items
	 */
	public function add(array $items = array())
	{
		$this->items = array_replace($this->items, $items);
    }

	/**
	 * Returns item count
	 * @implements \Countable
	 * @see http://www.php.net/manual/en/class.countable.php
	 * 
	 * @return integer
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * Replaces all item set with given items
	 * @param array $items
	 */
	public function replace(array $items = array())
	{
		$this->items = $items;
	}


	/**
	 * @implements \ArrayAccess
	 * @see http://www.php.net/manual/en/arrayaccess.offsetset.php
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
	
	/**
	 * @implements \ArrayAccess
	 * @see http://www.php.net/manual/en/arrayaccess.offsetexists.php
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * @implements \ArrayAccess
	 * @see http://www.php.net/manual/en/arrayaccess.offsetunset.php
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
	
	/**
	 * @implements \ArrayAccess
	 * @see http://www.php.net/manual/en/arrayaccess.offsetget.php
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset, null);
	}


	/**
	 * Returns an iterator for parameters
	 * @implements \IteratorAggregate
	 * @see http://www.php.net/manual/en/class.iteratoraggregate.php
	 * 
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}
