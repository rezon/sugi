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

		if (preg_match($this->compile($this->host, "."), $host, $matches)) {
			
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

	protected function compile($pattern, $delimiter)
	{
		$regex = $pattern;
		preg_match_all("#\{(\w+)\}#", $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach ($matches as $match) {
			$variable = $match[1][0];
			$varPattern = $match[0][0]; // {variable}
			$varPos = $match[0][1];
			$capture = Filter::key($variable, $this->requisites, "[^/.,;?<>]++");
			$nextChar = (isset($pattern[$varPos + strlen($varPattern)])) ? $pattern[$varPos + strlen($varPattern)] : "";
			$prevChar = ($varPos > 0) ? $pattern[$varPos - 1] : "";

			if (key_exists($variable, $this->getDefaults())) {
				// Make variables that have default values optional
				// Also make delimiter (if next char is a delimiter) to be also optional
				if ($delimiter == $nextChar and ($prevChar == "" or $prevChar == $delimiter)) {
					$delim = preg_quote($delimiter, "#");
					$regex = preg_replace("#".$varPattern.$delim."#", "((?P<".$variable.">".$capture.")".$delim.")?", $regex);
				} else {
					$regex = preg_replace("#".$varPattern."#", "((?P<".$variable.">".$capture."))?", $regex);
				}
				// var_dump("#^".$regex.'$#siuD');
			} else {
				$regex = preg_replace("#".$varPattern."#", "(?P<".$variable.">".$capture.")", $regex);
			}
		}
		
		return "#^".$regex.'$#siuD';
	}
	
	/**
	 * Compile regular expression for the route
	 */
	protected function _compile($pattern)
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for: {}
		$regex = preg_replace('#[.\\+?[^\\]$()<>=!]#', '\\\\$0', $pattern);
		
		// Transform segments and segment patterns
		// $this is not available in Closures before PHP 5.4, so we'll use function instead of Closure
		$regex = preg_replace_callback('#{([a-zA-Z0-9_]++)}#uD', array($this, 'regex_key_prc'), $regex); 

		return '#^'.$regex.'$#siuD';
	}

	protected function _regex_key_prc($match)
	{
		// Replace matches with segment rules or what {segment} accepts by default
		return "(?P<{$match[1]}>" . Filter::key($match[1], $this->segments, "[^/.,;?<>]++") . ')';
	}

}
