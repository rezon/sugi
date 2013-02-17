<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Facades \Sugi\Container
 */
class Registry extends Facade
{
	protected static $instance;

	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::$instance = new Container();
		}

		return static::$instance;
	}
}
