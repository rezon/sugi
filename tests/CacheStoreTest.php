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


class CacheStoreTest extends PHPUnit_Framework_TestCase
{	
	public static $store;

	public static function setUpBeforeClass()
	{
		// $storeInterface = new ApcStore();
		$storeInterface = MemcacheStore::factory(array());

		static::$store = new Store($storeInterface);
	}

	public function setUp()
	{
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

}
