<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 */

/**
 * SQLite3 extention for Sugi Database class
 */
class Sqlite3 implements IDatabase
{
	protected $params;
	protected $dbHandle = null;

	public function __construct(array $config)
	{
		$this->params = $config;

		if (!empty($config["handle"])) {
			if (gettype($config["handle"]) and get_class($config["handle"]) == "SQLite3") {
				$this->dbHandle = $config["handle"];
			}
			else throw new Exception("Handle paramater must be of type SQLite3");
		}
	}

	function open() {
		// if we have a SQLite3 database handle (connection) return it and ignore other settings
		if ($this->dbHandle) {
			return $this->dbHandle;
		}

		$database = (isset($this->params["database"])) ? $this->params["database"] : null;

		if (!$conn = new \SQLite3($database)) {
			throw new Exception("Connection failed");
		}
		$this->dbHandle = $conn;

		return $conn;
	}
	
	function close() {
		if ($this->dbHandle->close()) {
			return true;
		}

		throw new \Sugi\DatabaseException($this->dbHandle->lastErrorMsg());
	}
	
	function escape($item) {
		return $this->dbHandle->escapeString($item);
	}
	
	function query($sql) {
		return $this->dbHandle->query($sql);
	}
	
	function fetch($res) {
		return $res->fetchArray(SQLITE3_ASSOC);
	}
	
	function affected($res) {
		return $this->dbHandle->changes();
	}
	
	function lastId() {
		return $this->dbHandle->lastInsertRowID();
	}
		
	function free($res) {
		return $res->finalize();
	}
	
	function begin() {
		return FALSE;
	}

	function commit() {
		return FALSE;
	}
	
	function rollback() {
		return FALSE;
	}
	
	function error() {
		return $this->dbHandle->lastErrorMsg();
	}
}
