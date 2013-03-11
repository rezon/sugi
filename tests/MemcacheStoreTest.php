<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cache\MemcacheStore as Store;

class MemcacheStoreTest extends PHPUnit_Framework_TestCase
{
	public static $store;

	public static function setUpBeforeClass()
	{
		try {
			static::$store = Store::factory(array());
		} catch (\Exception $e) {}
	}

	public function setUp()
	{
		if (!static::$store) {
			$this->markTestSkipped("Could not connect to memecached");
		}
		static::$store->delete("phpunittestkey");
	}

	public function testCheckInstance()
	{
		$this->assertInstanceOf("\Sugi\Cache\StoreInterface", static::$store);
	}

	public function testReturnsNullWhenNotFound()
	{
		$this->assertSame(null, static::$store->get("phpunittestkey"));
	}

	public function testHasReturnsFalseIfNotFound()
	{
		$this->assertSame(false, static::$store->has("phpunittestkey"));
	}

	public function testDeleteReturnsFalseIfNotFound()
	{
		$this->assertSame(false, static::$store->delete("phpunittestkey"));
	}

	public function testSet()
	{
		$this->assertSame(true, static::$store->set("phpunittestkey", "phpunittestvalue"));
	}

	public function testGet()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertEquals("phpunittestvalue", static::$store->get("phpunittestkey"));
		$this->assertEquals("phpunittestvalue", static::$store->get("phpunittestkey", "default"));
	}

	public function testTTLMinus1()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertEquals(null, static::$store->get("phpunittestkey"));
	}

	public function testTTL()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue", 1);
		$this->assertSame(true, static::$store->has("phpunittestkey"));
		// this is working fine here, but on APC is not working correctly, so I'll skip it
		// sleep(1);
		// $this->assertSame(false, static::$store->has("phpunittestkey"));
	}

	public function testHas()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertSame(false, static::$store->has("phpunittestkey"));
		static::$store->set("phpunittestkey", "phpunittestvalue", 1);
		$this->assertSame(true, static::$store->has("phpunittestkey"));
	}

	public function testDeleteReturnsTrue()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, static::$store->has("phpunittestkey"));
		$this->assertSame(true, static::$store->delete("phpunittestkey"));
		$this->assertSame(false, static::$store->has("phpunittestkey"));
	}

	public function testFlush()
	{
		static::$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, static::$store->has("phpunittestkey"));
		$this->assertSame(true, static::$store->flush());
		$this->assertSame(false, static::$store->has("phpunittestkey"));
	}
/*
	@see note on ApcStoreTest

	public function testIncrementWithInitialSet()
	{
		static::$store->set("phpunittestkey", 1);
		$this->assertSame(1, static::$store->get("phpunittestkey"));
		// check return falue is increased one
		$this->assertSame(2, static::$store->inc("phpunittestkey"));
		$this->assertSame(2, static::$store->get("phpunittestkey"));
	}

	public function testIncrementWithNoInitialSet()
	{
		$this->assertSame(0, static::$store->inc("phpunittestkey"));
		static::$store->delete("phpunittestkey");
		// initial value
		$this->assertSame(7, static::$store->inc("phpunittestkey", 1, 7));
	}

	public function testIncrementWithCustomOffset()
	{
		$this->assertSame(0, static::$store->inc("phpunittestkey", 2));
		$this->assertSame(0, static::$store->get("phpunittestkey"));
		$this->assertSame(3, static::$store->inc("phpunittestkey", 3));
		$this->assertSame(7, static::$store->inc("phpunittestkey", 4));
	}

	public function testIncrementWithCustomOffsetAndInitialValue()
	{
		$this->assertSame(10, static::$store->inc("phpunittestkey", 2, 10));
		$this->assertSame(10, static::$store->get("phpunittestkey"));
		$this->assertSame(13, static::$store->inc("phpunittestkey", 3, 11));
		$this->assertSame(17, static::$store->inc("phpunittestkey", 4, 12));
	}

	public function testIncrementNotNumber()
	{
		static::$store->set("phpunittestkey", "phpunitvalue");
		// TODO: overrides it - maybe it's not perfect solution
		$this->assertSame(0, static::$store->inc("phpunittestkey"));
	}
*/

}
