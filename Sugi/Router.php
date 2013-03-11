<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use \Sugi\HTTP\Route;
use \Sugi\HTTP\Request;
use \Sugi\Container;
use \Sugi\Event;
use \Sugi\Config;
use \Sugi\Filter;

/**
 *
 */
class Router
{
	protected static $routes = null;
	protected static $removeIndexPhp = true;
	
	/**
	 * Registers a Route
	 * 
	 * @param  string  $name - the name of the registered route
	 * @param  string  $pattern
	 * @param  array   $defaults - default values for pattern variables
	 * @param  array   $requisites - regular expressions for accepted variables in the pattern
	 * @param  Closure $closure - closure to be executed when the request matches route
	 * @return Sugi\HTTP\Route
	 */
	public static function add($name, $pattern, $defaults = array(), $requisites = array(), $closure = null)
	{
		static::init();

		if (static::$routes->has($name)) {
			throw new \Exception("Already have route $name");
		}

		$route = new Route($pattern, $defaults, $requisites);
		static::$routes[$name] = array("name" => $name, "route" => $route, "closure" => $closure);

		return $route;
	}

	/**
	 * Checks each route against the request and on match 
	 */
	public static function proccess(Request $request = null)
	{
		static::init();

		if (is_null($request)) {
			$request = Request::real();
		}

		foreach (static::$routes as $name => $route) {
			if ($route["route"]->match($request)) {
				if ($route["closure"]) {
					call_user_func_array($route["closure"], array($route));
				} elseif (Event::hasListeners("sugi.route")) {
					Event::fire("sugi.route", $route);
				} else {
					App::execute("Controller_{$route->variables['controller']}", "action_{$route->variables['action']}", array($route->variables['param'])) and exit;
				}
			}
		}
	}

	protected static function init()
	{
		if (is_null(static::$routes)) {
			static::$routes = new Container();

			if ($config = Config::file("routes")) {
				foreach ($config as $key => $route) {
					static::add($key, $route["path"], Filter::key("defaults", $route, array()), Filter::key("requisites", $route, array()));
				}
			}
		}
	}
}
