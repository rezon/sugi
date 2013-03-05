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

		// TODO ???
		// $route = new HttpRoute("/?foo=bar");
		// $this->assertTrue($route->match($request));
		// $route = new HttpRoute("/?foo=baz");
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


	public function testPathVariables()
	{
		$route = new HttpRoute("/path/to/{file}");
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file/")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/fi-le")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/fi_le")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file.html")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/something.php?get=param")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/path/to/file")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/path/to/file.html")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/path/to/something.php?get=param")));
		// false
		$this->assertFalse($route->match(HttpRequest::custom("/path/to")));
		$this->assertFalse($route->match(HttpRequest::custom("/path/to/")));
		$this->assertFalse($route->match(HttpRequest::custom("/path//file")));
		$this->assertFalse($route->match(HttpRequest::custom("/wrong/to/file")));
		$this->assertFalse($route->match(HttpRequest::custom("/wrong/path/to/file")));
		$this->assertFalse($route->match(HttpRequest::custom("/wrong/path/to/file/")));
		$this->assertFalse($route->match(HttpRequest::custom("/path/to/file.html/foo")));
		$this->assertFalse($route->match(HttpRequest::custom("/path/to/file/foo")));
	}

	public function testPathVariablesWithDefault()
	{
		$route = new HttpRoute("/path/to/{file}", array("file" => "index"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/index.php")));

		$route = new HttpRoute("/path/to/{file}", array("file" => ""));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/index.php")));
	}

	public function testPathVariablesAndDot()
	{
		$route = new HttpRoute("/path/to/file.{ext}", array("ext" => ""));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/path/to/file."))); // ???
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/path/to/file"))); // ???
	}

	public function testPathVariablesWithRequirements()
	{
		$route = new HttpRoute("/{lang}/index.php", array(), array("lang" => "en|bg"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/en/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/index.php")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/ru/index.php")));
		$this->assertFalse($route->match(HttpRequest::custom("/index.php")));

		// same as above with default
		$route = new HttpRoute("/{lang}/index.php", array("lang" => "en"), array("lang" => "en|bg"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/en/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/index.php")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/ru/index.php")));

		// more simple
		$route = new HttpRoute("/{lang}", array(), array("lang" => "en|bg"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/en")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/ru")));
		$this->assertFalse($route->match(HttpRequest::custom("/ru/index")));
		$this->assertFalse($route->match(HttpRequest::custom("/")));
		$this->assertFalse($route->match(HttpRequest::custom("")));

		// with default
		$route = new HttpRoute("/{lang}", array("lang" => "en"), array("lang" => "en|bg"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("/en")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/")));
		$this->assertTrue($route->match(HttpRequest::custom("/")));
		$this->assertTrue($route->match(HttpRequest::custom("")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/ru")));
		$this->assertFalse($route->match(HttpRequest::custom("/ru/index")));
	}

	public function testMVC()
	{
		// this is real world example
		$route = new HttpRoute("/{lang}/{controller}/{action}/{id}",
			array("lang" => "en", "controller" => "home", "action" => "index", "id" => "", "myvar" => "myvalue"),
			array("lang" => "bg|en", "id" => "\d*"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("")));
		$this->assertTrue($route->match(HttpRequest::custom("/")));
		$this->assertTrue($route->match(HttpRequest::custom("/en")));
		$this->assertTrue($route->match(HttpRequest::custom("/en/")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/home")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/index.php")));
		$this->assertTrue($route->match(HttpRequest::custom("/home/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/home/index")));
		$this->assertTrue($route->match(HttpRequest::custom("/bg/user/edit/3")));
		// note that lang parameter here is actually taking place in controller variable
		$this->assertTrue($route->match(HttpRequest::custom("/ru")));
		// var_dump($route->variables); // {'lang' => "en", 'controller' => "ru", ...}

		// fails
		$this->assertFalse($route->match(HttpRequest::custom("/bg/user/edit/3a")));
		$this->assertFalse($route->match(HttpRequest::custom("/user/edit/3a")));
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
		$request = HttpRequest::custom("http://www.example.com/");
		$route = new HttpRoute("/");
		// ok
		$this->assertTrue($route->match($request)); // no required host set

		$route->setHost("www.example.com");
		$this->assertTrue($route->match($request));
		
		$route->setHost("{subdomain}.example.com");
		$this->assertTrue($route->match($request));
		
		// fail		
		$route->setHost("example.com");
		$this->assertFalse($route->match($request));
		
		$route->setHost("www2.example.com");
		$this->assertFalse($route->match($request));
	}

	public function testHostWithIndex()
	{
		$route = new HttpRoute("/");
		$route->setHost("www{serverid}.example.com");
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://www2.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://wwwtwo.example.com/")));
		// not good
		$this->assertFalse($route->match(HttpRequest::custom("http://www.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://foo.www2.example.com/")));
	}

	public function testTLD()
	{
		$request = HttpRequest::custom("http://example.com/");
		$route = new HttpRoute("/");
		// ok
		$route->setHost("example.{tld}");
		$this->assertTrue($route->match($request));
		
		$route->setHost("example.com");
		$this->assertTrue($route->match($request));
		
		// fail		
		$route->setHost("example.eu");
		$this->assertFalse($route->match($request));
		
		$route->setHost("www.example.com");
		$this->assertFalse($route->match($request));
	}

	public function testSubAndTLD()
	{
		$request = HttpRequest::custom("http://www.example.com/");
		$route = new HttpRoute("/");
		// ok
		$route->setHost("{subdomain}.example.{tld}");
		$this->assertTrue($route->match($request));
		
		// fail		
		$route->setHost("example.com");
		$this->assertFalse($route->match($request));
		
		$route->setHost("www.example.eu");
		$this->assertFalse($route->match($request));
	}

	public function testHostWithDefaultParam()
	{
		$route = new HttpRoute("/");
		$route->setHost("{subdomain}.example.com");
		$route->setDefaults(array("subdomain" => "www"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://www.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://test.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("http://wwwwexample.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://www.sub.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://sub.www.example.com/")));
	}

	public function testHostTLDwithDefaultParam()
	{
		$route = new HttpRoute("/");
		$route->setHost("example.{tld}");
		$route->setDefaults(array("tld" => "com"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.info/")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("http://example/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example./")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example2/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example2com/")));
	}

	public function testHostSubSubDomains()
	{
		$route = new HttpRoute("/");
		$route->setHost("{subdomain}.en.example.com");
		$route->setDefaults(array("subdomain" => "www"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://www.en.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://test.en.example.com/")));
		// !!! Note - this and next "!!! Note" are giving same results! This should be avoided by a developers
		$this->assertTrue($route->match(HttpRequest::custom("http://en.example.com/"))); 

		$route = new HttpRoute("/");
		$route->setHost("en.{subdomain}.example.com");
		$route->setDefaults(array("subdomain" => "www"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://en.www.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://en.test.example.com/")));
		// !!! Note
		$this->assertTrue($route->match(HttpRequest::custom("http://en.example.com/")));

		// NOTE in this situation developer should set proper requirements for at least one of the variables
		$route = new HttpRoute("/");
		$route->setHost("{lang}.{subdomain}.example.com");
		$route->setDefaults(array("subdomain" => "www", "lang" => "en"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://en.www.example.com/")));
		// !!! Note
		$this->assertTrue($route->match(HttpRequest::custom("http://en.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://www.example.com/")));
	}

	public function testHostSubSubDefaultAndEmtpyDefault()
	{
		$route = new HttpRoute("/");
		$route->setHost("www{id}.example.com");
		$route->setDefaults(array("id" => "1"));

		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://www1.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://www22.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://wwww.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://www.example.com/")));
		// fail
		$this->assertFalse($route->match(HttpRequest::custom("http://ww.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://wwwexample.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example.com/")));

		// empty default
		$route->setDefaults(array("id" => ""));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://www1.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://www22.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://wwww.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://www.example.com/")));
		// fail
		$this->assertFalse($route->match(HttpRequest::custom("http://ww.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://wwwexample.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://example.com/")));
	}

	public function testHostVariablesWithRequisites()
	{
		$route = new HttpRoute("/");
		$route->setHost("{lang}.example.com");
		$route->setRequisites(array("lang" => "en|bg"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://en.example.com/")));
		$this->assertTrue($route->match(HttpRequest::custom("http://bg.example.com/")));
		// fail
		$this->assertFalse($route->match(HttpRequest::custom("http://example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://ru.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://ru.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://www.en.example.com/")));
		$this->assertFalse($route->match(HttpRequest::custom("http://english.example.com/")));
		// with default value
		$route->setDefaults(array("lang" => "en"));
		// ok
		$this->assertTrue($route->match(HttpRequest::custom("http://example.com/")));
		// fails
		$this->assertFalse($route->match(HttpRequest::custom("http://ru.example.com/")));
	}
}
