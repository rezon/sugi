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

	public function testSetMethodsReturnSelf()
	{
		$route = new HttpRoute("/");
		$this->assertInstanceOf("\Sugi\HttpRoute", $route->setPath("/"));
		$this->assertInstanceOf("\Sugi\HttpRoute", $route->setHost("example.com"));
		$this->assertInstanceOf("\Sugi\HttpRoute", $route->setDefaults(array()));
		$this->assertInstanceOf("\Sugi\HttpRoute", $route->setMethod("get"));
		$this->assertInstanceOf("\Sugi\HttpRoute", $route->setScheme("http"));
	}

	public function testPath()
	{
		$route = new HttpRoute("/home");
		$this->assertEquals("/home", $route->getPath());
		// TODO: this should throw an exception
		// $route = new HttpRoute("/home?page=2");
		// $this->assertEquals("/home", $route->getPath());
		// $route = new HttpRoute("/?page=2");
		// $this->assertEquals("/", $route->getPath());
		// $route = new HttpRoute("?page=2");
		// $this->assertEquals("/", $route->getPath());
	}

	public function testSegmentsInPath()
	{
		$route = new HttpRoute("admin/{controller}/{method}/{id}");
	}

	public function testSetPath()
	{
		$route = new HttpRoute("/");
		$route->setPath("/home");
		$this->assertEquals("/home", $route->getPath());
	}

	public function testDefaults()
	{
		$route = new HttpRoute("/");
		$route->setDefaults(array("constructor" => "main"));
		$this->assertEquals(array("constructor" => "main"), $route->getDefaults());
		$route->setDefaults(array("constructor" => "main", "action" => "index"));
		$this->assertEquals(array("constructor" => "main", "action" => "index"), $route->getDefaults());

		$route = new HttpRoute("/", array("constructor" => "main"));
		$this->assertEquals(array("constructor" => "main"), $route->getDefaults());
	}

	public function testHost()
	{
		$route = new HttpRoute("/");
		$this->assertEquals("", $route->getHost());
		$route->setHost("example.com");
		$this->assertEquals("example.com", $route->getHost());
		$route->setHost(null);
		$this->assertEquals("", $route->getHost());
		// TODO: this should throw an exception
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
