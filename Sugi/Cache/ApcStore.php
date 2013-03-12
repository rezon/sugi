<?php namespace Sugi\Cache;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class ApcStore implements StoreInterface
{
	// for some optimization reasons in APC it does not invalidate
	// data on same request. @see  https://bugs.php.net/bug.php?id=58084
	// To fix this behavior we'll use cache to store items along with timestamps
	protected $ttls = array();

	function set($key, $value, $ttl = 0)
	{
		$res = apc_store($key, $value, $ttl);
		unset($this->ttls[$key]);
		// fixing ttl only if it is set
		if ($res and $ttl) {
			$this->ttls[$key] = microtime(true) + $ttl;
		}
		return $res;
	}

	function get($key)
	{
		$result = apc_fetch($key, $success);

		if (!$success) {
			return null;
		}

		if (isset($this->ttls[$key]) and $this->ttls[$key] < microtime(true)) {
			unset($this->ttls[$key]);
			return null;
		}

		return $result;
	}

	function has($key)
	{
		if (!apc_exists($key)) {
			return false;
		}

		if (isset($this->ttls[$key]) and $this->ttls[$key] < microtime(true)) {
			unset($this->ttls[$key]);
			return false;
		}

		return true;
	}

	function delete($key)
	{
		if (apc_delete($key)) {
			unset($this->ttls[$key]);
			return true;
		}
		return false;
	}

	function flush()
	{
		if (apc_clear_cache("user")) {
			unset($this->ttls);
			return true;
		}
		return false;
	}

	// public function inc($key, $offset = 1, $defaultValue = 0, $ttl = 0)
	// {
	// 	$inc = apc_inc($key, $offset, $success);
	// 	// thre was no key
	// 	if ($inc === false) {
	// 		// we need custom initial value to be set
	// 		return ($this->set($key, $defaultValue, $ttl)) ? $defaultValue : false;
	// 	}

	// 	return $inc;
	// }
}
