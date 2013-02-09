<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 * @deprecated SQLite is not available in PHP versions 5.4.0 and above
 */

/**
 * SQLite extention for Database class
 */
class Sqlite implements IDatabase
{
	protected $params;
	protected $dbHandle = null;
	private $_err = "";

	public function __construct(array $config)
	{
		$this->params = $config;
	}
	
	function open()
	{
		$database = (isset($this->params['database'])) ? $this->params['database'] : null;
		$mode = (isset($this->params['mode'])) ? $this->params['mode'] : 0666;
		$err = "";
		$conn = \sqlite_open($database, $mode, $err);
		if ($err) {
			throw new Exception("Could not connect to the database with message $err");
		}
		/*
		 * Could not connect, but sqlite_error will not be triggered.
		 * This could happen for example when there are open_basedir restriction in effect!
		 */
		if (!$conn) {
			throw new Exception("Could not connect to the database");
		}

		$this->dbHandle = $conn;

		return $conn;
	}
	
	function close()
	{
		\sqlite_close($this->dbHandle);
		return true;
	}
	
	function escape($item)
	{
		return \sqlite_escape_string($item);
	}
	
	function query($sql)
	{
		return \sqlite_query($this->dbHandle, $sql, SQLITE_ASSOC, $this->_err);
	}

	function fetch($res)
	{
		return \sqlite_fetch_array($res, SQLITE_ASSOC);		
	}
	
	function affected($res)
	{
		return \sqlite_changes($this->dbHandle);
	}

	function lastId()
	{
		return \sqlite_last_insert_rowid($this->dbHandle);
	}

	function free($res)
	{
		//sqlite_free_result($res); 
	}
	
	function begin()
	{
		return false;
	}

	function commit()
	{
		return false;
	}
	
	function rollback()
	{
		return false;
	}
		
	function error()
	{
		return $this->_err;
	}
}
