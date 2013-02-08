<?php namespace Sugi\Database;
/**
 * @package Sugi
 * @author Plamen Popov <tzappa@gmail.com>
 */

/**
 * Exception class for Sugi Database
 */
class Exception extends \Exception
{
	public function __construct($message, $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function __toString()
	{
		$code = $this->code;
		switch($this->code) {
			case E_USER_ERROR: 
				$code = "User Error";
				break;
			case E_USER_NOTICE:
				$code = "User Notice";
				break;
			case E_USER_WARNING:
				$code = "User Warning";
				break; 
			default: 
				$code = $this->code;
		};
		$file = $this->getFile(); 
		$line = $this->getLine();
		$trace = $this->getTrace();
		$stripped = "";
		$num = 0;
		foreach($trace as $backtrace) {
			if (!empty($backtrace["file"]) AND ($backtrace["file"] !== __FILE__)) {
				$stripped .= "#{$num} {$backtrace["file"]} ({$backtrace["line"]})\n";
				$num++;
			}
		}
		$trace = print_r($stripped, TRUE);
		return __CLASS__ . ": [{$code}]: {$this->message} in {$file} ({$line}):\n$stripped";
	}
}
