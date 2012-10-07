<?php
/**
 * Firebug Console.log
 * 
 * @package Sugi
 * @version 20121007
 */
namespace Sugi\Logger;

class Console extends \Sugi\Logger
{
	protected function __construct($config = array()) {
		// override default format if no formating is given
		$this->_format = '<script>window.console && console.log("[{level}] {message}");</script>';

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
		return str_replace(array('"', "\r\n", "\r", "\n"), array('\"', ' ', ' ', ' '), $message);
	}
}
