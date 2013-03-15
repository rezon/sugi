<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Cache\Cache as SugiPhpCache;
use SugiPHP\Cache\MemcachedStore;
use SugiPHP\Cache\ApcStore;

class Cache extends Facade
{
	protected static $instance;

	/**
	 * @inheritdoc
	 */
	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::configure(Config::file("cache"));
		}

		return static::$instance;
	}

	public static function configure(array $config = array())
	{
		if (empty($config["store"])) {
			throw new \Exception("Cache store must be set");
		}
		$store = $config["store"];
		$config = isset($config[$store]) ? $config[$store] : array();

		if ($store == "memcached") {
			$storeInterface = MemcachedStore::factory($config);
		} elseif ($store == "apc") {
			$storeInterface = new ApcStore($config);
		} elseif (is_string($store)) {
			$storeInterface = DI::reflect($store, $config);
		} else {
			$storeInterface = $store;
		}

		static::$instance = new SugiPhpCache($storeInterface);
	} 
}
