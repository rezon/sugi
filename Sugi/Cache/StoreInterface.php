<?php namespace Sugi\Cache;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

interface StoreInterface
{
	function set($key, $value, $ttl);
	function get($key);
	function has($key);
	function delete($key);
	function flush();
}
