<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @version 13.02.06
 */


/**
 * SQLite extention for Database class
 */
class Sqlite implements IDatabase
{
	protected $params;
	protected $dbHandle = null;
	private $_err = '';

	public function __construct(array $config)
	{
		$this->params = $config;
	}
	
	function _open()
	{
		$database = (isset($this->params['database'])) ? $this->params['database'] : null;
		$mode = (isset($this->params['mode'])) ? $this->params['mode'] : 0666;
		$err = '';
		$conn = sqlite_open($database, $mode, $err);
		if ($err) {
			throw new \Sugi\DatabaseException('Could not connect to the database with message ' . $err);
		}
		/*
		 * Could not connect, but sqlite_error will not be triggered.
		 * This could happen for example when there are open_basedir restriction in effect!
		 */
		if (!$conn) {
			throw new \Sugi\DatabaseException('Could not connect to the database');
		}

		$this->dbHandle = $conn;

		return $conn;
	}
	
	function _close()
	{
		sqlite_close($this->dbHandle);
		return true;
	}
	
	function _escape($item)
	{
		return sqlite_escape_string($item);
	}
	
	function _query($sql)
	{
		return sqlite_query($this->dbHandle, $sql, SQLITE_ASSOC, $this->_err);
	}

	function _fetch($res)
	{
		return sqlite_fetch_array($res, SQLITE_ASSOC);		
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
		return sqlite_single_query($this->dbHandle, $sql, true);
	}
	
	function _affected($res)
	{
		return sqlite_changes($this->dbHandle);
	}

	function _last_id()
	{
		return sqlite_last_insert_rowid($this->dbHandle);
	}

	function _free($res)
	{
		//sqlite_free_result($res); 
	}
	
	function _begin()
	{
		return false;
	}

	function _commit()
	{
		return false;
	}
	
	function _rollback()
	{
		return false;
	}
		
	function _error()
	{
		return $this->_err;
	}
}
