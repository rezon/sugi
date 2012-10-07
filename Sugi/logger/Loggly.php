<?php
/**
 * Loggly - sends logs to loggly.com
 * 
 * @package Sugi
 * @version 20121007
 */
namespace Sugi\Logger;

/**
 * Loggly class
 */
class Loggly extends \Sugi\Logger 
{
	
	protected $_curl = false;
	protected $_url = false;
	protected $_json = true;
	
	/**
	 * Class constructor
	 */
	public function __construct($config) {
		// overriding default format
		$this->_format = "{'level': '{level}', 'message': '{message}', 'time': '{Y}-{m}-{d} {H}:{i}:{s}', 'ip': '{ip}'}";
		// create it
		parent::__construct($config);
		// custom config
		if (isset($config['url'])) $this->_url = $config['url'];
		else throw new \Exception('url parameter is not provided in the config');
		if (isset($config['json'])) $this->_json = $config['json'];
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * cURL Initialization
	 * @return bool - always returns true 
	 */
	public function open() {
		// Check curl initialization
		if (!$this->_curl) {
			$this->_curl = curl_init($this->_url);
			curl_setopt($this->_curl, CURLOPT_AUTOREFERER, true);
			curl_setopt($this->_curl, CURLOPT_BINARYTRANSFER, false);
			curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->_curl, CURLOPT_HEADER, array("content-type: " . ($this->_json) ? "application/json" : 'text/plain'));
		}
		return true;
	}
	
	/**
	 * Close cURL
	 */
	public function close() {
		if ($this->_curl) {
			curl_close($this->_curl);
			$this->_curl = false;
		}
	}

	/**
	 * Sending message via cURL
	 * 
	 * @param string $msg
	 * @return mixed - logged or not
	 */
	protected function _log($msg) {
		/*
		 * Make sure we have initialized cURL 
		 */
		if (!$this->open()) {
			return false;
		}
		
		/*
		 * Sending the message
		 */
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $msg);
		$res = curl_exec($this->_curl);
		if ($res AND ($r = json_decode($res, true)) AND ($r['response'] == 'ok')) {
			return true;
		}

		return $res;
	}


	protected function _escape($message) {
		return str_replace("'", "\'", $message);
	}
}
