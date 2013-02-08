<?php namespace Sugi;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 */

/**
 * Database class - database abstraction class.
 * 
 * Hookable methods: open, close, query
 * Hooks can be triggered before (pre_{method_name}) and 
 * after (post_{method_name}) each hookable methods.
 */
class Database
{
	/**
	 * Database instance
	 * @var string
	 */
	protected $db;

	/**
	 * Handle to DB connection
	 * @var resource
	 */
	protected $dbHandle;
	
	/**
	 * Hooks
	 * @var array of events
	 */
	protected $hooks = array();
	
	/**
	 * Class constructor
	 */
	public function __construct(array $config = null)
	{
		if (empty($config["type"])) {
			throw new Database\Exception("Required database type parameter is missing");
		}
		$type = $config["type"];
		unset($config["type"]);

		$type = ucfirst(strtolower($type));
		// support for old type mysqli
		if ($type == "Mysqli") $type = "Mysql";

		$class_name = "\Sugi\Database\\$type";
		try {
			$this->db = new $class_name($config);
		} catch (\Exception $e) {
			throw new Database\Exception("Could not instantiate $class_name", 0, $e);
		}

		if (!$this->db instanceof \Sugi\Database\IDatabase) {
			throw new Database\Exception("$class_name is not Sugi\Database\IDatabase");
		}
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Opens connection to the database
	 * 
	 * @return mixed - database connection object
	 */
	public function open()
	{
		if ($this->dbHandle) {
			return $this->dbHandle;
		}

		$this->triggerAction("pre", "open");
		$this->dbHandle = $this->db->_open();
		$this->triggerAction("post", "open");

		return $this->dbHandle;
	}
	
	/**
	 * Closes connection to the database
	 */
	public function close()
	{
		if ($this->dbHandle) {
			if ($this->db->_close()) {
				$this->triggerAction("pre", "close");
				$this->dbHandle = null;
				$this->triggerAction("post", "close");
			}
		}
	}
	
	/**
	 * Escapes a string
	 * 
	 * @param string $item
	 * @return string
	 */
	public function escape($item)
	{
		// For delayed opens
		$this->open();

		return $this->db->_escape($item);
	}

	/**
	 * Executes query.
	 * Query could be any valid SQL statement.
	 * 
	 * @param string $sql
	 * @throws Database\Exception If the query fails
	 * @return mixed
	 */
	public function query($sql)
	{
		// For delayed opens
		$this->open();

		$this->triggerAction("pre", "query", $sql);
		if ($res = $this->db->_query($sql)) {
			$this->triggerAction("post", "query", $sql);
			return $res;
		}
			
		throw new Database\Exception($this->db->_error());
	}
	
	/**
	 * Fetches one row
	 * 
	 * @param handle $res result returned from query()
	 * @return array
	 */
	public function fetch($res)
	{
		try {
			$res = $this->db->_fetch($res);
		} catch (\Exception $e) {
			throw new Database\Exception($e->getMessage());
		}

		return $res;
	}

	/**
	 * Fetches all rows
	 * 
	 * @param handle $res result returned from query()
	 * @return array
	 */
	public function fetchAll($res)
	{
		$return = array();
		while ($row = $this->fetch($res)) {
			$return[] = $row;
		}
		return $return;
	}

	/**
	 * Alias of fetchAll
	 */
	public function fetch_all($res)
	{
		return $this->fetchAll($res);
	}
	
	/**
	 * Fetches all rows
	 * 
	 * @param string $sql SQL statement
	 * @return array
	 */
	public function all($sql)
	{
		return $this->fetchAll($this->query($sql));
	}
	
	/**
	 * Fetches single row
	 * 
	 * @param string $sql SQL statement
	 * @return array|null
	 */
	public function single($sql)
	{
		if ($res = $this->query($sql)) {
			return $this->fetch($res);
		}

		return null;
	}
	
	/**
	 * Returns first field of the first row
	 * 
	 * @param string $sql - SQL statment
	 * @return string|null
	 */
	public function singleField($sql)
	{
		if ($row = $this->single($sql)) {
			return array_shift($row);
		}

		return null;
	}

	/**
	 * Alias of single_field()
	 */
	public function single_field($sql)
	{
		return $this->singleField($sql);
	}

	/**
	 * Returns rows affected by the query
	 * 
	 * @param handle $res handle returned by query()
	 * @return integer
	 */
	public function affected($res = null)
	{
		return $this->db->_affected($res);
	}
	
	/**
	 * Returns last ID returned after successfull INSERT statement
	 * 
	 * @return mixed
	 */
	public function lastId()
	{
		return $this->db->_last_id();
	}
	
	/**
	 * Alias of lastId()
	 */
	public function last_id()
	{
		return $this->lastId();
	}
	
	/**
	 * Frees result
	 * 
	 * @param handle $res handle returned by query()
	 */
	public function free($res)
	{
		$this->db->_free($res);
	}

	/**
	 * Starts a transaction
	 * 
	 * @return boolean
	 */
	public function begin()
	{
		// For delayed opens
		$this->open();
		return $this->db->_begin();
	}
	
	/**
	 * Commits transaction
	 *
	 * @return boolean
	 */
	public function commit()
	{
		return $this->db->_commit();
	}
	
	/**
	 * Rollbacks transaction
	 *
	 * @return boolean
	 */
	public function rollback()
	{
		return $this->db->_rollback();
	}
	
	/**
	 * Returns handle to a connection established with open()
	 * 
	 * @return handle
	 */
	public function getConnection()
	{
		return $this->dbHandle;
	}

	/**
	 * Hook a callback function/method to some hookable events.
	 * Hooks could be 'pre_' and 'post_'.
	 *
	 * <code>
	 * 	// to hook an event before executing a query
	 *  Database::hook('pre_query', array($object, 'method_name'));
	 *  // to hook an event after executing a query
	 *  Database::hook('post_query', 'function_name')
	 * </code>
	 * 
	 * @param string $event - pre or post method name
	 * @param mixed $callback - callable function or method name
	 */
	public function hook($event, $callback)
	{
		if (is_array($callback)) $inx = get_class($callback[0]).'::'.$callback[1];
		elseif (gettype($callback) == 'object') $inx = uniqid();
		else $inx = $callback;
				
		$this->hooks[$event][$inx] = $callback;
	}

	
	/**
	 * Unhook.
	 * If callback is not given all callbacks are unhooked from this event.
	 * If event is not given all callbacks are unhooked.
	 * 
	 * <code>
	 * 	Database::unhook('pre_query', array($this, 'before_query')); // This will unhook method $this->before_query before query
	 * 	Database::unhook('post_query'); // This will unhook all callbacks which are executed after query
	 *  Database::unhook(); // This will unhook all callbacks
	 *  Database::unhook(false, 'test'); // This will unhook callback function test from any (pre and post) events
	 * </code>
	 * 
	 * @param string $event
	 * @param mixed $callback - callback function to unhook.
	 */
	public function unhook($event = null, $callback = null)
	{
		if (is_array($callback)) $inx = get_class($callback[0]).'::'.$callback[1];
		else $inx = $callback;
						
		if (is_null($event) AND is_null($callback)) {
			$this->hooks = array();
		}
		elseif (is_null($callback)) {
			$this->hooks[$event] = array();
		}
		elseif (is_null($event)) {
			foreach ($this->hooks as $key => $value) {
				unset($this->hooks[$key][$inx]);
			}
		}
		else {
			unset($this->hooks[$event][$inx]);
		}
	}

	/**
	 * Escapes each element in the array
	 * 
	 * @param array
	 * @return array
	 */
	public function escapeAll(array $values)
	{
		$return = array();
		foreach ($values as $key => $value) {
			if (is_null($value)) $value = 'null';
			elseif (is_numeric($value)) ;
			elseif (is_bool($value)) $value = $value ? 'TRUE' : 'FALSE';
			else $value = "'" . $this->escape($value) . "'";
			$return[$key] = $value;
		}
		return $return;
	}

	/**
	 * Escapes all parameters and binds them in the SQL
	 * 
	 * @param string $sql
	 * @param array $params Associative array, where the key is replaced in the SQL with the value  
	 * @return string
	 */
	public function bindParams($sql, array $params)
	{
		$params = $this->escapeAll($params);
		if (preg_match_all('#:([a-zA-Z0-9_]+)#', $sql, $matches)) {
			foreach ($matches[1] as $match) {
				$value = isset($params[$match]) ? $params[$match] : 'null';
				$sql = str_replace(":$match", $value, $sql);
			}
		}
		return $sql;
	}

	protected function triggerAction($type, $action, $data = null)
	{
		$hook = "{$type}_{$action}";
		// check for hooks
		if (!empty($this->hooks[$hook])) {
			foreach ($this->hooks[$hook] as $callback) {
				$callback($action, $data);
			}
		}
	}
}
