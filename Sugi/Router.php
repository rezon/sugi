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

/**
 *
 */
class Router
{
	protected static $routes = null;
	
	public static function add($name, $pattern, $defaults = array(), $requisites = array(), $closure = null)
	{
		if (is_null(static::$routes)) {
			static::$routes = new Container();
		}

		if (static::$routes->has($name)) {
			throw new \Exception("Already have route $name");
		}

		$route = new Route($pattern, $defaults, $requisites);
		static::$routes[$name] = array("name" => $name, "route" => $route, "closure" => $closure);

		return $route;
	}

	public static function proccess(Request $request = null)
	{
		foreach (static::$routes as $name => $route) {
			if ($route["route"]->match($request)) {
				if ($route["closure"]) {
					call_user_func_array($route["closure"], array($route));
				} else {
					Event::fire("sugi.router.match", $route);
				}
			} else {
				Event::fire("sugi.router.nomatch", $route);
			}
		}
	}
}
