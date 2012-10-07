<?php
/**
 * Log messages to the STDOUT.
 * Note:
 * 		Use this logger only for reference.
 * 
 * @package Sugi
 * @version 20121007
 */
namespace Sugi\Logger;

class Stdout extends \Sugi\Logger
{
	protected function __construct($config = array()) {
		// override default format if no formating is given
		$this->_format = '<div class="{level}">[{level}]: {message}</div>';

		// create it
		parent::__construct($config);
	}

	/**
	 * @param string $message
	 */
	protected function _log($msg) {
		echo $msg;
		return true;
	}

	protected function _escape($message) {
		return str_replace(array("\r\n", "\r", "\n"), '<br>', $message);
	}
}
