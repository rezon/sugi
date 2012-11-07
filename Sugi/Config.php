<?php namespace Sugi;
/**
 * Simplifies configurations via automatic loading and caching configuration files.
 * Unlike Registry Config if trying to load settings on demand.
 * The values can be invoked with dot notation like Config::get('file.key').
 * If there is no dot, the key is assumed - Config::get('key').
 *
 * Note: the config files should not have extra dots (.) in their names.
 *
 * @todo If there are more than one dots in the key we assume the value is set as array,
 *   	 so we will return the corresponding key of that array - Config('file.key.item').
 * @todo Except JSON as configuration files (with .json extension)
 * 
 * @package Sugi
 * @version 12.11.07
 */

include_once __DIR__."/File.php";

class Config
{
	// cache of all configuration items
	protected static $registry = array();

	/**
	 * Gets an item.
	 * If the item does not exists it will try to load configuration file.
	 * If the configuration file does not exists, or when the key is could 
	 * not be found there the $default value is returned.
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		$parts = explode('.', $key);
		$values = static::$registry;

		if (count($parts) > 1) {
			list($file, $key) = $parts;
			if (array_key_exists($file, static::$registry)) $values = static::$registry[$file];
			else $values = static::load_config($file);
		}

		return static::arr_key($values, $key, $default);
	}

	/**
	 * Sets an item for furthure use.
	 * 
	 * @param string $key
	 * @param string $value
	 */
	public static function set($key, $value)
	{
		static::$registry[$key] = $value;
	}

	/**
	 * Loads configuration file.
	 * 
	 * @param string $file
	 * @return array
	 */
	protected static function load_config($file)
	{
		$values = null;
		if (File::exists("$file.php")) {
			$values = include("$file.php");
		}
		static::$registry[$file] = $values;

		return $values;
	}

	/**
	 * Gets an item from named array. If the key does not exists in the array returns $default value.
	 * 
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	protected static function arr_key($array, $key, $default = null)
	{
		if (is_array($array) and array_key_exists($key, $array)) return $array[$key];
		return $default;
	}
}
