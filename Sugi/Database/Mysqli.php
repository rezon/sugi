<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @version 13.02.06
 */

/**
 * MySQLi extention for Database class
 */
class Mysqli implements IDatabase
{
	protected $params;
	protected $dbHandle = null;

	/**
	 * Default value is true;
	 * This can be manually set to false with mysqli_autocommit(false)
	 * or can temporary deactivate using begin() and restored with commit() or rollback() functions
	 */
	private $_autocommit = true;

	public function __construct(array $config)
	{
		$this->params = $config;
	}

	function _open()
	{
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
			throw new \Sugi\DatabaseException(mysqli_connect_error());
		}
		$this->dbHandle = $conn;

		return $conn;
	}
	
	function _close()
	{
		if (mysqli_close($this->dbHandle)) {
			return true;
		}

		throw new \Sugi\DatabaseException(mysql_error());
	}
	
	function _escape($item)
	{
		return mysqli_real_escape_string($this->dbHandle, $item);
	}
	
	function _query($sql)
	{
		return mysqli_query($this->dbHandle, $sql, MYSQLI_STORE_RESULT);
	}
	
	function _fetch($res)
	{
		return mysqli_fetch_assoc($res);
	}

	function _single($sql)
	{
		$res = $this->_query($sql);
		$row = $this->_fetch($res);
		$this->_free($res);
		return $row;
	}
	
	function _single_field($sql)
	{
		$res = $this->_query($sql);
		$row = mysqli_fetch_row($res);
		$this->_free($res);
		return $row[0];
	}
	
	function _affected($res)
	{
		return mysqli_affected_rows($this->dbHandle);
	}
	
	function _last_id()
	{
		return mysqli_insert_id($this->dbHandle);
	}
		
	function _free($res)
	{
		mysqli_free_result($res);
	}
	
	function _begin()
	{
		if (!$this->_autocommit) {
			return $this->mysqli_autocommit(false);
		}
		else {
			return true;
		}
	}

	function _commit()
	{
		$r = mysqli_commit($this->dbHandle);
		if (!$this->_autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	function _rollback()
	{
		$r = mysqli_rollback($this->dbHandle);
		if (!$this->_autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	function _error()
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
			$this->_autocommit = $mode;
			return true;		
		}
		else {
			return false;
		}
	}
}
