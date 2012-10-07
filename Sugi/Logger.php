<?php
/**
 * Logger class
 *
 * Filter levels:
 * Filter level can be defined as 'all' or 'none', they can also have some sertain levels like
 * 'all -debug -info', which will log all messages except those with level 'debug' and 'info'
 *	or 
 * 'none +error', which will log only messages with level 'error'
 * Those levels, unlike majority (or even all) of currently available loggers, can be custom
 * defined. There are no hierarchy in the level names, thus can be anything you want. There is 
 * only one limitation - level name can have only alphanumeric characters and underscores.
 * Defaul log level is all and this is true even if you did not explicitly mension it in the filter.
 * To modify it you should add 'none' in the begining of the filter.
 *
 * @package Sugi
 * @version 20121007
 */
namespace Sugi;
use Sugi\Request;
include_once('Request.php');

abstract class Logger 
{
	/**
	 * @var array - all registered loggers
	 */
	protected static $_loggers = array();


	/**
	 * Create loggers with Logger specific files
	 */
	public static function __callStatic($name, $arguments) {
		// check requested logger type exists
		$name = ucfirst(strtolower($name));
		$class_name = "\Sugi\Logger\\$name";
		$class_file = dirname(__FILE__)."/Logger/{$name}.php";
		if (!class_exists($class_name)) {
			if (!file_exists($class_file)) {
				throw new \Exception("Call to undefined method Sugi\Logger::{$name}()");
			}
			include $class_file;
		}

		// checking configurations
		$config = array();
		if (!empty($arguments[0])) {
			$config = $arguments[0];
		}

		// create logger child
		$log = new $class_name($config);
		static::$_loggers[] = $log;

		return $log;
	}

	/**
	 * Sets the level filter. If used with no parameter it returns the current filter
	 * 
	 * @param  string $filter 
	 * @return string - returns the current filter
	 */
	public function filter($filter = null) {
		// set filter
		if (!is_null($filter)) {
			$arr = explode(' ', $filter);
			foreach ($arr as $f) {
				if (!$f = trim($f)) continue;
				// default action
				if ($f === 'all') {
					$this->_receive_all = true;
					$this->_levels = array();
				}
				elseif ($f === 'none') {
					$this->_receive_all = false;
					$this->_levels = array();
				}
				// implicit adding or removing levels
				else {
					if (strpos($f, '+') === 0) $l = true;
					elseif (strpos($f, '-') === 0) $l = false;
					else {
						throw new \Exception("level $f has no sign");
					}
					
					$f = trim($f, '+-');
					$this->_levels[$f] = $l;
				}
			}
		}

		$res = ($this->_receive_all) ? 'all' : 'none';
		foreach ($this->_levels as $name => $act) {
			$res .= ' ' . ($act ? '+' : '-') . $name;
		}

		return $res;
	}

	/**
	 * Sets the output format. If used with no parameter the function returns the current log format 
	 * 
	 * @param  string $format
	 * @return string
	 */
	public function format($format = null) {
		if (!is_null($format)) {
			$this->_format = $format;
		}

		return $this->_format;
	}

	/**
	 * Log some message
	 * 
	 * @param  string $message - the message to be logged
	 * @param  string $level - the level (which may be filtered)
	 * @return void
	 */
	public static function log($message, $level) {
		// for each registered logger
		foreach (static::$_loggers as $log) {
			$log->message($message, $level);
		}
	}

	/**
	 * Check current message level shold be logged
	 * 
	 * @param  string $current level received from Logger::log
	 * @param  array $levels  child accepted levels
	 * @param  bool $all does child accepts all levels by default
	 * @return bool
	 */
	protected static function _check_level($current, $levels, $all) {
		if (isset($levels[$current])) return $levels[$current];
		if (!$all) return false;

		return true;
	}

	/**
	 * Do message formating - replaces variables in format string
	 *  
	 * @param string $format
	 * @param string $message
	 * @param string $level
	 * @return string - formated message
	 */
	protected static function _fmt($format, $message, $level) {
		$format = str_replace('{message}', $message, $format);
		$format = str_replace('{level}', $level, $format);
		$format = str_replace('{ip}', Request::ip(), $format);
		$time = time();
		if (preg_match_all('#\{([a-zA-Z]?)\}#', $format, $matches)) {
			foreach ($matches[0] as $match) {
				$val = trim($match, '{}');
				$format = str_replace($match, date($val, $time), $format);
			}
		}
		return $format;
	}


	protected $_levels = array();
	protected $_receive_all = true;
	protected $_format = "{level}: {message}";
	
	/**
	 * Constructor
	 * @param array $config
	 */
	protected function __construct($config = array()) {
		if (isset($config['filter'])) $this->filter($config['filter']);
		if (isset($config['format'])) $this->format($config['format']);
	}

	/**
	 * Override this function in child classes if there are some specific 
	 * formating requirements for the message text - escaping, truncating, etc. 
	 * 
	 * @param  string $message original message
	 * @return string  escaped (formated) message
	 */
	protected function _escape($message) {
		return $message;
	}

	public function message($message, $level) {
		// check if the level is within accepted ones
		if (static::_check_level($level, $this->_levels, $this->_receive_all)) {
			$msg = $message;
			// pre-formating usually is used to escape some specific chars in the message
			$msg = $this->_escape($message);
			// actual formating
			$msg = static::_fmt($this->_format, $msg, $level);
			// do the logging
			$this->_log($msg);
		}
	}

	protected abstract function _log($msg);
}
