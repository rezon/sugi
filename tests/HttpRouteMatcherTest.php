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
		$route = new HttpRoute("");
		$this->assertTrue($route->match($request));
		$route = new HttpRoute("/");
		$this->assertTrue($route->match($request));
		// TODO
		// $route = new HttpRoute("/?foo=bar"); // this is OK, since (?foo=bar) is not a path
		// $this->assertTrue($route->match($request));
		// $route = new HttpRoute("/?foo=baz"); // this is OK, since ?foo=baz
		// $this->assertTrue($route->match($request));
	}
}
