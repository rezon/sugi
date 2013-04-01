<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Event as SugiEvent;

class EvnetTests extends PHPUnit_Framework_TestCase
{
	public function testLazyCreation()
	{
		// register listener
		SugiEvent::listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		// firing event
		SugiEvent::fire("unit.test", array());
		$this->assertEquals(1, $this->eventsDispatched);

		// adding second listener
		SugiEvent::listen("unit.test", function($e) {
			$this->eventsDispatched++;
		});

		// firing event with 2 listeners
		SugiEvent::fire("unit.test", array());
		$this->assertEquals(3, $this->eventsDispatched);

		// firing not listened event
		SugiEvent::fire("unit.test2", array());
		$this->assertEquals(3, $this->eventsDispatched);
	}
}
