<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Cache\Cache as SugiPhpCache;
use SugiPHP\Cache\MemcachedStore;
use SugiPHP\Cache\MemcacheStore;
use SugiPHP\Cache\ApcStore;
use SugiPHP\Cache\FileStore;

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
		// if we've passed custom Store instance
		if (!is_string($store)) {
			$storeInterface = $store;
		} else {
			$storeConfig = isset($config[$store]) ? $config[$store] : array();

			if ($store == "memcached") {
				$storeInterface = MemcachedStore::factory($storeConfig);
			} elseif ($store == "memcache") {
				$storeInterface = MemcacheStore::factory($storeConfig);
			} elseif ($store == "apc") {
				$storeInterface = new ApcStore($storeConfig);
			} elseif ($store == "file") {
				$storeInterface = new FileStore($storeConfig["path"]);
			} else {
				$storeInterface = DI::reflect($store, $storeConfig);
			}
		}

		// creating new SugiPHP\Cache instance
		$instance = new SugiPhpCache($storeInterface);

		// check we want keys prefix
		if (!empty($config["prefix"])) {
			$instance->setPrefix($config["prefix"]);
		}

		// save it
		static::$instance = $instance;
	} 
}
