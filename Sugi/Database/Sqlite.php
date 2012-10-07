<?php
/**
 * SQLite extention for abstract Database class
 * 
 * @package Sugi
 * @version 20121005
 */
namespace Sugi\Database;

class Sqlite extends \Sugi\Database
{
	private $_err = '';
	
	protected function _open() {
		$database = (isset($this->_params['database'])) ? $this->_params['database'] : null;
		$mode = (isset($this->_params['mode'])) ? $this->_params['mode'] : 0666;
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

		$this->_conn = $conn;
	}
	
	protected function _close() {
		sqlite_close($this->_conn);
		return true;
	}
	
	protected function _escape($item) {
		return sqlite_escape_string($item);
	}
	
	protected function _query($sql) {
		return sqlite_query($this->_conn, $sql, SQLITE_ASSOC, $this->_err);
	}

	protected function _fetch($res) {
		return sqlite_fetch_array($res, SQLITE_ASSOC);		
	}
	
	protected function _single($sql) {
		$res = $this->unbuffered_query($sql);
		$row = $this->fetch($res);
		$this->free($res);
		return $row;
	}
	
	protected function _single_field($sql) {
		return sqlite_single_query($this->_conn, $sql, true);
	}
	
	protected function _affected($res) {
		return sqlite_changes($this->_conn);
	}

	protected function _last_id() {
		return sqlite_last_insert_rowid($this->_conn);
	}

	protected function _free($res) {
		//sqlite_free_result($res); 
	}
	
	protected function _begin() {
		return false;
	}

	protected function _commit() {
		return false;
	}
	
	protected function _rollback() {
		return false;
	}
		
	protected function _error($res) {
		return $this->_err;
	}
}

