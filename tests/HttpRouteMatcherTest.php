<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\HttpRoute;
use Sugi\HttpRequest;

class HttpRouteMatcherTest extends PHPUnit_Framework_TestCase
{
	public function testNoPath()
	{
		$request = HttpRequest::custom("");
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/test");
		$this->assertFalse($route->match($request));

		$request = HttpRequest::custom("/");
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/test");
		$this->assertFalse($route->match($request));

		$request = HttpRequest::custom("http://example.com");
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/test");
		$this->assertFalse($route->match($request));

		$request = HttpRequest::custom("http://example.com/");
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/test");
		$this->assertFalse($route->match($request));
	}

	public function testNoPathWithParams()
	{
		$request = HttpRequest::custom("http://example.com?foo=bar");
		// ok
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		// not
		$route = new HttpRoute("/test");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/example.com");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/example.com?foo=bar");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("http://example.com");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("http://example.com?foo=bar");
		$this->assertFalse($route->match($request));

		// TODO
		// $route = new HttpRoute("/?foo=bar"); // this is OK, since (?foo=bar) is not a path
		// $this->assertTrue($route->match($request));
		// $route = new HttpRoute("/?foo=baz"); // this is OK, since ?foo=baz
		// $this->assertTrue($route->match($request));
	}

	public function testSinglePath()
	{
		$request = HttpRequest::custom("/path");
		// ok
		$route = new HttpRoute("/path");
		$this->assertTrue($route->match($request));
		// ok, route adds leading slash
		$route = new HttpRoute("path");
		$this->assertTrue($route->match($request));
		// ok, route removes trailing slash
		$route = new HttpRoute("/path/");
		$this->assertTrue($route->match($request));
		// false
		$route = new HttpRoute("");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/path/more");
		$this->assertFalse($route->match($request));
	}

	public function testSinglePathTrailingSlash()
	{
		$request = HttpRequest::custom("/path/");
		// ok
		$route = new HttpRoute("/path");
		$this->assertTrue($route->match($request));
	}

	public function testSinglePathNoSlash()
	{
		$request = HttpRequest::custom("path");
		// ok
		$route = new HttpRoute("/path");
		$this->assertTrue($route->match($request));
	}

	public function testLongPath()
	{
		$request = HttpRequest::custom("/path/to/file.html");
		// ok
		$route = new HttpRoute("/path/to/file.html");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("path/to/file.html/");
		$this->assertTrue($route->match($request));
		// false
		$route = new HttpRoute("/");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/path");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/path/to");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/path/to/file");
		$this->assertFalse($route->match($request));
		$route = new HttpRoute("/path/to/file.");
		$this->assertFalse($route->match($request));
	}

	public function testHost()
	{
		$request = HttpRequest::custom("http://example.com");
		$route = new HttpRoute("/");
		// ok
		$route->setHost("example.com");
		$this->assertTrue($route->match($request));
		$route->setHost("");
		$this->assertTrue($route->match($request));

		// false
		$route->setHost("foo.bar");
		$this->assertFalse($route->match($request));
		$route->setHost("example");
		$this->assertFalse($route->match($request));
		$route->setHost("example.");
		$this->assertFalse($route->match($request));
		$route->setHost("example.net");
		$this->assertFalse($route->match($request));
		$route->setHost(".com");
		$this->assertFalse($route->match($request));
		// not a host
		$route->setHost("example.com/");
		$this->assertFalse($route->match($request));
		$route->setHost("example.com/path");
		$this->assertFalse($route->match($request));
		// this is wrong! we need only host, not the scheme
		$route->setHost("http://example.com/path");
		$this->assertFalse($route->match($request));
		$route->setHost("http://example.com");
		$this->assertFalse($route->match($request));
	}

	public function testHostWithSubdomain()
	{
		$request = HttpRequest::custom("http://example.com");
		$route = new HttpRoute("/");
		// any subdomain
		$route->setHost(".example.com");
		$this->assertTrue($route->match($request));

		$request = HttpRequest::custom("http://sub.example.com");
		// any subdomain
		$route->setHost(".example.com");
		$this->assertTrue($route->match($request));
		$route->setHost("sub.example.com");
		$this->assertTrue($route->match($request));
		// fail
		$route->setHost("sub2.example.com");
		$this->assertFalse($route->match($request));
	}
}