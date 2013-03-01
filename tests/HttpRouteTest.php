<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\HttpRoute;

class HttpRouteTest extends PHPUnit_Framework_TestCase
{
	public function testSetCreateWithPath()
	{
		$route = new HttpRoute("/");
		$this->assertEquals("/", $route->getPath());
		$route = new HttpRoute("");
		$this->assertEquals("/", $route->getPath());
		$route = new HttpRoute(null);
		$this->assertEquals("/", $route->getPath());
		$route = new HttpRoute(false);
		$this->assertEquals("/", $route->getPath());
	}

	// public function testCreateWithMoreParams()
	// {
	// 	$route = new HttpRoute("/", "");
	// 	$this->assertEquals("/", $route->getPath());
	// 	$this->assertEquals("", $route->getHost());
	// 	$route = new HttpRoute("/", "example.com");
	// 	$this->assertEquals("/", $route->getPath());
	// 	$this->assertEquals("example.com", $route->getHost());
	// 	$route = new HttpRoute("/", "", "GET");
	// 	$this->assertEquals("GET", $route->getMethod());
	// 	$route = new HttpRoute("/", "", "GET|post");
	// 	$this->assertEquals("GET|POST", $route->getMethod());
	// }

	public function testPath()
	{
		$route = new HttpRoute("/home");
		$this->assertEquals("/home", $route->getPath());
		// TODO:
		// $route = new HttpRoute("/home?page=2");
		// $this->assertEquals("/home", $route->getPath());
		// $route = new HttpRoute("/?page=2");
		// $this->assertEquals("/", $route->getPath());
		// $route = new HttpRoute("?page=2");
		// $this->assertEquals("/", $route->getPath());
	}

	public function testSetPath()
	{
		$route = new HttpRoute("/");
		$route->setPath("/home");
		$this->assertEquals("/home", $route->getPath());
	}

	public function testHost()
	{
		$route = new HttpRoute("/");
		$this->assertEquals("", $route->getHost());
		$route->setHost("example.com");
		$this->assertEquals("example.com", $route->getHost());
		$route->setHost(null);
		$this->assertEquals("", $route->getHost());
		// TODO
		// $route->setHost("http://example.com/users/list?page=1");
		// $this->assertEquals("example.com", $route->getHost());
		$route->setHost("sub.example.com");
		$this->assertEquals("sub.example.com", $route->getHost());
	}

	public function testScheme()
	{
		$route = new HttpRoute("/");
		$this->assertEquals("", $route->getScheme());
		$route->setScheme("http");
		$this->assertEquals("http", $route->getScheme());
		$route->setScheme(null);
		$this->assertEquals("", $route->getScheme());
		$route->setScheme("https");
		$this->assertEquals("https", $route->getScheme());
		$route->setScheme("");
		$this->assertEquals("", $route->getScheme());
	}

	public function testMethod()
	{
		$route = new HttpRoute("/");
		$this->assertEquals("", $route->getMethod());
		$route->setMethod("GET");
		$this->assertEquals("GET", $route->getMethod());
		$route->setMethod("post");
		$this->assertEquals("POST", $route->getMethod());
		$route->setMethod(null);
		$this->assertEquals("", $route->getMethod());
		$route->setMethod("GET|POST|PUT");
		$this->assertEquals("GET|POST|PUT", $route->getMethod());
		$route->setMethod("");
		$this->assertEquals("", $route->getMethod());
	}
}
