<?php namespace Sugi;
/**
 * File
 * Wrapper functions to ease file specific operations.
 * Directory operations are intensionally avoided.
 *
 * @package Sugi
 * @version 20121013
 */

class File
{
	/**
	 * Determine if the file exists.
	 *
	 * @param string $filename - filename with optional path
	 * @return boolean
	 */
	public static function exists($filename)
	{
		return is_file($filename);
	}

	/**
	 * Determine if the file can be opened for reading
	 *
	 * @param string $filename - filename with optional path
	 * @return boolean
	 */
	public static function readable($filename)
	{
		return static::exists($filename) && is_readable($filename);
	}

	/**
	 * Determine if the file is writable.
	 *
	 * @param string $filename - filename with optional path
	 * @return boolean
	 */
	public static function writable($filename)
	{
		return static::exists($filename) && is_writable($filename);
	}

	/**
	 * Trying to get the contents of the file.
	 * The file should exists and should be readable. If not default value will be returned.
	 *
	 * <code>
	 * 		// Get the contents of a file
	 *		$contents = File::get('foo/bar.txt');
	 *
	 *		// Get the contents of a file or return a default value if it doesn't exist
	 *		$contents = File::get('foo/bar.txt', 'Default Value');
	 * </code>
	 *
	 * @param string $filename
	 * @param string $default
	 * @return string
	 */
	public static function get($filename, $default = null)
	{
		return static::readable($filename) ? file_get_contents($filename) : $default;
	}

	/**
	 * Writes data in the file
	 *
	 * @param string $filename
	 * @param string $data
	 * @return integer - the number of bytes that were written to the file, or FALSE on failure.
	 */
	public static function put($filename, $data)
	{
		return file_put_contents($filename, $data, LOCK_EX);
	}

	/**
	 * Append given data to the file
	 *
	 * @param string $filename
	 * @param string $data
	 * @return integer - the number of bytes that were written to the file, or FALSE on failure.
	 */
	public static function append($filename, $data)
	{
		return file_put_contents($filename, $data, LOCK_EX | FILE_APPEND);
	}

	/**
	 * Changes file mode
	 *
	 * @param string $filename
	 * @param octal $mode
	 * @return boolean - TRUE on success or FALSE on failure. 
	 */
	public static function chmod($filename, $mode)
	{
		// intentionally check $filename is a file not a path since chmod works also on paths
		return /*preg_match('@^0[0-7]{3}$@', $mode) and*/ static::exists($filename) and chmod($filename, $mode);
	}

	/**
	 * Gets last modification time of the file
	 *
	 * @param string $filename
	 * @return integer, or FALSE on failure (e.g. file does not exists)
	 */
	public static function modified($filename)
	{
		return @filemtime($filename);
	}

	/** 
	 * Extracts file extension from the name of the file
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function ext($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
}
