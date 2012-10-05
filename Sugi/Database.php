<?php
/**
 * Database class - common database abstract class.
 * DatabaseException class
 * 
 * Hookable methods: open, close, query, escape, fetch,
 * begin, commit, rollback, single, 
 * single_field, affected, last_id, free.
 * Hooks can be triggered before (pre_{method_name}) and 
 * after (post_{method_name}) each hookable methods.
 * 
 * @package Sugi
 * @version 20121005
 */
namespace Sugi;

/**
 * Database class
 */
abstract class Database
{
	/**
	 * Opens connection to the database
	 * 
	 * @return db connection object
	 */
	public function open() {
		$this->h_open();
		return $this->_conn;
	}
	
	/**
	 * Closes connection to the database
	 */
	public function close() {
		if ($this->_conn and $this->h_close()) {
			$this->_conn = null;
		}
	}
	
	/**
	 * Escapes a string
	 * 
	 * @param string $item
	 * @return string
	 */
	public function escape($item) {
		// For delayed opens
		if (!$this->_conn) {
			$this->open();
		}
		return $this->h_escape($item);
	}

	/**
	 * Executes query.
	 * Query could be any valid SQL statement.
	 * 
	 * @param string $sql
	 */
	public function query($sql) {
		// For delayed opens
		if (!$this->_conn) {
			$this->open($this->_params);
		}
		if ($res = $this->h_query($sql)) {
			return $res;
		}
			
		throw new DatabaseException($this->_error($this->_conn));
	}
	
	/**
	 * Fetches one row
	 * 
	 * @param handle $res result returned from query()
	 * @return array
	 */
	public function fetch($res) {
		return $this->h_fetch($res);
	}

	/**
	 * Fetches all rows
	 * 
	 * @param handle $res result returned from query()
	 * @return array
	 */
	public function fetch_all($res) {
		$return = array();
		while ($row = $this->fetch($res)) {
			$return[] = $row;
		}
		return $return;
	}
	
	/**
	 * Fetches all rows
	 * 
	 * @param string $sql SQL statement
	 * @return array
	 */
	public function all($sql) {
		return $this->fetch_all($this->query($sql));
	}
	
	/**
	 * Fetches single row
	 * 
	 * @param string $sql SQL statement
	 * @return array
	 */
	public function single($sql) {
		// For delayed opens
		if (!$this->_conn) {
			$this->open($this->_params);
		}
		return $this->h_single($sql);
	}
	
	/**
	 * Returns first field of the first row
	 * 
	 * @param str $sql SQL statment
	 * @return string
	 */
	public function single_field($sql) {
		// For delayed opens
		if (!$this->_conn) {
			$this->open($this->_params);
		}
		return $this->h_single_field($sql);
	}

	/**
	 * Returns rows affected by the query
	 * 
	 * @param handle $res handle returned by query()
	 * @return integer
	 */
	public function affected($res = null) {
		return $this->h_affected($res);
	}
	
	/**
	 * Returns last ID returned after successfull INSERT statement
	 * 
	 * @return int
	 */
	public function last_id() {
		return $this->h_last_id();
	}
	
	/**
	 * Frees result
	 * 
	 * @param handle $res handle returned by query()
	 */
	public function free($res) {
		$this->h_free($res);
	}

	/**
	 * Starts a transaction
	 * 
	 * @return boolean
	 */
	public function begin() {
		// For delayed opens
		if (!$this->_conn) {
			$this->open($this->_params);
		}
		return $this->h_begin();
	}
	
	/**
	 * Commits transaction
	 *
	 * @return boolean
	 */
	public function commit() {
		return $this->h_commit();
	}
	
	/**
	 * Rollbacks transaction
	 *
	 * @return boolean
	 */
	public function rollback() {
		return $this->h_rollback();
	}
	
	/**
	 * Returns handle to a connection established with open()
	 * 
	 * @return handle
	 */
	public function get_connection() {
		return $this->_conn;
	}
	
