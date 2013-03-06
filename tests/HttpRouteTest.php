<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\HTTP\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
	public function testSetCreateWithPath()
	{
		$route = new Route("/");
		$this->assertEquals("/", $route->getPath());
		$route = new Route("");
		$this->assertEquals("/", $route->getPath());
		$route = new Route(null);
		$this->assertEquals("/", $route->getPath());
		$route = new Route(false);
		$this->assertEquals("/", $route->getPath());
	}

	public function testSetMethodsReturnSelf()
	{
		$route = new Route("/");
		$this->assertInstanceOf("\Sugi\HTTP\Route", $route->setPath("/"));
		$this->assertInstanceOf("\Sugi\HTTP\Route", $route->setHost("example.com"));
		$this->assertInstanceOf("\Sugi\HTTP\Route", $route->setDefaults(array()));
		$this->assertInstanceOf("\Sugi\HTTP\Route", $route->setMethod("get"));
		$this->assertInstanceOf("\Sugi\HTTP\Route", $route->setScheme("http"));
	}

	public function testPath()
	{
		$route = new Route("/home");
		$this->assertEquals("/home", $route->getPath());
		// TODO: this should throw an exception
		// $route = new Route("/home?page=2");
		// $this->assertEquals("/home", $route->getPath());
		// $route = new Route("/?page=2");
		// $this->assertEquals("/", $route->getPath());
		// $route = new Route("?page=2");
		// $this->assertEquals("/", $route->getPath());
	}

	public function testSegmentsInPath()
	{
		$route = new Route("admin/{controller}/{method}/{id}");
	}

	public function testSetPath()
	{
		$route = new Route("/");
		$route->setPath("/home");
		$this->assertEquals("/home", $route->getPath());
	}

	public function testDefaults()
	{
		$route = new Route("/");
		$route->setDefaults(array("constructor" => "main"));
		$this->assertEquals(array("constructor" => "main"), $route->getDefaults());
		$route->setDefaults(array("constructor" => "main", "action" => "index"));
		$this->assertEquals(array("constructor" => "main", "action" => "index"), $route->getDefaults());

		$route = new Route("/", array("constructor" => "main"));
		$this->assertEquals(array("constructor" => "main"), $route->getDefaults());
	}

	public function testHost()
	{
		$route = new Route("/");
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
		$route = new Route("/");
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
		$route = new Route("/");
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
