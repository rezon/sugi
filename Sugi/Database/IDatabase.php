<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Interface that all Sugi\Database drivers shoul implement
 */
interface IDatabase
{
	/**
	 * Connects to the database
	 * 
	 * @return resource handle to connection
	 */
	function open();
	
	/**
	 * Closes connection to the database
	 * 
	 * @return boolean - true on success
	 */
	function close();
	
	/**
	 * Escapes a string for use as a query parameter
	 * 
	 * @param string
	 * @return string
	 */
	function escape($item);
	
	/**
	 * Executes query
	 * 
	 * @param string SQL statement
	 * @return resource id
	 */
	function query($sql);
	
	/**
	 * Fetches row
	 * 
	 * @param resource id
	 * @return array if the query returns rows
	 */
	function fetch($res);
	
	/**
	 * Returns the number of rows that were changed by the most recent SQL statement (INSERT, UPDATE, REPLACE, DELETE)
	 * 
	 * @return integer
	 */
	function affected($res);
	
	/**
	 * Returns the auto generated id used in the last query
	 * 
	 * @return mixed
	 */
	function lastId();
	
	/**
	 * Frees the memory associated with a result
	 * 
	 * @param A result set identifier returned by query()
	 */
	function free($res);
	
	/**
	 * Begin Transaction
	 * 
	 * @return boolean
	 */
	function begin();
	
	/**
	 * Commit Transaction
	 * 
	 * @return boolean
	 */
	function commit();
	
	/**
	 * Rollback Transaction
	 * 
	 * @return boolean
	 */
	function rollback();
	
	/**
	 * Returns last error for given resource
	 * 
	 * @param resource id
	 * @return string
	 */
	function error();
}
