<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Route is a set of rules used for routing.
 * Main rule is a path path, but there is more:
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
	protected $defaults = array();
	protected $segments = array();

	public function __construct($path, array $defaults = array())
	{
		$this->setPath($path);
		$this->setDefaults($defaults);
	}

	/**
	 * Set expected path
	 * @param string $path
	 * @return HttpRoute
	 */
	public function setPath($path)
	{
		$path = "/" . trim($path, "/");
		$this->path = $path;

		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setDefaults(array $defaults)
	{
		$this->defaults = $defaults;

		return $this;
	}

	public function getDefaults()
	{
		return $this->defaults;
	}

	public function setHost($host)
	{
		$this->host = $host;

		return $this;
	}

	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Set request methods for which the Route should work.
	 * @param string $method - empty matches any method
	 * @return HttpRoute
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);

		return $this;
	}

	/**
	 * Get expected request method
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Expected HTTP scheme: "http" or "https"
	 * @param string|null $scheme - null means all
	 * @return HttpRoute
	 */
	public function setScheme($scheme)
	{
		$this->scheme = strtolower($scheme);

		return $this;
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Match defined route rules against the request
	 * @param HttpRequest $request
	 * @return boolean - true if the request match defined route
	 */
	public function match(HttpRequest $request = null)
	{
		$result = array();

		if (is_null($request)) {
			$request = HttpRequest::real();
		}

		if ($this->matchScheme($request->scheme()) === false) {
			return false;
		}

		if ($this->matchMethod($request->method()) === false) {
			return false;
		}

		$hostVars = $this->matchHost($request->host());
		if ($hostVars === false) {
			return false;
		}

		$pathVars = $this->matchPath($request->path());
		if ($pathVars === false) {
			return false;
		}

		return true;
	}

	protected function matchScheme($scheme)
	{
		return (!$this->scheme or $this->scheme == $scheme);
	}

	protected function matchMethod($method)
	{
		return (!$this->method or preg_match("#" . str_replace("#", "\\#", $this->method)."#", $method));
	}

	protected function matchHost($host)
	{
		$vars = array();

		if (!$this->host) {
			return $vars;
		}

		if (preg_match($this->compile($this->host), $host, $matches)) {
			
			// var_dump($matches);
			// add matches in array to know variables in host name
			foreach ($matches as $var => $value) {
				if (!is_int($var)) {
					$vars[$var] = $value;
				}
			}
			return $vars;
		}

		return false;
	}

	protected function matchPath($path)
	{
		$vars = array();

		if ($this->path == $path) {
			return $vars;
		}

		return false;
	}
	
	/**
	 * Compile regular expression for the route
	 */
	protected function compile($pattern)
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for: {}
		$regex = preg_replace('#[.\\+?[^\\]$()<>=!]#', '\\\\$0', $pattern);
		
		// Transform segments and segment patterns
		// $this is not available in Closures before PHP 5.4, so we'll use function instead of Closure
		$regex = preg_replace_callback('#{([a-zA-Z0-9_]++)}#uD', array($this, 'regex_key_prc'), $regex); 

		return '#^'.$regex.'$#siuD';
	}

	protected function regex_key_prc($match)
	{
		// Replace matches with segment rules or what {segment} accepts by default
		return "(?P<{$match[1]}>" . Filter::key($match[1], $this->segments, "[^/.,;?<>]++") . ')';
	}

}
