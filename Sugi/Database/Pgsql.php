<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 */

/**
 * PgSQL extention for Sugi Database class
 */
class Pgsql implements IDatabase
{
	protected $params;
	protected $dbHandle = null;
	private $res;

	public function __construct(array $config)
	{
		$this->params = $config;

		if (!empty($config["handle"])) {
			if (gettype($config["handle"]) and get_class($config["handle"]) == "pgsql") {
				$this->dbHandle = $config["handle"];
			}
			else throw new Exception("Handle paramater must be of type pgsql");
		}
	}

	function open()
	{
		// if we have a pgsql database handle (connection) return it and ignore other settings
		if ($this->dbHandle) {
			return $this->dbHandle;
		}

		$params = $this->params;
		$conn_string = "";
		foreach($params as $key=>$value) {
			if ($key == "pass") $key = "password";
			else if ($key == "database") $key = "dbname";
			if ($conn_string) $conn_string .= " ";
			$conn_string .= "{$key}={$value}";
		}
		if (!$conn = \pg_connect($conn_string)) {
			throw new Exception("Connection failed");
		}

		$this->dbHandle = $conn;
		return $conn;
	}
	
	function close()
	{
		if (\pg_close($this->dbHandle)) {
			return true;
		}

		throw new Exception(\pg_last_error());
	}
	
	function escape($item)
	{
		return \pg_escape_string($this->dbHandle, $item);
	}
	
	function query($sql)
	{
		$this->res = \pg_query($this->dbHandle, $sql);
		return $this->res;
	}
	
	function fetch($res)
	{
		return \pg_fetch_assoc($res);
	}

	function affected($res)
	{
		return \pg_affected_rows($res);
	}
	
	function lastId()
	{
		return false;
	}
		
	function free($res)
	{
		\pg_free_result($res);
	}
	
	function begin()
	{
		return \pg_query($this->dbHandle, "BEGIN TRANSACTION");
	}

	function commit()
	{
		return \pg_query($this->dbHandle, "COMMIT TRANSACTION");
	}
	
	function rollback()
	{
		return \pg_query($this->dbHandle, "ROLLBACK TRANSACTION");
	}
	
	function error()
	{
		return \pg_last_error($this->res);
	}
}
