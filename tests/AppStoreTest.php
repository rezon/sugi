<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cache\ApcStore;

class AppStoreTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if (!ini_get("apc.enable_cli")) {
			$this->markTestSkipped("apc.enable_cli is disabled");
		}
		apc_delete("phpunittestkey");
	}

	public function testCheckInstance()
	{
		$store = new ApcStore();
		$this->assertInstanceOf("\Sugi\Cache\StoreInterface", $store);
	}

	public function testReturnsNullWhenNotFound()
	{
		$store = new ApcStore();
		$this->assertSame(null, $store->get("phpunittestkey"));
	}

	public function testHasReturnsFalseIfNotFound()
	{
		$store = new ApcStore();
		$this->assertSame(false, $store->has("phpunittestkey"));
	}

	public function testDeleteReturnsFalseIfNotFound()
	{
		$store = new ApcStore();
		$this->assertSame(false, $store->delete("phpunittestkey"));
	}

	public function testSet()
	{
		$store = new ApcStore();
		$this->assertSame(true, $store->set("phpunittestkey", "phpunittestvalue"));
	}

	public function testGet()
	{
		$store = new ApcStore();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertEquals("phpunittestvalue", $store->get("phpunittestkey"));
		$this->assertEquals("phpunittestvalue", $store->get("phpunittestkey", "default"));
	}

	public function testTTL()
	{
		$store = new ApcStore();
		$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertEquals(null, $store->get("phpunittestkey"));
	}

	public function testHas()
	{
		$store = new ApcStore();
		$store->set("phpunittestkey", "phpunittestvalue", -1);
		$this->assertSame(false, $store->has("phpunittestkey"));
		$store->set("phpunittestkey", "phpunittestvalue", 1);
		$this->assertSame(true, $store->has("phpunittestkey"));
	}

	public function testDeleteReturnsTrue()
	{
		$store = new ApcStore();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, $store->has("phpunittestkey"));
		$this->assertSame(true, $store->delete("phpunittestkey"));
		$this->assertSame(false, $store->has("phpunittestkey"));
	}

	public function testFlush()
	{
		$store = new ApcStore();
		$store->set("phpunittestkey", "phpunittestvalue");
		$this->assertSame(true, $store->has("phpunittestkey"));
		$this->assertSame(true, $store->flush());
		$this->assertSame(false, $store->has("phpunittestkey"));
	}
}
