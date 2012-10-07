<?php
/**
 * MySQLi extention for abstract DataBase class
 *
 * @package Sugi
 * @version 20121005
 */
namespace Sugi\Database;

class Mysqli extends \Sugi\Database
{
	/**
	 * Default value is true;
	 * This can be manually set to false with mysqli_autocommit(false)
	 * or can temporary deactivate using begin() and restored with commit() or rollback() functions
	 */
	private $_autocommit = true;

	protected function _open() {
		$params = $this->_params;

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
		$this->_conn = $conn;
	}
	
	protected function _close() {
		if (mysqli_close($this->_conn)) {
			return true;
		}

		throw new \Sugi\DatabaseException(mysql_error());
	}
	
	protected function _escape($item) {
		return mysqli_real_escape_string($this->_conn, $item);
	}
	
	protected function _query($sql) {
		return mysqli_query($this->_conn, $sql, MYSQLI_STORE_RESULT);
	}
	
	protected function _fetch($res) {
		return mysqli_fetch_assoc($res);
	}

	protected function _single($sql) {
		$res = $this->query($sql);
		$row = $this->fetch($res);
		$this->free($res);
		return $row;
	}
	
	protected function _single_field($sql) {
		$res = $this->query($sql);
		$row = mysqli_fetch_row($res);
		$this->free($res);
		return $row[0];
	}
	
	protected function _affected($res) {
		return mysqli_affected_rows($this->_conn);
	}
	
	protected function _last_id() {
		return mysqli_insert_id($this->_conn);
	}
		
	protected function _free($res) {
		mysqli_free_result($res);
	}
	
	protected function _begin() {
		if (!$this->_autocommit) {
			return $this->mysqli_autocommit(false);
		}
		else {
			return true;
		}
	}

	protected function _commit() {
		$r = mysqli_commit($this->_conn);
		if (!$this->_autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	protected function _rollback() {
		$r = mysqli_rollback($this->_conn);
		if (!$this->_autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	protected function _error($res) {
		return mysqli_error($res);
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
	public function mysqli_autocommit($mode) {
		if (mysqli_autocommit($this->_conn, $mode)) {
			$this->_autocommit = $mode;
			return true;		
		}
		else {
			return false;
		}
	}
}