	/**
	 * Hook a callback function/method to some hookable events.
	 * Hooks could be 'pre_' and 'post_'.
	 * @example:
	 * <code>
	 * 	// to hook an event before executing a query
	 *  Database::hook('pre_query', array($object, 'method_name'));
	 *  // to hook an event after executing a query
	 *  Database::hook('post_query', 'function_name')
	 * </code>
	 * 
	 * @param str $event - pre or post method name
	 * @param mixed $callback - callable function or method name
	 */
	public function hook($event, $callback) {
		// FIXME: this doesn't work with closures
		if (is_array($callback)) $inx = get_class($callback[0]).'::'.$callback[1];
		else $inx = $callback;
				
		$this->_hooks[$event][$inx] = $callback;
	}
	
	/**
	 * Unhook.
	 * If callback is not given all callbacks are unhooked from this event.
	 * If event is not given all callbacks are unhooked.
	 * 
	 * @example: 
	 * <code>
	 * 	Database::unhook('pre_query', array($this, 'before_query')); // This will unhook method $this->before_query before query
	 * 	Database::unhook('post_query'); // This will unhook all callbacks which are executed after query
	 *  Database::unhook(); // This will unhook all callbacks
	 *  Database::unhook(false, 'test'); // This will unhook callback function test from any (pre and post) events
	 * </code>
	 * 
	 * @param str $event
	 * @param mixed $callback - callback function to unhook.
	 */
	public function unhook($event = null, $callback = null) {
		if (is_array($callback)) $inx = get_class($callback[0]).'::'.$callback[1];
		else $inx = $callback;
						
		if (is_null($event) AND is_null($callback)) {
			$this->_hooks = array();
		}
		elseif (is_null($callback)) {
			$this->_hooks[$event] = array();
		}
		elseif (is_null($event)) {
			foreach ($this->_hooks as $key => $value) {
				unset($this->_hooks[$key][$inx]);
			}
		}
		else {
			unset($this->_hooks[$event][$inx]);
		}
	}




	/**
	 * Database type - can be mysql, mysqli, sqlite, postgres...
	 * @var string
	 */
	private $_dbtype = false;

	/**
	 * Handle to DB connection
	 * @var resource
	 */
	protected $_conn = false;
	
	/**
	 * Connection params
	 * @var array
	 */
	protected $_params = false;
	
	/**
	 * Hooks
	 * @var array of events
	 */
	protected $_hooks = array();
	
	/**
	 * Class constructor
	 */
	protected function __construct($dbtype, $params) {
		$this->_dbtype = $dbtype;
		$this->_params = $params;
	}

	/**
	 * Some methods are prefixed with h_. Those methods should be started without using h_ (hook) prefix.
	 * In this way those methods will get here and all hooks will be invoked
	 * 
	 * @param str $name - method name, that is invoked
	 * @param arr $args - arguments that are passed
	 */
	public function __call($name, $args) {
		// calling hookable method?
		if (strpos($name, 'h_') !== 0) {
			throw new DatabaseException("Call to undefined method Database::{$name}()");
		}

		$method = substr($name, 1);
		if (!method_exists($this, $method)) {
			throw new DatabaseException("Call to undefined method Database::{$method}()");
		}
	
		// check for pre hooks
		$hook = 'pre'.$method;
		if (!empty($this->_hooks[$hook])) {
			$callback_args = $args;
			array_unshift($callback_args, $hook);
			foreach ($this->_hooks[$hook] as $key => $callback) {
				call_user_func_array($callback, $callback_args);
			}
		}
		
		// execute method
		$result = call_user_func_array(array($this, $method), $args);
		
		// check for post hooks
		$hook = 'post'.$method;
		if (!empty($this->_hooks[$hook])) {
			$callback_args = $args;
			array_unshift($callback_args, $hook);
			$callback_args[] = $result;
			foreach ($this->_hooks[$hook] as $callback) {
				call_user_func_array($callback, $callback_args);
			}
		}
		
		return $result;
	}

