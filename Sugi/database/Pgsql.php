<?php
/**
 * PgSQL extention for abstract Database class
 * 
 * @package Sugi
 * @version 20121005
 */
namespace Sugi\Database;

class Pgsql extends \Sugi\Database
{
	protected function _open() {
		$params = $this->_params;
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

		$this->_conn = $conn;
	}
	
	protected function _close() {
		if (pg_close($this->_conn)) {
			return true;
		}

		throw new \Sugi\DatabaseException(pg_last_error());
	}
	
	protected function _escape($item) {
		return pg_escape_string($this->_conn, $item);
	}
	
	protected function _query($sql) {
		return pg_query($this->_conn, $sql);
	}
	
	protected function _fetch($res) {
		return pg_fetch_assoc($res);
	}

	protected function _single($sql) {
		$res = $this->query($sql);
		$row = $this->fetch($res);
		$this->free($res);
		return $row;
	}
	
	protected function _single_field($sql) {
		$res = $this->query($sql);
		$row = pg_fetch_row($res);
		$this->free($res);
		return $row[0];
	}
	
	protected function _affected($res) {
		return pg_affected_rows($res);
	}
	
	protected function _last_id() {
		return false;
	}
		
	protected function _free($res) {
		pg_free_result($res);
	}
	
	protected function _begin() {
		return pg_query($this->_conn, 'BEGIN TRANSACTION');
	}

	protected function _commit() {
		return pg_query($this->_conn, 'COMMIT TRANSACTION');
	}
	
	protected function _rollback() {
		return pg_query($this->_conn, 'ROLLBACK TRANSACTION');
	}
	
	protected function _error($res) {
		return pg_last_error($res);
	}
	
	/*
	 * PgSQL Specific Functions
	 */
	public function next_val($sequence) {
		$sequence = $this->escape($sequence);
		return $this->single_field("SELECT nextval('$sequence') AS newid");
	}
}
