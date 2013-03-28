<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi;

use SugiPHP\Config\Config as SugiPHPConfig;
use SugiPHP\Config\FileLocator;
use SugiPHP\Config\NativeLoader;
use SugiPHP\Config\JsonLoader;


/**
 * This class is temporary and will replace Sugi\Config
 */
class FileConfig extends Facade
{
	protected static $instance;
	protected static $nativeLoader;
	protected static $jsonLoader;
	protected static $fileLocator;

	/**
	 * @inheritdoc
	 */
	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::configure("");
		}

		return static::$instance;
	}

	public static function configure($path)
	{
		static::$fileLocator = new FileLocator($path);
		static::$nativeLoader = new NativeLoader(static::$fileLocator);
		static::$jsonLoader = new JsonLoader(static::$fileLocator);
		static::$instance = new SugiPHPConfig(array(static::$nativeLoader, static::$jsonLoader));
	}
}
