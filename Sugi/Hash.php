<?php namespace Sugi;
/**
 * Hash class
 * 
 * @package Sugi
 * @version 12.11.22
 */

/**
 * Hash class- crypt and decrypt passwords
 * Safely store passwords in the DB.
 * Generates unique hash even for same passwords. The hash is 60 chars.
 * 
 * @example
 * <code>
 * //Generate unique hash for the password and store it in the DB:
 * $hash = Hash::make($_POST['password']);
 * // Now the hash will hold hash of the password along with its unique salt
 * 
 * // To check the password use:
 * if (Hash::check($hash, $_POST['password'])) {
 * 	echo "Access granted";
 * }
 * else {
 * 	echo "Wrong!";
 * }
 * </code>
 */
class Hash
{
	/**
	 * Algorithm to use: blowfish
	 */
	private static $algo = '$2a';

	/**
	 * Cost parameter for the blowfish to slow down the hashing algorithm
	 */
	private static $cost = '$10';


	/**
	 * Generates a hash
	 * 
	 * @param string $password
	 * @return string
	 */
	public static function make($password)
	{
		return crypt($password, static::$algo . static::$cost . '$' . static::unique_salt());
	}

	/**
	 * Compares a password against a hash
	 * 
	 * @param string $hash - password hash made with make() method
	 * @param string $password - password provided on login
	 * @return boolean
	 */
	public static function check($hash, $password)
	{
		$salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $salt);
		return ($hash === $new_hash);
	}

	/**
	 * Generates unique salt each time we use it
	 *
	 * @return string
	 */
	protected static function unique_salt()
	{
		return substr(sha1(mt_rand()), 0, 22);
	}
}
