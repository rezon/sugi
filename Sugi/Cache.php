<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Cache\Store;
use Cache\Config;

class Cache extends Facade
{
	protected static $instance;

	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::configure(Config::file("cache"));
		}

		return static::$instance;
	}

	public function configure(array $config = array())
	{
		if (empty($config["store"])) {
			throw new \Exception("Cache store must be set");
		}
		$store = $config["store"];
		$config = $config[$store];
		$className = "\\Sugi\\Store\\$storeStore";
		$storeInterface = new $className($config);
		static::$instance = new Store($storeInterface);
	} 
}
