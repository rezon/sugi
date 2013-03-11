<?php namespace Sugi\Cache;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class MemcacheStore implements StoreInterface
{
	protected $memcached;

	/**
	 * Creates MemcacheStore instance
	 * 
	 * @param  array $config Server Configurations
	 * @return MemcacheStore
	 */
	public static function factory(array $config)
	{
		$memcached = new \Memcached();

		// empty config
		if (empty($config)) {
			$host = "127.0.0.1";
			$port = 11211;
			$weight = 1;
			
			$memcached->addServer($host, $port, $weight);		
		} elseif (count($config) == 1) {
			// only one server
			$server = $config[0];
			$host = empty($server["host"]) ? "127.0.0.1" : $server["host"];
			$port = empty($server["port"]) ? 11211 : $server["port"];
			$weight = empty($server["weight"]) ? 1 : $server["weight"];

			$memcached->addServer($host, $port, $weight);		
		} else {
			// multiple servers
			$memcached->addServers($config);
		}

		// at least one server should be running
		$servers = $memcached->getVersion();
		foreach ($servers as $server) {
			if ($server != "255.255.255") {
				return new MemcacheStore($memcached);
			}
		}

		throw new \Exception("Could not create memcached");
	}

	public function __construct(\Memcached $memcached)
	{
		$this->memcached = $memcached;
	}

	function set($key, $value, $ttl = 0)
	{
		return $this->memcached->set($key, $value, $ttl);
	}

	function get($key)
	{
		$result = $this->memcached->get($key);

		if (($result === false) and ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND)) {
			return null;
		}
		
		return $result;
	}

	function has($key)
	{
		$result = $this->memcached->get($key);
		if (($result === false) and ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND)) {
			return false;
		}
		return true;
	}

	function delete($key)
	{
		return $this->memcached->delete($key);
	}

	function flush()
	{
		return $this->memcached->flush();
	}

	// public function inc($key, $offset = 1, $defaultValue = 0, $ttl = 0)
	// {
	// 	$inc = $this->memcached->increment($key, $offset);
	// 	// there was no key, or on some other error
	// 	if ($inc === false) {
	// 		// we need custom initial value to be set
	// 		return ($this->set($key, $defaultValue, $ttl) === false) ? false : $defaultValue;
	// 	}

	// 	return $inc;
	// }
}
