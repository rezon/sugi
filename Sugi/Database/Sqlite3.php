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
	}

	function _open() {
		$database = (isset($this->params["database"])) ? $this->params["database"] : null;

		if (!$conn = new \SQLite3($database)) {
			throw new Exception("Connection failed");
		}
		$this->dbHandle = $conn;

		return $conn;
	}
	
	function _close() {
		if ($this->dbHandle->close()) {
			return true;
		}

		throw new \Sugi\DatabaseException($this->dbHandle->lastErrorMsg());
	}
	
	function _escape($item) {
		return $this->dbHandle->escapeString($item);
	}
	
	function _query($sql) {
		return $this->dbHandle->query($sql);
	}
	
	function _fetch($res) {
		return $res->fetchArray(SQLITE3_ASSOC);
	}
	
	function _affected($res) {
		return $this->dbHandle->changes();
	}
	
	function _last_id() {
		return $this->dbHandle->lastInsertRowID();
	}
		
	function _free($res) {
		return $res->finalize();
	}
	
	function _begin() {
		return FALSE;
	}

	function _commit() {
		return FALSE;
	}
	
	function _rollback() {
		return FALSE;
	}
	
	function _error() {
		return $this->dbHandle->lastErrorMsg();
	}
}
