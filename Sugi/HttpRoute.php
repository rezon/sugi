<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Route is a set of rules used for routing.
 * Main rule is a path, but there is more:
 *  - host (domains, subdomains)
 *  - scheme (http or https)
 *  - method (GET, POST, etc.)
 *  - ...
 * This class is intended to replace Route class
 */
class HttpRoute
{
	protected $path = "/";
	protected $host = ""; // empty means all
	protected $method = ""; // empty means all - GET, HEADER, POST, PUT, DELETE, ...
	protected $scheme = ""; // empty means all - http, https


	public function __construct($path)
	{
		$this->setPath($path);
		// $this->setHost($host);
		// $this->setMethod($method);
	}

	/**
	 * Set expected path
	 * @param string $path
	 */
	public function setPath($path)
	{
		$path = "/" . trim($path, "/");
		// if ($path != "/") {
		// 	$parts = parse_url($path);
		// 	if (!isset($parts["path"]) or $parts["path"] != $path) {
		// 		throw new \Exception("$path is not a valid path");
		// 	}
		// }
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setHost($host)
	{
		$this->host = $host;
	}

	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Set request methods for which the Route should work.
	 * @param string $method - empty matches any method
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
	}

	/**
	 * Get expected request method
	 * @return array
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Expected HTTP scheme: "http" or "https"
	 * @param string|null $scheme - null means all
	 */
	public function setScheme($scheme)
	{
		$this->scheme = strtolower($scheme);
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	public function match(HttpRequest $request = null)
	{
		if (is_null($request)) {
			$request = HttpRequest::real();
		}

		if (!$this->matchPath($request->path())) {
			return false;
		}

		if (!$this->matchHost($request->host())) {
			return false;
		}

		if (!$this->matchMethod($request->method())) {
			return false;
		}

		if (!$this->matchScheme($request->scheme())) {
			return false;
		}

		return true;
	}

	protected function matchPath($path)
	{
		return ($this->path == $path);
	}

	protected function matchMethod($method)
	{
		return (!$this->method or preg_match("#" . str_replace("#", "\\#", $this->method)."#", $method));
	}

	protected function matchScheme($scheme)
	{
		return (!$this->scheme or $this->scheme == $scheme);
	}

	protected function matchHost($host)
	{
		return (!$this->host or $this->host == $host);
	}
}
