<?php namespace Sugi;
/**
 * Sessions
 * 
 * @package Sugi
 * @version 12.12.11
 */

use \Sugi\File;

abstract class Session
{
	protected static $session = null;
	protected static $type;

	protected static $lifetime = 0;

	/**
	 * Create a session handler with singleton method
	 * 
	 * @param array $config
	 * @return \Sugi\Session with type specified in config array
	 */
	public static function singleton($config = array())
	{
		// return created session
		if (static::$session) return static::$session;

		// If we did not set type - we will not use Session wrapper
		if (empty($config['type'])) {
			return false;
		}

		// Loading child class
		$type = strtolower($config['type']);
		$Type = ucfirst($type);
		$class_name = "\Sugi\Session\\$Type";
		$class_file = __DIR__."/Session/{$Type}.php";
		if (!class_exists($class_name)) {
			if (!File::exists($class_file)) {
				throw new \Exception("Unknow \Sugi\Session type {$Type}");
			}
			include $class_file;
		}

		static::$type = $type;
		
		session_set_save_handler(
			array('\Sugi\Session', 'open'),
			array('\Sugi\Session', 'close'),
			array('\Sugi\Session', 'read'),
			array('\Sugi\Session', 'write'),
			array('\Sugi\Session', 'destroy'),
			array('\Sugi\Session', 'gc')
		);

		// Garbage collector settings
		// Max LifeTime in seconds
		if (isset($config['gc_maxlifetime'])) {
			ini_set('session.gc_maxlifetime', (int) $config['gc_maxlifetime']);
		}
		// The garbage collector (GC) probability
		// GC will be executed (gc_probability / gc_divisor)% of the sessions reads
		if (isset($config['gc_probability'])) {
			ini_set('session.gc_probability', (int) $config['gc_probability']);
		}
		if (isset($config['gc_divisor'])) {
			ini_set('session.gc_divisor', (int) $config['gc_divisor']);
		}
		
		register_shutdown_function('\Sugi\Session::close');
		
		$child_params = (isset($config[$type])) ? $config[$type] : array();

		// return instance
		static::$session = new $class_name($child_params);
		return static::$session;
	}

	/**
	 * Cookie's lifetime
	 * 
	 * @param int $days - 0 is for session lifetime (until browser close)
	 */
	public static function set_lifetime($days) {
		if (!$days) {
			static::$lifetime = 0;
			$time = 0;
		}
		else {
			static::$lifetime = $days * 24 * 60 * 60;
			$time = time() + static::$lifetime; 
		}
		$cp = session_get_cookie_params();
		setcookie(session_name(), session_id(), $time, $cp['path'], $cp['domain'], $cp['secure'], $cp['httponly']);
	}



	/***********************************************************************************
	 *                                                                                 *
	 * Following methods are automatically executed by PHP's internal session features *
	 *                                                                                 *
	 ***********************************************************************************/
	
	/**
	 * Executes on session_start()
	 * 
	 * @param string $save_path
	 * @param string $id
	 */
	public static function open($save_path, $id) {
		return static::$session->_open($save_path, $id);
	}
	
	/**
	 * Read function must return string value always to make save handler work as expected. 
	 * Return empty string if there is no data to read. 
	 * Return values from other handlers are converted to boolean expression. TRUE for success, FALSE for failure.
	 * 
	 * @param string $id
	 * @return string
	 */
	public static function read($id) {
		return static::$session->_read($id);
	}
	
	/**
	 * On session write()
	 * 
	 * @param string $id
	 * @param string $data
	 */
	public static function write($id, $data) {
		return static::$session->_write($id, $data);
	}
	
	/**
	 * On session_destroy();
	 * 
	 * @param string $id
	 */
	public static function destroy($id) {
		// delete the cookie
		$cp = session_get_cookie_params();
		setcookie(session_name(), '', time() - 30 * 24 * 3600, $cp['path'], $cp['domain'], $cp['secure'], $cp['httponly']);
		return static::$session->_destroy($id);
	}
	
	/**
	 * On session_close()
	 */
	public static function close() {
		return static::$session->_close();
	}
	
	/**
	 * Garbage collector
	 * 
	 * @param int $maxlifetime
	 */
	public static function gc($maxlifetime) {
		return static::$session->_gc($maxlifetime);
	}

	/**
	 * Class constructor is not publicly available. Use static singleton() method!
	 */
	protected function __construct()
	{
		//
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		$this->close();
	}


	/******************************************************************************
	 *                                                                            *
	 * Abstract methods which should be described in child (session driver) class *
	 *                                                                            *
	 ******************************************************************************/

	abstract protected function _open($save_path, $id);
	abstract protected function _read($id); 
	abstract protected function _write($id, $data);
	abstract protected function _destroy($id);
	abstract protected function _close();
	abstract protected function _gc($maxlifetime);
}
