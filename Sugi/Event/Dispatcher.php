<?php namespace  Sugi\Event;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */


class Dispatcher
{
	protected $listeners = array();

	/**
	 * Registers the callback function to be triggered on event
	 * 
	 * @param string $eventName
	 * @param callable $callback 
	 */
	public function listen($eventName, $callback)
	{
		$this->listeners[$eventName][] = $callback;
	}

	/**
	 * Notifies registered for that event callback function
	 * 
	 * @param Event $event
	 */
	public function fire(Event $event)
	{
		$eventName = $event->getName();
		foreach ($this->getListeners($eventName) as $listener) {
			call_user_func($listener, $event);
		}
	}

	/**
	 * Gets the listeners of a given event or all listeners
	 * 
	 * @param string $eventName
	 * @return array
	 */
	public function getListeners($eventName = null)
	{
		if (is_null($eventName)) {
			return $this->listeners;
		}

		if (isset($this->listeners[$eventName])) {
			return $this->listeners[$eventName];
		}

		return array();
	}
}
