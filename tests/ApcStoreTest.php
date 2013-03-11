<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cache\ApcStore as Store;

class ApcStoreTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if (!ini_get("apc.enabled")) {
			$this->markTestSkipped("apc is not enabled");
		} elseif (!ini_get("apc.enable_cli")) {
			$this->markTestSkipped("apc.enable_cli is disabled");
		}
		apc_delete("phpunittestkey");
	}

	public function testCheckInstance()
	{
		$store = new Store();
		$this->assertInstanceOf("\Sugi\Cache\StoreInterface", $store);
	}

	public function testReturnsNullWhenNotFound()
	{
		$store = new Store();
		$this->assertSame(null, $store->get("phpunittestkey"));
	}

	public function testHasReturnsFalseIfNotFound()
	{
		$store = new Store();
		$this->assertSame(false, $store->has("phpunittestkey"));
	}

	public function testDeleteReturnsFalseIfNotFound()
	{
		$store = new Store();
		$this->assertSame(false, $store->delete("phpunittestkey"));
	}

	public function testSet()
	{
		$store = new Store();
		$this->assertSame(true, $store->set("phpunittestkey", "phpunittestvalue"));
	}

	public function testGet()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertEquals("phpunittestvalue", $store->get("phpunittestkey"));
		$this->assertEquals("phpunittestvalue", $store->get("phpunittestkey", "default"));
	}

	public function testTTLMinus1()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertEquals(null, $store->get("phpunittestkey"));
	}

	public function testTTL()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue", 1);
		$this->assertSame(true, $store->has("phpunittestkey"));
		// This cannot be checked, since for some optimization reasons in APC it does not invalidate
		// data on same request. @see  https://bugs.php.net/bug.php?id=58084
		// sleep(2);
		// $this->assertSame(false, $store->has("phpunittestkey"));
	}

	public function testHas()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertSame(false, $store->has("phpunittestkey"));
		$store->set("phpunittestkey", "phpunittestvalue", 1);
		$this->assertSame(true, $store->has("phpunittestkey"));
	}

	public function testDeleteReturnsTrue()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, $store->has("phpunittestkey"));
		$this->assertSame(true, $store->delete("phpunittestkey"));
		$this->assertSame(false, $store->has("phpunittestkey"));
	}

	public function testFlush()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, $store->has("phpunittestkey"));
		$this->assertSame(true, $store->flush());
		$this->assertSame(false, $store->has("phpunittestkey"));
	}
/*
	NOTE: increment/decrements methods on both APC and Memcached have so many bugs, caveats and is so 
	complicated to work with that I decided not to work with build in functions directly!

	public function testIncrementWithInitialSet()
	{
		$store = new Store();
		$store->set("phpunittestkey", 1);
		$this->assertSame(1, $store->get("phpunittestkey"));
		// check return falue is increased one
		$this->assertSame(2, $store->inc("phpunittestkey"));
		$this->assertSame(2, $store->get("phpunittestkey"));
	}

	public function testIncrementWithNoInitialSet()
	{
		$store = new Store();
		$this->assertSame(0, $store->inc("phpunittestkey"));
		$store->delete("phpunittestkey");
		// initial value
		$this->assertSame(7, $store->inc("phpunittestkey", 1, 7));
	}

	public function testIncrementWithCustomOffset()
	{
		$store = new Store();
		$this->assertSame(0, $store->inc("phpunittestkey", 2));
		$this->assertSame(0, $store->get("phpunittestkey"));
		$this->assertSame(3, $store->inc("phpunittestkey", 3));
		$this->assertSame(7, $store->inc("phpunittestkey", 4));
	}

	public function testIncrementWithCustomOffsetAndInitialValue()
	{
		$store = new Store();
		$this->assertSame(10, $store->inc("phpunittestkey", 2, 10));
		$this->assertSame(10, $store->get("phpunittestkey"));
		$this->assertSame(13, $store->inc("phpunittestkey", 3, 11));
		$this->assertSame(17, $store->inc("phpunittestkey", 4, 12));
	}

	public function testIncrementNotNumber()
	{
		$store = new Store();
		$store->set("phpunittestkey", "phpunitvalue");
		// TODO: overrides it - maybe it's not perfect solution
		$this->assertSame(0, $store->inc("phpunittestkey"));
	}

*/
}