	public static function __callStatic($name, $arguments) {
		$name = ucfirst(strtolower($name));
		$class_file = dirname(__FILE__)."/database/{$name}.php";
		if (file_exists($class_file)) {
			$db = static::factory($name, $arguments[0]);
			return $db;
		}

		throw new DatabaseException("Call to undefined method Sugi\Database::{$name}()");
	}
	
	/**
	 * Static method to create a specific database class
	 * @param string $dbtype can be 'sqlite', 'mysqli', 'mysql'...
	 * @return instance of that class
	 */
	protected static function factory($dbtype, $params) {
		// If the class is not loaded we will try to include the file
		$class = "\Sugi\Database\\$dbtype";
		if (!class_exists($class, FALSE)) {
			$class_file = dirname(__FILE__)."/database/{$dbtype}.php";
			include_once $class_file;
		}

		// If the class exists we will create instance
		if (class_exists($class, FALSE)) {
			return new $class($dbtype, $params);
		}

		throw new DatabaseException("Invalid Database Type $dbtype");
	}

	/**
	 * Class destructor
	 */
	public function __destruct() {
		$this->close();
	}




	/**
	 * Connects to the database
	 * @return resource handle to connection
	 */
	abstract protected function _open();
	
	/**
	 * Closes connection to the database
	 * @return boolean - true on success
	 */
	abstract protected function _close();
	
	/**
	 * Escapes a string for use as a query parameter
	 * @param string
	 * @return string
	 */
	abstract protected function _escape($item);
	
	/**
	 * Executes query
	 * @param string SQL statement
	 * @return resource id
	 */
	abstract protected function _query($sql);
	
	/**
	 * Fetches row
	 * @param resource id
	 * @return array if the query returns rows
	 */
	abstract protected function _fetch($res);
	
	/**
	 * Returns one (first) row
	 * @param string SQL statement
	 * @return array
	 */
	abstract protected function _single($sql);
	
	/**
	 * Returns first field in a first returned row
	 * @param string SQL statement
	 * @return string
	 */
	abstract protected function _single_field($sql);
	
	/**
	 * Returns the number of rows that were changed by the most recent SQL statement (INSERT, UPDATE, REPLACE, DELETE)
	 * @return integer
	 */
	abstract protected function _affected($res);
	
	/**
	 * Returns the auto generated id used in the last query
	 * @return int
	 */
	abstract protected function _last_id();
	
	/**
	 * Frees the memory associated with a result
	 * @param A result set identifier returned by query()
	 * @return void
	 */
	abstract protected function _free($res);
	
	/**
	 * Begin Transaction
	 * @return bool
	 */
	abstract protected function _begin();
	
	/**
	 * Commit Transaction
	 * @return bool
	 */
	abstract protected function _commit();
	
	/**
	 * Rollback Transaction
	 * @return bool
	 */
	abstract protected function _rollback();
	
	/**
	 * Returns last error for given resource
	 * @param resource id
	 * @return string
	 */
	abstract protected function _error($res);
}

/**
 * Database exception class
 */
class DatabaseException extends \Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		$code = $this->code;
		switch($this->code) {
			case E_USER_ERROR: 
				$code = 'User Error';
				break;
			case E_USER_NOTICE:
				$code = 'User Notice';
				break;
			case E_USER_WARNING:
				$code = 'User Warning';
				break; 
			default: 
				$code = $this->code;
		};
		$file = $this->getFile(); 
		$line = $this->getLine();
		$trace = $this->getTrace();
		$stripped = '';
		$num = 0;
		foreach($trace as $backtrace) {
			if (!empty($backtrace['file']) AND ($backtrace['file'] !== __FILE__)) {
				$stripped .= "#{$num} {$backtrace['file']} ({$backtrace['line']})\n";
				$num++;
			}
		}
		$trace = print_r($stripped, TRUE);
		return __CLASS__ . ": [{$code}]: {$this->message} in {$file} ({$line}):\n$stripped";
	}
}
