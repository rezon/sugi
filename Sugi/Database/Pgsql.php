<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @version 13.02.06
 */

/**
 * PgSQL extention for Database class
 */
class Pgsql implements IDatabase
{
	protected $params;
	protected $dbHandle = null;
	private $res;


	public function __construct(array $config)
	{
		$this->params = $config;
	}

	function _open()
	{
		$params = $this->params;
		$conn_string = '';
		foreach($params as $key=>$value) {
			if ($key == 'pass') $key = 'password';
			else if ($key == 'database') $key = 'dbname';
			if ($conn_string) $conn_string .= ' ';
			$conn_string .= "{$key}={$value}";
		}
		if (!$conn = pg_connect($conn_string)) {
			throw new \Sugi\DatabaseException('Connection failed');
		}

		$this->dbHandle = $conn;
		return $conn;
	}
	
	function _close()
	{
		if (pg_close($this->dbHandle)) {
			return true;
		}

		throw new \Sugi\DatabaseException(pg_last_error());
	}
	
	function _escape($item)
	{
		return pg_escape_string($this->dbHandle, $item);
	}
	
	function _query($sql)
	{
		$this->res = pg_query($this->dbHandle, $sql);
		return $this->res;
	}
	
	function _fetch($res)
	{
		return pg_fetch_assoc($res);
	}

	function _single($sql)
	{
		$res = $this->query($sql);
		$row = $this->fetch($res);
		$this->free($res);
		return $row;
	}
	
	function _single_field($sql)
	{
		$res = $this->query($sql);
		$row = pg_fetch_row($res);
		$this->free($res);
		return $row[0];
	}
	
	function _affected($res)
	{
		return pg_affected_rows($res);
	}
	
	function _last_id()
	{
		return false;
	}
		
	function _free($res)
	{
		pg_free_result($res);
	}
	
	function _begin()
	{
		return pg_query($this->dbHandle, 'BEGIN TRANSACTION');
	}

	function _commit()
	{
		return pg_query($this->dbHandle, 'COMMIT TRANSACTION');
	}
	
	function _rollback()
	{
		return pg_query($this->dbHandle, 'ROLLBACK TRANSACTION');
	}
	
	function _error()
	{
		return pg_last_error($this->res);
	}
	
	/*
	 * PgSQL Specific Functions
	 */
	public function next_val($sequence) {
		$sequence = $this->escape($sequence);
		return $this->single_field("SELECT nextval('$sequence') AS newid");
	}
}
