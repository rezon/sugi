<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cache\Store;
use Sugi\Cache\ApcStore;
use Sugi\Cache\MemcacheStore;


class StoreTest extends PHPUnit_Framework_TestCase
{	
	public static $store;

	public static function setUpBeforeClass()
	{
		// ApcStore
		if (ini_get("apc.enabled") and ini_get("apc.enable_cli")) {
			$storeInterface = new ApcStore();
			static::$store = new Store($storeInterface);
		}
		
		// MemcacheStore
		$storeInterface = MemcacheStore::factory(array());
		if ($storeInterface->isRunning()) {
			static::$store = new Store($storeInterface);
		}
	}

	public function setUp()
	{
		if (!static::$store) {
			$this->markTestSkipped("No store is running");
		}
		static::$store->delete("phpunittestkey");
	}

	public function testReturnsNullWhenNotFound()
	{
		$this->assertSame(null, static::$store->get("phpunittestkey"));
	}

	public function testHasReturnsFalseIfNotFound()
	{
		$this->assertSame(false, static::$store->has("phpunittestkey"));
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
		$this->assertSame(1, static::$store->inc("phpunittestkey"));
		static::$store->delete("phpunittestkey");
		// initial value
		$this->assertSame(8, static::$store->inc("phpunittestkey", 1, 7));
	}

	public function testIncrementWithCustomOffset()
	{
		$this->assertSame(2, static::$store->inc("phpunittestkey", 2));
		$this->assertSame(2, static::$store->get("phpunittestkey"));
		$this->assertSame(5, static::$store->inc("phpunittestkey", 3));
		$this->assertSame(9, static::$store->inc("phpunittestkey", 4));
	}

	public function testIncrementWithCustomOffsetAndInitialValue()
	{
		$this->assertSame(12, static::$store->inc("phpunittestkey", 2, 10));
		$this->assertSame(12, static::$store->get("phpunittestkey"));
		$this->assertSame(15, static::$store->inc("phpunittestkey", 3, 11));
		$this->assertSame(19, static::$store->inc("phpunittestkey", 4, 12));
	}

	public function testIncrementNotNumber()
	{
		static::$store->set("phpunittestkey", "phpunitvalue");
		$this->assertSame(false, static::$store->inc("phpunittestkey"));
	}


	public function testDecrementWithInitialSet()
	{
		static::$store->set("phpunittestkey", 10);
		$this->assertSame(10, static::$store->get("phpunittestkey"));
		// check return falue is increased one
		$this->assertSame(9, static::$store->dec("phpunittestkey"));
		$this->assertSame(9, static::$store->get("phpunittestkey"));
	}

	public function testDecrementWithNoInitialSet()
	{
		$this->assertSame(-1, static::$store->dec("phpunittestkey"));
		static::$store->delete("phpunittestkey");
		// initial value
		$this->assertSame(6, static::$store->dec("phpunittestkey", 1, 7));
	}

	public function testDecrementWithCustomOffset()
	{
		$this->assertSame(-2, static::$store->dec("phpunittestkey", 2));
		$this->assertSame(-2, static::$store->get("phpunittestkey"));
		$this->assertSame(-5, static::$store->dec("phpunittestkey", 3));
		$this->assertSame(-9, static::$store->dec("phpunittestkey", 4));
	}

	public function testDecrementWithCustomOffsetAndInitialValue()
	{
		$this->assertSame(8, static::$store->dec("phpunittestkey", 2, 10));
		$this->assertSame(8, static::$store->get("phpunittestkey"));
		$this->assertSame(5, static::$store->dec("phpunittestkey", 3, 11));
		$this->assertSame(1, static::$store->dec("phpunittestkey", 4, 12));
	}

	public function testDecrementNotNumber()
	{
		static::$store->set("phpunittestkey", "phpunitvalue");
		$this->assertSame(false, static::$store->dec("phpunittestkey"));
	}
}
