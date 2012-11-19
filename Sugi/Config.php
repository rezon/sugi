<?php namespace Sugi;
/**
 * Simplifies configurations via automatic loading and caching configuration files.
 * Unlike Registry Config if trying to load configuration files on demand.
 *
 * To get a values from database config file use:
 * <code>
 * 		// simple (no file auto-loading) config
 * 		Config::set('debug', TRUE);
 * 		Config::get('debug'); 							// returns TRUE;
 * 		Config::set('test', array('foo' => 'bar'));
 * 		Config::get('test'); 							// returns an array
 * 		Config::get('test.foo'); 						// returns 'bar'
 * 		Config::get('test.bar'); 						// returns NULL
 * 		Config::get('test.bar', 'foobar'); 				// returns 'foobar'
 *
 * 		// before using Config with auto-loading functions it is good to set special config item, which will resolve the search path
 * 		Config::set('_path', '/your/full/path/to/config');
 * 		
 * 		$dbconf = Config::database(); 					// returns an array if the file 'database.conf.php' exists in the search path
 * 		$dbname = Config::database('name');				// returns an item from the config file 'database.conf.php'
 * 		$host = Config::database('host', 'localhost'); 	// returns an item, and if the item does not exists will return your default value - 'localhost'
 * 		$tz = Config::application('default.timezone'); 	// returns an item timezone from the array returned from the config file 'application.conf.php'
 *   	
 *   	// return value when trying to get an item from some file that does not exist will be null, or your default value
 *   	Config::unexistingconfig(); 					// returns NULL
 *   	Config::unexistingconfig('foo'); 				// returns NULL
 *   	Config::unexistingconfig('foo', 'bar'); 		// returns 'bar'
 *
 * </code>
 *
 * The name of the config file can be anything, but 'set' and 'get' - which are used as a simple registry (without file auto-loading).
 * 
 * The values can be invoked with dot notation like Config::get('key.subkey').
 * 
 * Configuration files are PHP with (.conf.php extensions) and JSON (with .json extension)
 *
 * Note: Items could not be set with dot donation. Use arrays!
 * 
 * @package Sugi
 * @version 12.11.19
 */

include_once __DIR__."/File.php";

class Config
{
	// cache of configuration items
	protected static $registry = array();

	// cache of loaded files
	protected static $files_registry = array();

	// accepted extensions and search file order
	protected static $extorder = array('.conf.php', '.json');

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
			$path = rtrim(static::get('_path', '.'), '/\\') . DIRECTORY_SEPARATOR;
			$values = static::_load("{$path}{$name}");
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


	/**
	 * Search and load configuration file
	 * 
	 * @param string $filebase
	 * @return mixed
	 */
	protected static function _load($filebase)
	{
		foreach (static::$extorder as $ext) {
			if (File::exists("{$filebase}{$ext}")) {
				if ($ext == '.json') return json_decode(File::get("{$filebase}{$ext}"), true);
				return include("{$filebase}{$ext}");
			}
		}
		return null;
	}

	/**
	 * Search for a key with dot notation in the array. If the key is not found default value is returned
	 * 
	 * @param array $values
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
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
