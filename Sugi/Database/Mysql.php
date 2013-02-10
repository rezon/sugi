<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * MySQL driver for Sugi\Database class
 */
class Mysql implements IDatabase
{
	/**
	 * Cache of connection parameters
	 * @var array
	 */
	protected $params;

	/**
	 * MySQLi connection handle
	 * @var object
	 */
	protected $dbHandle = null;

	/**
	 * Default value is true;
	 * This can be manually set to false with mysqli_autocommit(false)
	 * or can temporary deactivate using begin() and restored with commit() or rollback() functions
	 */
	private $autocommit = true;

	/**
	 * Creates an instance of Sugi/IDatabase
	 * 
	 * @param array $params - associative array:
	 *  - "handle" object - if this is set and it's a MySQLi handle all other config options are ignored on first connect
	 *  - "host" string - OPTIONAL
	 *  - "user" string - connection username
	 *  - "pass" string - OPTIONAL connection password
	 *  - "database" string - database name
	 */
	function __construct(array $params)
	{
		if (!isset($params["handle"]) and empty($params["database"])) {
			throw new Exception("internal_error", "Database handle or database name required"); 
		}
		if (isset($params["handle"])) {
			$this->setHandle($params["handle"]);
		}

		$this->params = $params;
	}

	function open()
	{
		// if we have a MySQL database handle (connection) return it now
		if ($this->dbHandle) {
			return $this->dbHandle;
		}

		$params = $this->params;
		/*
		 * When one of those are not given the MySQLi's default will be used
		 */
		$user = (isset($params['user'])) ? $params['user'] : null;
		$pass = (isset($params['pass'])) ? $params['pass'] : null;
		$host = (isset($params['host'])) ? $params['host'] : null;
		$database = (isset($params['database'])) ? $params['database'] : null;
		
		// Establish connection
		if (!$this->dbHandle = @mysqli_connect($host, $user, $pass, $database)) {
			throw new Exception("connection_error", mysqli_connect_error());
		}

		return $this->dbHandle;
	}
	
	function close()
	{
		if (!$this->dbHandle) {
			return;
		}
		$this->dbHandle->close();
		$this->dbHandle = null;
	}
	
	function escape($item)
	{
		return mysqli_real_escape_string($this->dbHandle, $item);
	}
	
	/**
	 * Executes query
	 * 
	 * @param string SQL statement
	 * @return object(mysqli_result) or FALSE on failure
	 */
	function query($sql)
	{
		return @mysqli_query($this->dbHandle, $sql, MYSQLI_STORE_RESULT);
	}
	
	function fetch($res)
	{
		return mysqli_fetch_assoc($res);
	}

	function affected($res)
	{
		return mysqli_affected_rows($this->dbHandle);
	}
	
	function lastId()
	{
		return mysqli_insert_id($this->dbHandle);
	}
		
	function free($res)
	{
		mysqli_free_result($res);
	}
	
	function error()
	{
		return mysqli_error($this->dbHandle);
	}
	
	function getHandle()
	{
		return $this->dbHandle;
	}

	/*
	 * Other functions that are not part of the IDatabase
	 */
	
	/**
	 * Begin transaction
	 */
	public function begin()
	{
		if (!$this->autocommit) {
			return $this->mysqli_autocommit(false);
		}
		else {
			return true;
		}
	}

	/**
	 * Commit transaction
	 */
	public function commit()
	{
		$r = mysqli_commit($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	/**
	 * Rollback transaction
	 */
	public function rollback()
	{
		$r = mysqli_rollback($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}

	/**
	 * Turns on or off auto-commiting database modifications
	 * To get current auto-commit mode: SELECT @@autocommit
	 * @param boolean - Whether to turn on auto-commit or not. 
	 * @return boolean
	 */
	public function mysqli_autocommit($mode)
	{
		if (mysqli_autocommit($this->dbHandle, $mode)) {
			$this->autocommit = $mode;
			return true;		
		}
		else {
			return false;
		}
	}

	protected function setHandle($handle)
	{
		if (gettype($handle) != "object" or get_class($handle) != "mysqli") {
			throw new Exception("internal_error", "Handle must be MySQLi object");
		}
		$this->dbHandle = $handle;
	}

}
