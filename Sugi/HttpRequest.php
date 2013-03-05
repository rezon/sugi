<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

class HttpRequest
{
	/**
	 * HTTP SERVER parameter container, like $_SERVER
	 * @var \Sugi\Container
	 */
	public $server;

	/**
	 * HTTP GET parameters container, like $_GET
	 * @var \Sugi\Container
	 */
	public $query;

	/**
	 * HTTP POST parameters container, like $_POST
	 * @var \Sugi\Container
	 */
	public $post;


	/**
	 * Cookies container, like $_COOKIE
	 * @var \Sugi\Container
	 */
	public $cookie;

	/**
	 * it's protected for now
	 * instantiate it with static methods real() and custom()
	 */
	protected function __construct(array $server = array(), array $query = array(), array $post = array(), array $cookie = array())
	{
		$this->server = new Container($server);
		$this->query  = new Container($query);
		$this->post   = new Container($post);
		$this->cookie = new Container($cookie);
	}

	public static function real()
	{
		return new self($_SERVER, $_GET, $_POST, $_COOKIE);
	}

	public static function custom($uri, $method = "GET", array $params = array(), array $cookie = array())
	{
		$method = strtoupper($method);

		// default values
		$server = array(
			"HTTP_HOST"             => "localhost",
			"SERVER_PORT"           => 80,
			"REMOTE_ADDR"           => "127.0.0.1",
			"REQUEST_METHOD"        => $method,
			"QUERY_STRING"          => "",
			"REQUEST_URI"           => "/",
			"PATH_INFO"             => "/",
			//
			// "REDIRECT_STATUS" => 200,
			// "HTTP_ACCEPT"           => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			// "HTTP_ACCEPT_LANGUAGE"  => "en-us,en;q=0.5",
			// "HTTP_ACCEPT_CHARSET"   => "utf-8;q=1.0",
			// "HTTP_ACCEPT_ENCODING" => "gzip, deflate"
			// "HTTP_DNT" => 1, // Do Not Track
			// "HTTP_CONNECTION" => "keep-alive",
			// "HTTP_CACHE_CONTROL" => "max-age=0",
			// "PATH" => "/usr/local/bin:/usr/bin:/bin",
			// "SERVER_NAME"           => "localhost",
			// "SERVER_ADDR" => "127.0.0.1",
			// "SERVER_SIGNATURE" => "Apache/2.2.22",
			// "SERVER_SOFTWARE" => "Apache/2.2.22",
			// "DOCUMENT_ROOT" => "/path/to/http/doc/root", 
			// "SERVER_ADMIN" => "[no address given]",
			// "REMOTE_PORT" => 12345,
			// "REDIRECT_QUERY_STRING" => http_build_query($params, "", "&"),
			// "REDIRECT_URL" => "/path",
			// "GATEWAY_INTERFACE" => "CGI/1.1",
			// "SERVER_PROTOCOL" => "HTTP/1.1",
			// "SCRIPT_NAME"           => "/index.php", 
			// "SCRIPT_FILENAME" => "/path/to/http/doc/root/index.php",
			// "PHP_SELF" => "/index.php/path",
			// "REQUEST_TIME_FLOAT" => 1361878419.594,
			// "REQUEST_TIME" => 1361878419
		);
	
		// content
		if ($method !== "GET") {
			$server["CONTENT_TYPE"] = "application/x-www-form-urlencoded";
		}

		// scheme://user:pass@host:port/path/script?query=value#fragment
		$parts = parse_url($uri);

		// scheme
		if (isset($parts["scheme"])) {
			if ($parts["scheme"] === "https") {
				$server["SERVER_PORT"] = 443;
				$server["HTTPS"] = "on";
			} else {
				$server["SERVER_PORT"] = 80;
			}
		}

		// port
		if (isset($parts["port"])) {
			$server["SERVER_PORT"] = $parts["port"];
		}

		// user
		if (isset($parts["user"])) {
			$server["PHP_AUTH_USER"] = $parts["user"];
		}

		// pass
		if (isset($parts["pass"])) {
			$server["PHP_AUTH_PW"] = $parts["pass"];
		}

		// host
		if (isset($parts["host"])) {
			// $server["SERVER_NAME"] = $parts["host"];
			// Not sure we should trim, or simply throw an exception
			$server["HTTP_HOST"] = trim($parts["host"], ".");
		}

		// path
		if (isset($parts["path"])) {
			// path ALWAYS begin with a slash and has no trailing slash
			$path = "/" . trim($parts["path"], "/");
			$server["PATH_INFO"] = $path;
			$server["REQUEST_URI"] = $path;
		}

		// query
		if ($method === "GET" and isset($parts["query"])) {
			parse_str(html_entity_decode($parts["query"]), $partsQ);
			// replacing query part from $uri to those set in array $query
			$query = array_merge($partsQ, $params);
		} elseif ($method === "GET") {
			$query = $params;
		} elseif (isset($parts["query"])) {
			parse_str(html_entity_decode($parts["query"]), $query);
		} else {
			$query = array();
		}
		$queryString = http_build_query($query, "", "&");
		if ($queryString) {
			$server["QUERY_STRING"] = $queryString;
			$server["REQUEST_URI"] .= "?".$queryString;
		}

		// post
		$post = ($method !== "GET") ? $params : array();

		if ($cookie) {
			// HTTP_COOKIE string look like this: "cs=alabalacookie; ci=1"
			$server["HTTP_COOKIE"] = http_build_query($cookie, "", "; ");
		}

		return new self($server, $query, $post, $cookie);
	}

