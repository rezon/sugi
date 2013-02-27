<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	public function testImplements()
	{
		$container = new Container();
		$this->assertInstanceOf("\IteratorAggregate", $container);
		$this->assertInstanceOf("\ArrayAccess", $container);
		$this->assertInstanceOf("\Countable", $container);
	}

	public function testConstruct()
	{
		$container = new Container();
		$this->assertEmpty($container->all());
		$container = new Container(array("foo" => "bar"));
		$this->assertNotEmpty($container->all());
	}

	public function testGet()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertEquals("bar", $container->get("foo"));
		$this->assertEquals("bar", $container->get("foo", "foobar"));
		$this->assertEquals("foobar", $container->get("key", "foobar"));
	}

	public function testSet()
	{
		$container = new Container(array("foo" => "bar"));
		$container->set("foo", "foobar");
		$this->assertEquals("foobar", $container->get("foo"));
		$container->set("key", "value");
		$this->assertEquals("value", $container->get("key"));
	}

	/*
	 * those options can be removed from Container
	 * do not rely on them!
	 */
	public function testSetWithEmptyKeys()
	{
		$container = new Container(array("foo" => "bar"));
		$container->set("", "three");
		$this->assertEquals("three", $container->get(""));
		$container->set(null, "four");
		$container->set(null, "five");
		$container->set(0, "six");
		$container->set(0, "seven");
		$this->assertEquals("five", $container->get(null));
		$this->assertEquals("seven", $container->get(0));
	}

	public function testHas()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertSame(true, $container->has("foo"));
		$this->assertSame(false, $container->has("foobar"));
		$this->assertSame(false, $container->has("bar"));
	}

	public function testRemove()
	{
		$container = new Container(array("foo" => "bar"));
		$container->remove("foo");
		$this->assertSame(false, $container->has("foo"));
		$this->assertEmpty($container->get("foo"));
		$this->assertSame(null, $container->get("foo"));
	}

	public function testAll()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertEquals(array("foo" => "bar"), $container->all());
		$container->set("key", "value");
		$container["key2"] = "value2";
		$this->assertEquals(array("foo" => "bar", "key" => "value", "key2" => "value2"), $container->all());
	}

	public function testKey()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertEquals(array("foo"), $container->keys());
		$container->set("key", "value");
		$this->assertEquals(array("foo", "key"), $container->keys());
	}

	public function testAdd()
	{
		$container = new Container(array("foo" => "bar", "key" => "value"));
		$container->add(array("foo" => "foobar", "key2" => "value2"));
		$this->assertEquals(array("foo" => "foobar", "key" => "value", "key2" => "value2"), $container->all());
	}

	public function testCount()
	{
		$container = new Container();
		$this->assertSame(0, $container->count());
		$container->add(array("foo" => "foobar", "key2" => "value2"));
		$this->assertSame(2, $container->count());
		$container->add(array("foo" => "bar", "key" => "value"));
		$this->assertSame(3, $container->count());
	}

	public function testReplace()
	{
		$container = new Container(array("foo" => "bar", "key" => "value"));
		$container->replace(array("foo" => "foobar", "key2" => "value2"));
		$this->assertEquals(array("foo" => "foobar", "key2" => "value2"), $container->all());
	}

	/**
	 * Checking all methods that implements \ArrayAccess
	 * offsetExists()
	 * offsetGet()
	 * offsetSet()
	 * offsetUnset()
	 */
	public function testInterfaceArrayAccess()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertEquals("bar", $container->offsetGet("foo"));
		$this->assertSame(null, $container->offsetGet("key"));
		$this->assertTrue($container->offsetExists("foo"));
		$this->assertFalse($container->offsetExists("key"));
		$container->offsetSet("key", "value");
		$this->assertTrue($container->offsetExists("key"));
		$this->assertEquals("value", $container->offsetGet("key"));
		$container->offsetUnset("key");
		$this->assertFalse($container->offsetExists("key"));
	}

	public function testArrayAccess()
	{
		$container = new Container(array("foo" => "bar"));
		$this->assertEquals("bar", $container["foo"]);
		$container["key"] = "value";
		$this->assertEquals("value", $container["key"]);
		$container["key"] = "value2";
		$this->assertEquals("value2", $container["key"]);
		$this->assertSame(null, $container["key2"]);
	}
}
