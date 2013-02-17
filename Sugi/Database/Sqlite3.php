<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * SQLite3 driver for Sugi\Database class
 */
class Sqlite3 implements DriverInterface
{
	/**
	 * Cache of connection parameters
	 * @var array
	 */
	protected $params;

	/**
	 * SQLite3 connection handle
	 * @var object
	 */
	protected $dbHandle = null;

	function __construct(array $params)
	{
		if (!isset($params["handle"]) and empty($params["database"])) {
			throw new Exception("Database handle or database name required", "internal_error"); 
		}
		if (isset($params["handle"])) {
			$this->setHandle($params["handle"]);
		}

		$this->params = $params;
	}

	function open()
	{
		// if we have a SQLite database handle (connection) return it now
		if ($this->dbHandle) {
			return $this->dbHandle;
		}
		// Database parameter is mandatory
		if (empty($this->params["database"])) {
			throw new Exception("Database parameter is missing", "internal_error");
		}
		// Establish connection
		try {
			$this->dbHandle = new \SQLite3($this->params["database"]);
		} catch (\Exception $e) {
			throw new Exception($e->getMessage(), "connection_error");
		}

		return $this->dbHandle;
	}

	function close()
	{
		if (!$this->dbHandle) {
			return;
		}
		$this->dbHandle->close();
		$this->dbHandle = null;
	}

	function escape($item)
	{
		return $this->dbHandle->escapeString($item);
	}
	
	/**
	 * Executes query
	 * 
	 * @param string SQL statement
	 * @return object(SQLite3Result) or FALSE on failure
	 */
	function query($sql)
	{
		// additional warning is triggered
		return @$this->dbHandle->query($sql);
	}

	function fetch($res)
	{
		return $res->fetchArray(SQLITE3_ASSOC);
	}

	function affected($res)
	{
		return $this->dbHandle->changes();
	}

	function lastId()
	{
		return $this->dbHandle->lastInsertRowID();
	}
	
	function free($res) {
		return $res->finalize();
	}

	function error() {
		return $this->dbHandle->lastErrorMsg();
	}

	function getHandle()
	{
		return $this->dbHandle;
	}

	protected function setHandle($handle)
	{
		if (gettype($handle) != "object" or get_class($handle) != "SQLite3") {
			throw new Exception("Handle must be SQLite3 object", "internal_error");
		}
		$this->dbHandle = $handle;
	}
}
