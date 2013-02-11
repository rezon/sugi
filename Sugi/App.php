<?php namespace Sugi;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 */

defined("APPLICATION_START") or define("APPLICATION_START", microtime(true));

/**
 * Application base class
 * Autoloads and executes application specific classes
 */
class App
{
	static $path = array();
	static $registered = false;

	/**
	 * Convinient way to configure application
	 *
	 * @param array $config
	 */
	public static function configure(array $config = null)
	{
		defined("DS") or define("DS", DIRECTORY_SEPARATOR);
		defined("BASEPATH") or define("BASEPATH", realpath(__DIR__.DS."..".DS."..".DS."..".DS."..").DS);

		// checking for configuration
		if (is_null($config)) {
			// checking config has been configured. If not - guess the configuration path
			Config::has("_path") or Config::set("_path", realpath(BASEPATH."app".DS."config").DS);
			// loading configuration
			$config = Config::file("app");
		}

		/**
		 * Are we on development or on production server
		 * To set development environment add the following code in your apache configuration file
		 * <code>
		 * 	SetEnv APPLICATION_ENV development
		 * </code>
		 * 
		 * @var string
		 */
		defined("APPLICATION_ENV") or define("APPLICATION_ENV", (getenv("APPLICATION_ENV") === "development") ? "development" : "production");

		/**
		 * Define DEBUG flag
		 * Debug depends of is it on development or production, 
		 * or it can be manually set to true or false in config file or with
		 * <code>
		 * 	define("DEBUG", true);
		 * 	define("DEBUG", false);
		 * </code>
		 *
		 * @var string
		 */
		defined("DEBUG") OR define("DEBUG", isset($config["debug"]) ? $config["debug"] : (APPLICATION_ENV == "development"));
				
		/*
		 * Set error reporting level
		 * Error reporting depends of DEBUG
		 */
		error_reporting((DEBUG) ? E_ALL | E_STRICT : E_ALL ^ E_NOTICE ^ E_USER_NOTICE ^ E_WARNING ^ E_DEPRECATED);
				
		/*
		 * The errors are wellcome even in production if we use error and exception handlers to display 
		 * custom error page, rather than a blank page
		 * Display errors can be set to false, or to DEBUG which will show errors on development and 
		 * hide them from production
		 */
		ini_set("display_errors", DEBUG);
				
		/*
		 * Since we have no error_handler at this time, it's a good idea to see them in HTML format
		 * can be set to true or false
		 * or ini_get("diaplay_errors") which will make them appear in HTML format on the screen, or in text format when errors does not appear on screen
		 */
		ini_set("html_errors", ini_get("diaplay_errors"));

		// Set the default time zone
		if (!empty($config["timezone"])) date_default_timezone_set($config["timezone"]);

		// Register/Unregister default application autoloder
		if (isset($config["autoload"])) {
			if ($config["autoload"] and !static::$registered) static::register();
			if (!$config["autoload"] and static::$registered) static::unregister();
		}
	}


	/**
	 * Autoload function - loads var classes
	 *
	 * @param string $class_name
	 */
	public static function autoload($class_name)
	{
		if (!class_exists($class_name)) {
			if ($file = static::search_file(str_replace("_", DIRECTORY_SEPARATOR, strtolower($class_name)).".php")) {
				require_once $file;
			}
		}
	}

	/**
	 * Registers application autoload function
	 */
	public static function register()
	{
		static::$registered = true;
		spl_autoload_register(array("\Sugi\App", "autoload"), true, false);
	}

	/**
	 * Unregisters application autoload function
	 */
	public static function unregister()
	{
		static::$registered = false;
		spl_autoload_unregister(array("\Sugi\App", "autoload"));
	}
	
	/**
	 * Searching for a file
	 *
	 * @param string $file
	 * @return mixed - string if we have located the file, false if there is no such file in the search path
	 */
	public static function search_file($file)
	{
		$where = explode(PATH_SEPARATOR, get_include_path());
		if (static::$path) $where = array_merge(static::$path, $where);
		foreach ($where as $path) {
			$path = rtrim($path, '/') . DS;
			if (File::exists($path.$file)) return $path.$file;
		}
		return false;
	}

	/**
	 * Check method from a class can be called 
	 *
	 * @param mixed $class
	 * @param string $method
	 */
	public static function class_method_exists($class, $method)
	{
		try {
			// Load the controller using reflection
			$class = new \ReflectionClass($class);

			// Check we can create an instance of the class
			if ($class->isAbstract()) {
				return false;
			}
			
			// Check the class has needed method
			if (!$class->hasMethod($method)) {
				return false;
			}
			
			// Is the method callable?
			if (!$class->getMethod($method)->isPublic()) {
				return false;
			}
	
			return true;
		}
		catch (\Exception $e) {
			if ($e instanceof \ReflectionException) {
				// Reflection will throw exceptions for missing classes or actions
				return false;
			}
		}
	}
	
	/**
	 * Safe way to execute method from a class
	 * 
	 * @param string $class
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public static function execute($class, $method, $params)
	{
		try {
			// Load the controller using reflection
			$class = new \ReflectionClass($class);
	
			// Create a new instance of the controller
			$controller = $class->newInstance();
	
			// Execute the "before action" method
			if ($class->hasMethod("pre_action")) $class->getMethod("pre_action")->invoke($controller);
			
			// Execute the main action with the parameters
			$class->getMethod($method)->invokeArgs($controller, $params);
			
			// Execute the "after action" method
			if ($class->hasMethod("post_action")) $class->getMethod("post_action")->invoke($controller);
			
			return $controller; 
		}
		catch (\Exception $e) {
			if ($e instanceof \ReflectionException) {
				// Reflection will throw exceptions for missing classes or actions
				return false;
			}
	
			// All other exceptions are PHP/server errors (status 500)
			throw $e; // re-throw the exception
		}
	}
}
