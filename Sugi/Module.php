<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.11.21
 */

use \Sugi\Config;


/**
 * Module - registry of class, methods, objects with ability to instantiate them
 */
class Module
{

	/**
	 * Module aliases
	 * @var array
	 */
	public static $aliases = array();
	
	/**
	 * Module closures
	 * @var array
	 */
	public static $closures = array();


	/**
	 * Loaded modules
	 * @var array
	 */
	protected static $modules = array();
	

	public static function set($alias, $param) {

		$alias = strtolower($alias);

		if (is_string($param)) 
		{ //set alias
			Module::$aliases[$alias] = $param;
		} 
		elseif (is_callable($param)) 
		{	//set closure
			Module::$closures[$alias] = $param;
		}

	}


	public static function get($alias) {
		
		$alias = strtolower($alias);
		
		if (isset(Module::$modules[$alias])) 
		{	// If we have already loaded this module we return it right now
			return Module::$modules[$alias];
		} else 
		{	// return new module and to the list for next reference
			Module::$modules[$alias] = Module::factory($alias);
			return Module::$modules[$alias];
		}
		
	}
	
	public static function factory($alias, array $params = array()) {
		
		$module = FALSE;
		$name = (isset(Module::$aliases[$alias])) ? Module::$aliases[$alias] : $alias;
		
		// Loader
		if (isset(Module::$closures[$alias]) && is_callable(Module::$closures[$alias])) {
			$module = call_user_func_array(Module::$closures[$alias], $params); 
		}
		elseif (($alias != $name) AND (isset(Module::$closures[$name]) && is_callable(Module::$closures[$name]))) {
			$module = call_user_func_array(Module::$closures[$name] , $params); 
		}
		else {
			Module::$closures[$name] = function() use ($alias, $name) {
				// Configuration settings
				if (!($conf = Config::$alias()))
					if (!(($alias != $name) AND ($conf = Config::$name())))
						$conf = FALSE;
				return new $name($conf);
			};
			$module = call_user_func_array(Module::$closures[$name], $params); 	
		}		
		return $module;
		
	}
}
