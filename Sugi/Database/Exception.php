<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Exception for Sugi\Database class.
 */
class Exception extends \Exception
{
	public $error_description = "";

	public function __construct($message, $error_description = "")
	{
		parent::__construct($message);
		$this->error_description = $error_description;
	}

	// public function __toString()
	// {
	// 	return "Database Exception: {$this->getMessage()}: {$this->getDescription()}";
	// }

	public function getDescription()
	{
		return $this->error_description;
	}
}
