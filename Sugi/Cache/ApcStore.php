<?php namespace Sugi\Cache;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class ApcStore implements StoreInterface
{
	function set($key, $value, $ttl = 0)
	{
		return apc_store($key, $value, $ttl);
	}

	function get($key)
	{
		$result = apc_fetch($key, $success);

		return ($success) ? $result : null;
	}

	function has($key)
	{
		return apc_exists($key);
	}

	function delete($key)
	{
		return apc_delete($key);
	}

	function flush()
	{
		return apc_clear_cache("user");
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