	/**
	 * Returns request method used
	 * @return string
	 */
	public function method()
	{
		return $this->server["REQUEST_METHOD"];
	}

	/**
	 * Returns scheme: "http" or "https"
	 * @return string
	 */
	public function scheme()
	{
		return (!empty($this->server["HTTPS"]) AND filter_var($this->server["HTTPS"], FILTER_VALIDATE_BOOLEAN)) ? "https" : "http";
	}

	/**
	 * Returns host name like "subdomain.example.com"
	 * @return string
	 */
	public function host()
	{
		return $this->server["HTTP_HOST"];
	}

	/**
	 * Returns request scheme://host
	 * @return string
	 */
	public function base()
	{
		return $this->scheme() . "://" .  $this->host();
	}

	/**
	 * Get the URI for the current request.
	 * @return string
	 */
	public function uri()
	{
		// determine URI from Request
		$uri = isset($this->server["REQUEST_URI"]) ? $this->server["REQUEST_URI"] : 
			(isset($this->server["PATH_INFO"]) ? $this->server["PATH_INFO"] : 
				(isset($this->server["PHP_SELF"]) ? $this->server["PHP_SELF"] : 
					(isset($this->server["REDIRECT_URL"]) ? $this->server["REDIRECT_URL"] : "")));
		
		// remove unnecessarily slashes, like doubles and leading
		$uri = preg_replace("|//+|", "/", $uri);
		$uri = ltrim($uri, "/");
		// remove get params
		if (strpos($uri, "?") !== false) {
			$e = explode("?", $uri, 2);
			$uri = $e[0];
		}
		// $uri = trim($uri, '/');
		// add / only on empty URI - not good, because this will not work: 
		// 		Route::uri('(<controller>(/<action>(/<param>*)))', function ($params) {
		// since we have no "/", this is OK, but it's more complicated:
		//		Route::uri('(/)(<controller>(/<action>(/<param>*)))', function ($params) {
		//
		// if (!$uri) $uri = '/';

		return $uri;
	}

	/**
	 * Get PATH_INFO
	 * @return string
	 */
	public function path()
	{
		return "/" . $this->uri();
	}

	/**
	 * Returns request (protocol+host+uri)
	 * @return string
	 */
	public function current()
	{
		return $this->base() . $this->path();
	}

	/**
	 * The part of the url which is after the ?
	 * @return string
	 */
	public function queue()
	{
		return http_build_query($this->query->all(), "", "&");
	}

	/**
	 * Returns request scheme://host/uri?queue
	 * 
	 * @return string
	 * @todo: maybe shold place user/pass and port (if not default)
	 */
	public function address()
	{
		if ($queue = $this->queue()) {
			$queue = "?" . $queue;
		}
		return $this->base().$this->path().$queue;
	}

	/**
	 * Is the request AJAX or not
	 * @return boolean
	 */
	public function ajax()
	{
		return (isset($this->server["HTTP_X_REQUESTED_WITH"]) AND (strtolower($this->server["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"));
	}

	/**
	 * Request from CLI
	 * TODO: this should be somehow changeable
	 * 
	 * @return boolean
	 */
	public function cli()
	{
		return (PHP_SAPI === "cli");
	}

	/**
	 * Client's IP
	 * @return string
	 */
	public function ip()
	{
		if ($this->cli()) return "127.0.0.1"; // The request was started from the command line
		if (isset($this->server["HTTP_X_FORWARDED_FOR"])) return $this->server["HTTP_X_FORWARDED_FOR"]; // If the server is behind proxy
		if (isset($this->server["HTTP_CLIENT_IP"])) return $this->server["HTTP_CLIENT_IP"];
		if (isset($this->server["REMOTE_ADDR"])) return $this->server["REMOTE_ADDR"];
		return "0.0.0.0";
	}

}
