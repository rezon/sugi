<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Event as SugiEvent;
use Sugi\Event\Event;
use Sugi\Event\Dispatcher;

class EvnetTests extends PHPUnit_Framework_TestCase
{
	
	public $eventsDispatched;
	public $dispatcher;
	public $event;

	public function setUp()
	{
		if (version_compare(PHP_VERSION, "5.4.0") < 0) {
			$this->markTestSkipped("EventTest requires PHP version >= 5.4");
		}

		$this->eventsDispatched = 0;
		$this->dispatcher = new Dispatcher();
		$this->event = new Event("unit.test", array());
	}

	public function testOneListener()
	{
		// creating listener
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});
		// firing
		$this->dispatcher->fire($this->event);
		// checking it was fired
		$this->assertEquals(1, $this->eventsDispatched);
	}

	public function testOneListenerTriggerOtherEver()
	{
		// creating listener
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		$this->dispatcher->fire(new Event("unit.test2", array()));
		$this->assertEquals(0, $this->eventsDispatched);
	}

	public function testTwoListeners()
	{
		// creating listener
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});
		// creating second listener for the same event
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		$this->dispatcher->fire($this->event);
		$this->assertEquals(2, $this->eventsDispatched);
	}

	public function testTwoListenersTwoFires()
	{
		// creating listener
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});
		// creating second listener for the same event
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		// 2 fires
		$this->dispatcher->fire($this->event);
		$this->dispatcher->fire($this->event);
		$this->assertEquals(4, $this->eventsDispatched);
	}

	public function testEventParams()
	{
		// creating listener
		$this->dispatcher->listen("unit.test", function($e) {
			$this->eventsDispatched++;
			$this->assertSame("unit.test", $e->getName());
			$params = $e->getParams();
			$this->assertContains("foo", $params);
			$this->assertContains("foobar", $params);
			$this->assertArrayHasKey("bar", $params);
			$this->assertEquals("foobar", $params["bar"]);
		});

		// firing event
		$event = new Event("unit.test", array("foo", "bar" => "foobar"));
		$this->dispatcher->fire($event);
		// check that the event was dispatched
		$this->assertEquals(1, $this->eventsDispatched);
	}

	public function testLazyCreation()
	{
		// register listener
		\Sugi\Event::listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		// firing event
		\Sugi\Event::fire("unit.test", array());
		$this->assertEquals(1, $this->eventsDispatched);

		// adding second listener
		\Sugi\Event::listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		// firing event with 2 listeners
		\Sugi\Event::fire("unit.test", array());
		$this->assertEquals(3, $this->eventsDispatched);

		// firing not listened event
		\Sugi\Event::fire("unit.test2", array());
		$this->assertEquals(3, $this->eventsDispatched);
	}
}
