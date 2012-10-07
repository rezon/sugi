<?php
/**
 * SQLite3 extention for abstract Database class
 * 
 * @package Sugi
 * @version 20121005
 */
namespace Sugi\Database;

class Sqlite3 extends \Sugi\Database
{
	protected function _open() {
		$database = (isset($this->_params['database'])) ? $this->_params['database'] : null;

		if (!$conn = new \SQLite3($database)) {
			throw new \Sugi\DatabaseException('Connection failed');
		}
		$this->_conn = $conn;
	}
	
	protected function _close() {
		if ($this->_conn->close()) {
			return true;
		}

		throw new \Sugi\DatabaseException($this->_conn->lastErrorMsg());
	}
	
	protected function _escape($item) {
		return $this->_conn->escapeString($item);
	}
	
	protected function _query($sql) {
		return $this->_conn->query($sql);
	}
	
	protected function _fetch($res) {
		return $res->fetchArray(SQLITE3_ASSOC);
	}

	protected function _single($sql) {
		return $this->_conn->querySingle($sql, true);
	}
	
	protected function _single_field($sql) {
		return $this->_conn->querySingle($sql, false);
	}
	
	protected function _affected($res) {
		return $this->_conn->changes();
	}
	
	protected function _last_id() {
		return $this->_conn->lastInsertRowID();
	}
		
	protected function _free($res) {
		return $res->finalize();
	}
	
	protected function _begin() {
		return FALSE;
	}

	protected function _commit() {
		return FALSE;
	}
	
	protected function _rollback() {
		return FALSE;
	}
	
	protected function _error($res) {
		return $this->_conn->lastErrorMsg();
	}
}
