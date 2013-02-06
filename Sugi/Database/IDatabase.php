<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @version 13.02.06
 */

interface IDatabase
{
	/**
	 * Connects to the database
	 * 
	 * @return resource handle to connection
	 */
	function _open();
	
	/**
	 * Closes connection to the database
	 * 
	 * @return boolean - true on success
	 */
	function _close();
	
	/**
	 * Escapes a string for use as a query parameter
	 * 
	 * @param string
	 * @return string
	 */
	function _escape($item);
	
	/**
	 * Executes query
	 * 
	 * @param string SQL statement
	 * @return resource id
	 */
	function _query($sql);
	
	/**
	 * Fetches row
	 * 
	 * @param resource id
	 * @return array if the query returns rows
	 */
	function _fetch($res);
	
	/**
	 * Returns one (first) row
	 * 
	 * @param string SQL statement
	 * @return array
	 */
	function _single($sql);
	
	/**
	 * Returns first field in a first returned row
	 * 
	 * @param string SQL statement
	 * @return string
	 */
	function _single_field($sql);
	
	/**
	 * Returns the number of rows that were changed by the most recent SQL statement (INSERT, UPDATE, REPLACE, DELETE)
	 * 
	 * @return integer
	 */
	function _affected($res);
	
	/**
	 * Returns the auto generated id used in the last query
	 * 
	 * @return mixed
	 */
	function _last_id();
	
	/**
	 * Frees the memory associated with a result
	 * 
	 * @param A result set identifier returned by query()
	 */
	function _free($res);
	
	/**
	 * Begin Transaction
	 * 
	 * @return boolean
	 */
	function _begin();
	
	/**
	 * Commit Transaction
	 * 
	 * @return boolean
	 */
	function _commit();
	
	/**
	 * Rollback Transaction
	 * 
	 * @return boolean
	 */
	function _rollback();
	
	/**
	 * Returns last error for given resource
	 * 
	 * @param resource id
	 * @return string
	 */
	function _error();
}
