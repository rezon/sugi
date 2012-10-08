<?php
/**
 * File
 * Wrapper functions to ease file specific operations
 *
 * @package Sugi
 * @version 20121008
 */
namespace Sugi;

class File
{

	/**
	 * Determine if the file exists.
	 *
	 * @param string $file - filename with optional path
	 * @return bool
	 */
	public static function exists($file) {
		return is_file($file);
	}

	/**
	 * Determine if the file can be opened for reading
	 *
	 * @param string $file - filename with optional path
	 * @return bool
	 */
	public static function readable($file) {
		return static::exists($file) && is_readable($file);
	}

	/**
	 * Determine if the file is writable.
	 *
	 * @param string $file - filename with optional path
	 * @return bool
	 */
	public static function writable($file) {
		return static::exists($file) && is_writable($file);
	}

	/**
	 * Trying to get the contents of the file.
	 * The file sholud exists and should be readable. If not default value will be returned.
	 *
	 * <code>
	 * 		// Get the contents of a file
	 *		$contents = File::get('foo/bar.txt');
	 *
	 *		// Get the contents of a file or return a default value if it doesn't exist
	 *		$contents = File::get('foo/bar.txt', 'Default Value');
	 * </code>
	 *
	 * @param string $file
	 * @param string $default
	 * @return string
	 */
	public static function get($file, $default = null) {
		return static::readable($file) ? file_get_contents($file) : $default;
	}

	/**
	 * Gets last modification time of the file
	 *
	 * @param string $file
	 * @return int, or false on failure (eg. file does not exists)
	 */
	public static function modified($file) {
		return @filemtime($file);
	}

	/** 
	 * Extracts file extension from a file
	 *
	 * @param string $file
	 * @return string
	 */
	public static function ext($file) {
		return pathinfo($file, PATHINFO_EXTENSION);
	}
}
