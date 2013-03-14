<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cache;

class CacheTest extends PHPUnit_Framework_TestCase
{
	public function testMemcached()
	{
		Cache::configure(array("store" => "memcached"));

		Cache::delete("sugiphpunittest");
		$this->assertNull(Cache::get("sugiphpunittest"));
		$this->assertTrue(Cache::set("sugiphpunittest", "value"));
		$this->assertSame("value", Cache::get("sugiphpunittest"));
	}

	public function testApc()
	{
		Cache::configure(array("store" => "apc"));

		Cache::delete("sugiphpunittest");
		$this->assertNull(Cache::get("sugiphpunittest"));
		$this->assertTrue(Cache::set("sugiphpunittest", "value"));
		$this->assertSame("value", Cache::get("sugiphpunittest"));
	}
}
