<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Interface that all Sugi\Database drivers should implement
 */
interface IDatabase
{
	/**
	 * Constructors must accept only one array parameter
	 * @param array $array - connection parameters
	 * @throws \Sugi\Database\Exception If required parameters are not set
	 */
	function __construct(array $params);

	/**
	 * Establish a database connection
	 * @return resource handle to connection
	 * @throws \Sugi\Database\Exception
	 */
	function open();
	
	/**
	 * Closes connection to the database
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
	 * @return mixed - FALSE on query failure
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
	 * @return integer
	 */
	function affected($res);
	
	/**
	 * Returns the auto generated id used in the last query
	 * @return mixed
	 */
	function lastId();
	
	/**
	 * Frees the memory associated with a result
	 * @param A result set identifier returned by query()
	 */
	function free($res);

	/**
	 * Return a database handle
	 * @return object|null
	 */
	function getHandle();
	
	/**
	 * Returns last error for given resource
	 * @return string
	 */
	function error();
}
