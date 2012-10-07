<?php
/**
 * How to safely store passwords in the DB.
 * Generates unique hash even for same passwords. The hash is 60 chars.
 * 
 * Usage:
 * <code>
 * 		//Generate unique hash for the password and store it in the DB:
 *   	$hash = Hash::make($_POST['password']);
 *    	// Now the hash will hold hash of the password along with its unique salt
 * 
 * 		// To check the password use:
 *   	if (Hash::check($hash, $_POST['password'])) {
 * 	  		echo "Access granted";
 *      }
 *      else {
 * 	    	echo "Wrong!";
 *      }
 * </code>
 * 
 * @package Sugi
 * @version 20121007
 */
namespace Sugi;

/**
 * Hash class
 */
class Hash {
	/**
	 * Algorithm we use: blowfish
	 */
	private static $algo = '$2a';

	/**
	 * Cost parameter for the blowfish to slow down the hashing algorithm
	 */
	private static $cost = '$10';

	/**
	 * Generating unique salt each time we use 
	 * for internal use 
	 */
	protected static function unique_salt() {
		return substr(sha1(mt_rand()), 0, 22);
	}

	// this will be used to generate a hash
	public static function make($password) {
		return crypt($password, self::$algo . self::$cost . '$' . self::unique_salt());
	}

	// this will be used to compare a password against a hash
	public static function check($hash, $password) {
		$salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $salt);
		return ($hash === $new_hash);
	}
}
