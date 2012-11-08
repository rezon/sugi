<?php namespace Sugi;
/**
 * Simplifies configurations via automatic loading and caching configuration files.
 * Unlike Registry Config if trying to load configuration files on demand.
 *
 * To get a values from database config file use:
 * <code>
 * 		$dbconf = Config::database(); 					// returns an array if the file 'database.php' exists in the search path
 * 		$dbname = Config::database('name');				// returns an item from the config file 'database.php'
 * 		$host = Config::database('host', 'localhost'); 	// returns an item, and if the item does not exists will return your default value - 'localhost'
 * 		$tz = Config::application('default.timezone'); 	// returns an item timezone from the array returned from the config file 'application.php'
 *   	
 *   	// return value when trying to get an item from some file that does not exist will be null, or your default value
 *   	Config::unexistingconfig(); 					// returns NULL
 *   	Config::unexistingconfig('foo'); 				// returns NULL
 *   	Config::unexistingconfig('foo', 'bar'); 		// returns 'bar'
 *
 * 		// simple (no file auto-loading) config
 * 		Config::set('debug', TRUE);
 * 		Config::get('debug'); 							// returns TRUE;
 * 		Config::set('test', array('foo' => 'bar'));
 * 		Config::get('test'); 							// returns an array
 * 		Config::get('test.foo'); 						// returns 'bar'
 * 		Config::get('test.bar'); 						// returns NULL
 * 		Config::get('test.bar', 'foobar'); 				// returns 'foobar'
 * </code>
 *
 * The name of the config file can be anything, but 'set' and 'get' - which are used as a simple registry (without file auto-loading).
 * 
 * The values can be invoked with dot notation like Config::get('key.subkey').
 *
 * Note: Items could not be set with dot donation. Use arrays!
 *
 *
 * @todo Except JSON as configuration files (with .json extension)
 * 
 * @package Sugi
 * @version 12.11.08
 */

include_once __DIR__."/File.php";

class Config
{
	// cache of configuration items
	protected static $registry = array();

	// cache of loaded files
	protected static $files_registry = array();


	/**
	 * Magic method, which is responsible for auto-loading configuration files
	 * and returning configuration items from it.
	 * 
	 * @param string $name - filename
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		if (array_key_exists($name, static::$files_registry)) $values = static::$files_registry[$name];
		else {
			$values = null;
			if (File::exists("$name.php")) {
				$values = include("$name.php");
			}
			static::$files_registry[$name] = $values;
		}

		list($key, $default) = array_merge($arguments, array(null, null));

		return static::_extract($values, $key, $default);
	}

	/**
	 * Gets an item.
	 * If the item does not exists it will return default value.
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($key = null, $default = null)
	{
		$values = static::$registry;

		return static::_extract($values, $key, $default);
	}

	/**
	 * Sets an item for further use.
	 * 
	 * @param string $key
	 * @param string $value
	 */
	public static function set($key, $value)
	{
		static::$registry[$key] = $value;
	}


	protected static function _extract($values, $key, $default)
	{
		$parts = explode('.', $key);
		foreach ($parts as $part) {
			if (!$part) return $values;
			if (!is_array($values) or !array_key_exists($part, $values)) return $default;
			$values = $values[$part];
		}

		return $values;
	}
}
