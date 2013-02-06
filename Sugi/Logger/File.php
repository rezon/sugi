<?php namespace Sugi\Logger;
/**
 * Logger_file - very simple file log.
 * 
 * @package Sugi
 * @version 13.02.05
 */


/**
 * Logger_file
 */
class File extends \Sugi\Logger
{
	/**
	 * Log filename
	 * 
	 * @var string
	 */
	private $_filename;
	
	/**
	 * File Permisions  - When log file is created change it's mode to $_filemode
	 * 
	 * @var octal
	 */
	private $_filemode = 0664;

	/**
	 * File resource handle
	 * 
	 * @var resource
	 */
	private $_handle = null;
		
	/**
	 * Class constructor
	 * 
	 * @param array $config - the key 'filename' is a must
	 */
	public function __construct($config = array()) {
		// overriding default format
		$this->_format = '[{Y}-{m}-{d} {H}:{i}:{s}] [{ip}] [{level}] {message}';
		// create it
		parent::__construct($config);
		// custom settings
		if (isset($config['filename'])) $this->_filename = $config['filename'];
		else throw new \Exception("filename parameter is not provided in the config");
		if (isset($config['filemode'])) $this->_filemode = $config['filemode'];
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->_close();
	}
	
	/**
	 * Opens or creates the file
	 * Note: The path must exists.
	 * 
	 * @return boolean - true if it is opened; false on failure 
	 */
	protected function _open() {
		// Check the file is open
		if (!$this->_handle) {
			/* 
			 * If the file does not exists we will create it 
			 */
			$create = !file_exists($this->_filename);

			/* 
			 * Open file in append mode 
			 */
			$this->_handle = fopen($this->_filename, 'a');

			if (!$this->_handle) {
				/*
				 * Failure
				 */
				return false;
			}

			/* 
			 * If we have now created the file we'll change the file permisions 
			 */
			if ($create) {
				chmod($this->_filename, $this->_filemode);
			}
		}
		return true;
	}
	
	/**
	 * Close the log file if it is open
	 */
	protected function _close() {
		if ($this->_handle AND fclose($this->_handle)) {
			$this->_handle = null;
		}
	}

	/**
	 * Logging to the file log
	 * One message per line
	 * 
	 * @param string $msg
	 * @return boolean - logged or not
	 */
	protected function _log($msg) {
		/*
		 * Make sure the file is opened
		 */
		if (!$this->_open()) {
			return false;
		}

		/*
		 * Write the message
		 */
		return (fwrite($this->_handle, $msg."\n") !== false);
	}

	protected function _escape($message) {
		return str_replace(array("\r\n", "\n", "\r"), ' ', $message);
	}

}
