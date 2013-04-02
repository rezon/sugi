<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi;

use \SugiPHP\Database\Database;
use \SugiPHP\Database\MySqlDriver as mysql;
use \SugiPHP\Database\PgSqlDriver as pgsql;
use \SugiPHP\Database\SQLiteDriver as sqlite;
use \Sugi\FileConfig as Cfg;

/**
 * Database class - database abstraction class.
 */
class DB extends Facade
{
	protected static $instance;

	/**
	 * @inheritdoc
	 */
	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::configure(Cfg::get("database"));
		}

		return static::$instance;
	}

	/**
	 * Used for Dependency Injections
	 */
	public static function configure(array $config)
	{
		if (empty($config["type"])) {
			throw new \SugiPHP\Database\Exception("Required database type parameter is missing", "internal_error");
		}
		$type = $config["type"];
		unset($config["type"]);

		if (isset($config[$type]) and is_array($config[$type])) {
			$config = $config[$type];
		}

		// if we've passed custom Store instance
		if (!is_string($type)) {
			$driver = $type;
		} else {
			$type = strtolower($type);
			if (($type == "mysql") or ($type == "mysqli")) {
				$driver = new mysql($config);
			} elseif ($type == "pgsql") {
				$driver = new pgsql($config);
			} elseif (($type == "sqlite") or ($type == "sqlite3")) {
				$driver = new sqlite($config);
			} else {
				$driver = DI::reflect($type, $config);
			}
		}

		static::$instance = new Database($driver);

		return static::$instance;
	}
}
