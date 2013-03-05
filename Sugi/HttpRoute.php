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
	protected $requisites = array();

	public $variables = array();

	/**
	 * Constructor
	 * 
	 * @param string $path - the path pattern, usually with variables like "/{controller}/{action}/{id}"
	 * @param array  $defaults - default values for variables in the path or for the host
	 *                         array("id" => "", "action" => "index")
	 * @param array  $requisites - regular expression to match variables like array("id" => "\d+")
	 */
	public function __construct($path, array $defaults = array(), array $requisites = array())
	{
		$this->setPath($path);
		$this->setDefaults($defaults);
		$this->setRequisites($requisites);
	}

	/**
	 * Set expected path
	 * 
	 * @param string $path
	 * @return HttpRoute
	 */
	public function setPath($path)
	{
		$path = "/" . trim($path, "/");
		$this->path = $path;

		return $this;
	}

	/**
	 * Returns expected path (pattern)
	 * @return array
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Sets default values for variables in host or path (pattern)
	 * and thus making them optional
	 * 
	 * @param array $defaults
	 * @return HttpRoute
	 */
	public function setDefaults(array $defaults)
	{
		$this->defaults = $defaults;

		return $this;
	}

	public function getDefaults()
	{
		return $this->defaults;
	}

	public function getDefault($key)
	{
		return isset($this->defaults[$key]) ? $this->defaults[$key] : null;
	}

	public function addDefault($key, $value)
	{
		$this->defaults[$key] = $value;

		return $this;
	}

	public function hasDefault($key)
	{
		return key_exists($key, $this->defaults);
	}

	/**
	 * Sets requisites (regular expressions) for variables in host and path
	 * <code>
	 * array("lang" => "en|bg");
	 * </code>
	 * 
	 * @param array $requisites
	 * @return HttpRoute
	 */
	public function setRequisites(array $requisites)
	{
		$this->requisites = $requisites;

		return $this;
	}

	public function getRequisites()
	{
		return $this->requisites;
	}

	public function getRequisite($key)
	{
		return isset($this->requisites[$key]) ? $this->requisites[$key] : null;
	}

	public function addRequisite($key, $value)
	{
		$this->requisites[$key] = $value;

		return $this;
	}

	public function hasRequisite($key)
	{
		return key_exists($key, $this->requisites);
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
	 * 
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
		// setting default values as a variables
		$this->variables = $this->defaults;

		if (is_null($request)) {
			$request = HttpRequest::real();
		}

		if ($this->matchScheme($request->scheme()) === false) {
			return false;
		}

		if ($this->matchMethod($request->method()) === false) {
			return false;
		}

		if ($this->matchHost($request->host()) === false) {
			return false;
		}

		if ($this->matchPath($request->path()) === false) {
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
		if (!$this->host) {
			return true;
		}

		if (preg_match($this->compile($this->host, "host"), $host, $matches)) {
			// add matches in array to know variables in host name
			foreach ($matches as $var => $value) {
				if (!is_int($var) and $value) {
					$this->variables[$var] = $value;
				}
			}
			return true;
		}

		return false;
	}

	protected function matchPath($path)
	{
		if (!$this->path) {
			return true;
		}

		$regEx = $this->compile($this->path, "path");
		if (preg_match($regEx, $path, $matches)) {
			// add matches in array to know variables in path name
			foreach ($matches as $var => $value) {
				if (!is_int($var) and $value) {
					$this->variables[$var] = $value;
				}
			}
			return true;
		}

		return false;
	}

	/**
	 * Create regular expression for the host or for the path
	 * 
	 * @param  string $pattern
	 * @param  string $style - "host" or "path"
	 * @return string
	 */
	protected function compile($pattern, $style)
	{
		$regex = $pattern;
		// $regex = preg_replace('#[.\\+?[^\\]$()<>=!]#', '\\\\$0', $regex);

		if ($style === "host") {
			$delimiter = ".";
			$defaultRequisites = "[^.,;?<>]+";
		} elseif ($style === "path") {
			$delimiter = "/";
			$defaultRequisites = "[^/,;?<>]+";
		} else {
			throw new \Exception("Unknown style $style");
		}

		preg_match_all("#\{(\w+)\}#", $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach ($matches as $match) {
			$variable = $match[1][0];
			$varPattern = $match[0][0]; // {variable}
			$varPos = $match[0][1];
			$capture = Filter::key($variable, $this->requisites, $defaultRequisites);
			$nextChar = (isset($pattern[$varPos + strlen($varPattern)])) ? $pattern[$varPos + strlen($varPattern)] : "";
			$prevChar = ($varPos > 0) ? $pattern[$varPos - 1] : "";

			if ($this->hasDefault($variable)) {
				// Make variables that have default values optional
				// Also make delimiter (if next char is a delimiter) to be also optional
				if ($style == "host" and $nextChar == $delimiter and ($prevChar == "" or $prevChar == $delimiter)) {
					$regex = preg_replace("#".$varPattern.$delimiter."#", "((?P<".$variable.">".$capture.")".$delimiter.")?", $regex);
				} elseif ($style == "path" and (($prevChar == $delimiter and $nextChar == $delimiter) or ($prevChar == $delimiter and $nextChar == "" and $varPos > 1))) {
					$regex = preg_replace("#".$delimiter.$varPattern."#", "(".$delimiter."(?P<".$variable.">".$capture."))?", $regex);
				} else {
					$regex = preg_replace("#".$varPattern."#", "((?P<".$variable.">".$capture."))?", $regex);
				}
			} else {
				$regex = preg_replace("#".$varPattern."#", "(?P<".$variable.">".$capture.")", $regex);
			}

			$this->variables[$variable] = $this->getDefault($variable);
		}
		
		if ($style == "host") {
			$regex = str_replace(".", "\.", $regex);
		} else {
			$regex = "/?".$regex;
		}

		return "#^".$regex.'$#siuD';
	}
}
