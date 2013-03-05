<?php namespace Sugi\Event;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * TODO: the dispatcher should be interfaced
 */
class Event
{
	protected $name;
	protected $params;

	/**
	 * Dispatcher who handles event firing
	 * @var Event\Dispatcher
	 */
	protected $dispatcher = null;

	public function __construct($name, array $params)
	{
		$this->name = $name;
		$this->params = $params;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getParam($name)
	{
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}

	public function setDispatcher(Dispatcher $d)
	{
		$this->dispatcher = $d;
	}

	public function getDispatcher($d)
	{
		return $this->dispatcher;
	}
}
