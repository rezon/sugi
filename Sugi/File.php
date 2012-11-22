<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.11.22
 */

/**
 * Facedes \Sugi\Files
 */
class File extends Facade
{
	protected static $instance;

	protected static function _getInstance()
	{
		if (!static::$instance) static::$instance = new Files;

		return static::$instance;
	}
}
