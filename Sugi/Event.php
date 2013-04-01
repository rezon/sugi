<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi;

use \SugiPHP\Events\Event as Evnt;
use \SugiPHP\Events\Dispatcher;

/**
 * Facades SugiPHP\Events\Dispatcher and SugiPHP\Events\Event for lazy people like me
 *
 * There are 2 basic static methods:
 *  - listen - tells the dispatcher to trigger given callback function when some event occurs
 *  - fire   - creates an event and through the dispatcher notifies all listeners for that event
 */
class Event extends Facade
{
	protected static $instance = null;

	/**
	 * @inheritdoc
	 */
	protected static function _getInstance()
	{
		if (!static::$instance) {
			static::$instance = new Dispatcher();
		}

		return static::$instance;
	}

	public static function listen($eventName, $callback)
	{
		$dispatcher = static::_getInstance();
		$dispatcher->addListener($eventName, $callback);
	}

	public static function fire($eventName, array $params = array())
	{
		$dispatcher = static::_getInstance();

		$event = new Evnt($eventName, $params);
		$event->setDispatcher($dispatcher);

		$dispatcher->dispatch($event);
	}
}
