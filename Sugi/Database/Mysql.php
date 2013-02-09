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
	 * Cache of connection parame
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
	 * @param array $config - associative array:
	 *  - "handle" object - if this is set and it's a MySQLi handle all other config options are ignored on first connect
	 *  - "host" string - OPTIONAL
	 *  - "user" string - connection username
	 *  - "pass" string - OPTIONAL connection password
	 *  - "database" string - database name
	 */
	public function __construct(array $config)
	{
		$this->params = $config;

		if (!empty($config["handle"])) {
			if (gettype($config["handle"]) and get_class($config["handle"]) == "mysqli") {
				$this->dbHandle = $config["handle"];
			}
			else throw new Exception("Handle paramater must be of type mysqli");
		}
	}

	function open()
	{
		// if we have a MySQL database handle (connection) return it and ignore other settings
		if ($this->dbHandle) {
			return $this->dbHandle;
		}

		$params = $this->params;

		/*
		 * When one of those are not given the MySQLi default will be used
		 */
		$user = (isset($params['user'])) ? $params['user'] : null;
		$pass = (isset($params['pass'])) ? $params['pass'] : null;
		$host = (isset($params['host'])) ? $params['host'] : null;
		$database = (isset($params['database'])) ? $params['database'] : null;
		
		$conn = @mysqli_connect($host, $user, $pass, $database);
		if (mysqli_connect_error()) {
			throw new Exception(mysqli_connect_error());
		}
		$this->dbHandle = $conn;

		return $conn;
	}
	
	function close()
	{
		if (mysqli_close($this->dbHandle)) {
			$this->dbHandle = null;
			return true;
		}

		throw new Exception(mysql_error());
	}
	
	function escape($item)
	{
		return mysqli_real_escape_string($this->dbHandle, $item);
	}
	
	function query($sql)
	{
		return mysqli_query($this->dbHandle, $sql, MYSQLI_STORE_RESULT);
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
	
	function begin()
	{
		if (!$this->autocommit) {
			return $this->mysqli_autocommit(false);
		}
		else {
			return true;
		}
	}

	function commit()
	{
		$r = mysqli_commit($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	function rollback()
	{
		$r = mysqli_rollback($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	function error()
	{
		return mysqli_error($this->dbHandle);
	}
	

	/*
	 * MySQLi Specific functions
	 */

	/**
	 * Turns on or off auto-commiting database modifications
	 * To get current auto-commit mode: SELECT @@autocommit
	 * @param bool - Whether to turn on auto-commit or not. 
	 * @return bool
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
}
