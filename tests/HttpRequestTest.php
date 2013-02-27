<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\HttpRequest;

class HttpRequestTest extends PHPUnit_Framework_TestCase
{
	public function testCustomCreation()
	{
		$req = HttpRequest::custom("http://example.com/path/to/file.php?arg1=one&arg2=two");
		// test $server, $query, $post and $cookies are Sugi\Container
		$this->assertInstanceOf("Sugi\Container", $req->server);
		$this->assertInstanceOf("Sugi\Container", $req->query);
		$this->assertInstanceOf("Sugi\Container", $req->post);
		$this->assertInstanceOf("Sugi\Container", $req->cookie);
		// default request method is GET
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("GET", $req->method());
		// test Container->get
		$this->assertEquals($req->server->get("REQUEST_METHOD"), $req->server["REQUEST_METHOD"]);
		// domain
		$this->assertEquals("example.com", $req->server["HTTP_HOST"]);
		// host()
		$this->assertEquals("example.com", $req->host());
		// scheme
		$this->assertEquals("http", $req->scheme());
		// port
		$this->assertEquals(80, $req->server["SERVER_PORT"]);
		// https is off (passing "off" as a default)
		$this->assertEquals("off", $req->server->get("HTTPS", "off"));
		// base()
		$this->assertEquals("http://example.com", $req->base());
		// PATH_INFO
		$this->assertEquals("/path/to/file.php", $req->server->get("PATH_INFO"));
		// path()
		$this->assertEquals("/path/to/file.php", $req->path());
		// current()
		$this->assertEquals("http://example.com/path/to/file.php", $req->current());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		$this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->query->all());
		// arg1
		$this->assertEquals("one", $req->query->get("arg1"));
		// arg2
		$this->assertEquals("two", $req->query->get("arg2", "second"));
		// arg3
		$this->assertSame(null, $req->query->get("arg3"));
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=one&arg2=two", $req->server["REQUEST_URI"]);
		// address()
		$this->assertEquals("http://example.com/path/to/file.php?arg1=one&arg2=two", $req->address());
		// ajax()
		$this->assertSame(false, $req->ajax());
		// cli()
		$this->assertSame(true, $req->cli());
		// REMOTE_ADDR
		$this->assertEquals("127.0.0.1", $req->server["REMOTE_ADDR"]);
		// ip()
		$this->assertEquals("127.0.0.1", $req->ip());
	}

	public function testCustomHttpsCreation()
	{
		$req = HttpRequest::custom("https://example.com/path/to/file.php?arg1=one&arg2=two");
		// scheme
		$this->assertEquals("https", $req->scheme());
		// port
		$this->assertEquals(443, $req->server["SERVER_PORT"]);
		// https is on (passing "off" as a default)
		$this->assertEquals("on", $req->server->get("HTTPS", "off"));
		// base()
		$this->assertEquals("https://example.com", $req->base());
	}

	public function testCustomPortUserPass()
	{
		$req = HttpRequest::custom("http://user1:pass1@example.com:8080/path/to/file.php?arg1=one&arg2=two");
		// scheme()
		$this->assertEquals("http", $req->scheme());
		// SERVER_PORT
		$this->assertEquals(8080, $req->server["SERVER_PORT"]);
		// https is off (passing "off" as a default)
		$this->assertEquals("off", $req->server->get("HTTPS", "off"));
		// user
		$this->assertEquals("user1", $req->server->get("PHP_AUTH_USER"));
		// pass
		$this->assertEquals("pass1", $req->server->get("PHP_AUTH_PW"));
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=one&arg2=two", $req->server["REQUEST_URI"]);
		// address()
		$this->assertEquals("http://example.com/path/to/file.php?arg1=one&arg2=two", $req->address());
		// HTTP_HOST
		$this->assertEquals("example.com", $req->server["HTTP_HOST"]);
		// host()
		$this->assertEquals("example.com", $req->host());
	}

	public function testMoreGetParams()
	{
		$req = HttpRequest::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "get", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("GET", $req->method());
		// QUERY_STRING
		$this->assertEquals("arg1=edno&arg2=two&foo=bar", $req->server["QUERY_STRING"]);
		// queue() arguments
		$this->assertEquals("arg1=edno&arg2=two&foo=bar", $req->queue());		
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=edno&arg2=two&foo=bar", $req->server["REQUEST_URI"]);
		// address()
		$this->assertEquals("http://example.com/path/to/file.php?arg1=edno&arg2=two&foo=bar", $req->address());
	}

	public function testPostParams()
	{
		$req = HttpRequest::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "post", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("POST", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("POST", $req->method());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		$this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->post->all());
		// GET arg1
		$this->assertEquals("one", $req->query->get("arg1"));
		// GET arg2
		$this->assertEquals("two", $req->query->get("arg2"));
		// GET foo
		$this->assertSame(null, $req->query->get("foo"));
		// POST arg1
		$this->assertEquals("edno", $req->post->get("arg1"));
		// POST arg2
		$this->assertSame(null, $req->post->get("arg2"));
		// POST foo
		$this->assertEquals("bar", $req->post->get("foo"));
	}

	public function testPostParamsWithCustomMethod()
	{
		$req = HttpRequest::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "DELETE", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("DELETE", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("DELETE", $req->method());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		$this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->post->all());
		// GET arg1
		$this->assertEquals("one", $req->query->get("arg1"));
		// GET arg2
		$this->assertEquals("two", $req->query->get("arg2"));
		// GET foo
		$this->assertSame(null, $req->query->get("foo"));
		// POST arg1
		$this->assertEquals("edno", $req->post->get("arg1"));
		// POST arg2
		$this->assertSame(null, $req->post->get("arg2"));
		// POST foo
		$this->assertEquals("bar", $req->post->get("foo"));
	}

	public function testCookies()
	{
		$req = HttpRequest::custom("", "GET", array(), array("cookiename" => "cookievalue", "foo" => "bar"));
		// cookies array
		$this->assertInternalType("array", $req->cookie->all());
		// cookie
		$this->assertEquals("cookievalue", $req->cookie->get("cookiename"));
		// cookie2
		$this->assertEquals("bar", $req->cookie->get("foo", "foobar"));
		// notexisting cookie
		$this->assertSame(null, $req->cookie->get("notexisting"));
		// defualt value
		$this->assertEquals("default", $req->cookie->get("notexistsing", "default"));
	}
}
