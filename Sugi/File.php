<?php
/**
 * File
 * Wrapper functions to ease file specific operations
 *
 * @package Sugi
 * @version 20121003
 */
namespace Sugi;

class File
{

	/**
	 * Determine if a file exists.
	 *
	 * @param str $file - filename with optional path
	 * @return bool
	 */
	public static function exists($file) {
		return is_file($file);
	}

	/**
	 * Determine if the file can be opened for reading
	 *
	 * @param str $file - filename with optional path
	 * @return bool
	 */
	public static function readable($file) {
		return static::exists($file) && is_readable($file);
	}

	/**
	 * Determine if the file is writable.
	 *
	 * @param str $file - filename with optional path
	 * @return bool
	 */
	public static function writable($file) {
		return static::exists($file) && is_writable($file);
	}

	/**
	 * Try to get the contents of a file.
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
	 * @param str $file
	 * @param str $default
	 * @return str
	 */
	public static function get($file, $default = null) {
		return (static::readable($file)) ? file_get_contents($file) : $default;
	}
}
