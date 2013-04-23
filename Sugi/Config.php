<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi;

use SugiPHP\Config\Config as BaseConfig;
use SugiPHP\Config\FileLocator;
use SugiPHP\Config\NativeLoader;
use SugiPHP\Config\JsonLoader;
use SugiPHP\Config\YamlLoader;
use Sugi\Config\NeonLoader;

/**
 * This class is temporary and will replace Sugi\Config
 */
class Config
{
	/**
	 * FileLocator search path.
	 * 
	 * @var string|array
	 */
	public static $path = "";

	/**
	 * Instance of SugiPHP\Config\Config.
	 * 
	 * @var SugiPHP\Config\Config
	 */
	protected static $config;

	/**
	 * Handles dynamic static calls to the object.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		$instance = static::getInstance();

		return call_user_func_array(array($instance, $method), $parameters);
	}

	public static function getInstance()
	{
		if (!static::$config) {
			static::$config = static::factory(array("path" => static::$path));
		}

		return static::$config;
	}

	public static function factory(array $config)
	{
		$path = $config["path"];
		$fileLocator = new FileLocator($path);
		$nativeLoader = new NativeLoader($fileLocator);
		$yamlLoader = new YamlLoader($fileLocator);
		$neonLoader = new NeonLoader($fileLocator);
		$jsonLoader = new JsonLoader($fileLocator);

		return new BaseConfig(array($nativeLoader, $yamlLoader, $neonLoader, $jsonLoader));
	}
}
