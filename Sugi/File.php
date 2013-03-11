<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Facades \Sugi\Files
 */
class File extends Facade
{
	protected static $instance;

	static function _getInstance()
	{
		if (!static::$instance) {
			static::$instance = new Files();
		}

		return static::$instance;
	}
}
