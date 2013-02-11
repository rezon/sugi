<?php namespace Sugi\Session;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Database driver for \Sugi\Session
 *
 * SQL script for the table sessions:
 * <code>
 *	CREATE TABLE IF NOT EXISTS sessions (
 *		session_id VARCHAR(40) NOT NULL PRIMARY KEY,
 *		session_time INTEGER NOT NULL,
 *		session_data TEXT,
 *		session_lifetime INTEGER NOT NULL DEFAULT 0
 *	);
 * </code>
 */
class Database extends \Sugi\Session
{
	protected $dbTable;
	protected $dbHandle;
	protected $updateInterval;
	protected $sessionTime = 0;
	protected $sessionData = "";
	protected $lifetimeChanged = false;

		
	public static function set_lifetime($days) {
		parent::set_lifetime($days);
		// we want anytime the lifetime is changed to update the info in the DB
		$this->lifetimeChanged = true;
	}

	protected function __construct($config = array()) {
		parent::__construct();
		
		if (empty($config["db"])) {
			throw new \Exception("Database based sessions needs a Database connection handle");
		}
		
		$this->dbHandle = $config["db"];
		$this->dbTable = (isset($config["table"])) ? $config["table"] : "sessions";
		$this->updateInterval = (isset($config["updateInterval"])) ? $config["updateInterval"] : 120;
	}
	
	protected function _open($save_path, $id) {
		return true;
	}


	protected function _read($id) {
		$session_id = "'" . $this->dbHandle->escape($id) . "'";
		$sql = "SELECT * FROM {$this->dbTable} WHERE session_id = {$session_id}";
		if ($row = $this->dbHandle->single($sql)) {
			$this->sessionData = (string) $row["session_data"];
			$this->sessionTime = $row["session_time"];
			return $this->sessionData;
		}
		return "";
	}
	
	protected function _write($id, $data) {
		$time = time();
		$session_id = "'" . $this->dbHandle->escape($id) . "'";
		$session_time = $time;
		$session_data = "'" . $this->dbHandle->escape($data) . "'";
		
		// If there was no session in the DB
		if (!$this->sessionTime) {
			$session_lifetime = (parent::$lifetime) ? (int) (parent::$lifetime) : 0;
			$sql = "INSERT INTO {$this->dbTable} (session_id, session_time, session_data, session_lifetime) VALUES ($session_id, $session_time, $session_data, $session_lifetime)";
			try {
				$res = $this->dbHandle->query($sql);
				return true;
			} 
			catch (\Sugi\Database\Exception $e) {
				// This will fail if there is Primary Key Violation
				// Try to update it
			}
		}

		// Session data is changed
		if (($this->sessionData != $data) OR ($this->lifetimeChanged)) {
			if ($this->lifetimeChanged) {
				$session_lifetime = (parent::$lifetime) ? (int) (parent::$lifetime) : 0;
			}
			else {
				$session_lifetime = "session_lifetime";
			}
			$sql = "UPDATE {$this->dbTable} SET session_time = $session_time, session_data = $session_data, session_lifetime = $session_lifetime WHERE session_id = $session_id";
			$this->dbHandle->query($sql);
		}
		// It's time to update it anyway
		elseif ($this->sessionTime < $time - $this->updateInterval) {
			$sql = "UPDATE {$this->dbTable} SET session_time = $session_time WHERE session_id = $session_id";
			$this->dbHandle->query($sql);
		}
		return true;
	}
	
	protected function _destroy($id) {
		if ($this->sessionTime) {
			$session_id = "'" . $this->dbHandle->escape($id) . "'";
			$sql = "DELETE FROM {$this->dbTable} WHERE session_id = $session_id";
			$this->dbHandle->query($sql);
		}
		return true;
	}
	
	protected function _close() {
		return true;
	}

	protected function _gc($maxLifetime) {
		$session_time = time() - $maxLifetime;
		$sql = "DELETE FROM {$this->dbTable} WHERE session_time + session_lifetime < $session_time";
		$res = $this->dbHandle->query($sql);
		return true;
	}
}
