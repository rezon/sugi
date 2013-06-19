<?php
/**
 * @package Sugi
 */

namespace Sugi;

use Sugi\Logger;
use Sugi\Filter;

/**
 * Cron jobs
 * 
 * Cronjobs are configured via crontab files similar to *NIX style
 * for example one configuration file can look like this:
 * <code>
 * # min 	hour 	day 	month 	dayofweek 	command
 * *	*	*	*	*	foo.php
 * /2	*	*	*	*	bar.php
 * #30	*	*	*	*	bar.php
 * 0	17	*	*	*	bar.php
 * *	16	*	*	*	bar.php
 * 15,45	*	*	*	*	foobar.php
 * *	9-17	*	*	*	work-hours.php
 * 0	20-8,12	*	*	*	at-night-and-at-noon.php
 * </code>
 * 
 * Lines starting with # are considered comments and are not started
 * 
 * Cron entry file can be started via CLI or via web request (lynx, wget etc.)
 * It should be started every minute! Local cronjob is preferred
 * 
 * <code>
 * * 	*	*	*	*	/usr/bin/php /var/www/example.com/app/files/cron.php >/dev/null 2>&1
 * #or
 * *	*	*	*	*	wget -O /dev/null http://example.com/cron
 * or
 * *	*	*	*	*	lynx -source http://example.com/cron
 * or	
 * *	*	*	*	*	curl --silent --compressed http://example.com/cron
 * </code>
 */
class Cron
{
	protected $file;
	protected $timestamp;
	protected $time = array();
	protected $jobs = array();
	protected static $current_job;

	/**
	 * Creates an instance of Cron and executes it immediately
	 * 
	 * @param array $config
	 */
	public static function start($config = array())
	{
		$cron = new self($config);
		$cron->proceed();
	}
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		// current time
		$this->timestamp = time();

		// extracting time
		// mins, hours, days, months are for recursive tasks
		$this->time['min'] 		= (int) date('i', $this->timestamp);
		$this->time['mins'] 	= (int) floor($this->timestamp / 60);
		$this->time['hour'] 	= (int) date('H', $this->timestamp);
		$this->time['hours'] 	= (int) floor($this->timestamp / 3600);
		$this->time['day'] 		= (int) date('d', $this->timestamp);
		$this->time['days'] 	= (int) floor($this->timestamp / 86400);
		$this->time['month'] 	= (int) date('m', $this->timestamp);
		$this->time['months'] 	= (int) floor($this->timestamp / 2592000);
		$this->time['dow'] 		= (int) date('w', $this->timestamp);
		
		// configuration file
		if (empty($config['file'])) {
			throw new \Exception("Cron file not set");
		}

		// open the file and parse it
		$this->file = fopen($config['file'], 'r');
		$this->parse();
	}
	
	/**
	 * Start each cron task which has to start in that moment
	 */
	public function proceed()
	{
		// catch errors
		set_error_handler(array("\Sugi\Cron", 'error_handler'));
		
		// catch exceptions
		$exceptions = array();

		$jobs = $this->get_jobs();
		foreach ($jobs as $job) {
			$file = $job['command'];
			static::$current_job = $file;

			Logger::log("Cron job {$file} start", 'debug');
			
			// each job will be in try/except block. If one job fails others will run (hopefully).
			try {
				include $file;
				Logger::log("Cron job {$file} end", 'debug');
			} 
			catch (\Exception $e) {
				Logger::log("Cron job {$file} error message: {$e->getMessage()}", 'error');
				$exceptions[] = $e;
			}
		}
		static::$current_job = null;
		
		//restore old error handler
		restore_error_handler();

		// this will usually throw only first exception
		foreach ($exceptions as $e) {
			throw $e;
		}
	}
	
	/**
	 * Returns cron tasks that should start now
	 * 
	 * @return array
	 */
	public function get_jobs()
	{
		$return = array();
		foreach ($this->jobs as $job) {
			if ($job['start']) $return[] = $job;
		}
		return $return;
	}
	
	/**
	 * Returns all cron tasks
	 * 
	 * @return array
	 */
	public function get_all_jobs()
	{
		return $this->jobs;
	}
	
	/**
	 * Parses configuration file - cron jobs
	 * 
	 * @return boolean - false on empty job list OR when no crontab file available
	 */
	protected function parse()
	{
		// clear all jobs
		$this->jobs = array();
		
		// is the file loaded
		if (!$this->file) return false;
		
		// create regular exp
		$re = $this->build_regexp();
		
		while (($row = fgets($this->file)) !== false) {
			// check for commented rows
			if (!$row = trim($row) or strpos($row, '#') === 0) {
				continue;
			}
			if ($res = preg_match($re, $row, $matches) !== 0) {
				$matches['start'] = (
					    $this->check_dow($matches['dow'])
					and $this->check_month($matches['month'])
					and $this->check_day($matches['day'])
					and $this->check_hour($matches['hour'])
					and $this->check_min($matches['min'])
				);
				$this->jobs[] = array(
					'min'		=> $matches['min'],
					'hour'		=> $matches['hour'],
					'day'		=> $matches['day'],
					'month'		=> $matches['month'],
					'dow'		=> $matches['dow'],
					'command'	=> $matches['command'],
					'start'		=> $matches['start'],
				);
			}
			else {
				Logger::log("Invalid syntax in cron job $row", 'warning');
			}
		}
	}
	
	protected function check_($value, $min, $max, $time, $times)
	{
		// if we have lists
		$values = explode(',', $value);
		// for each instance of the list
		foreach($values as $val) {
			// start always
			if ($val == '*') return true;
			// start at some interval
			if (strpos($val, '-') > 0) {
				list($from, $to) = explode('-', $val);
				// like 8-20
				if (($from <= $to) and ($time >= $from) and ($time <= $to)) return true;
				// like 21-7
				if (($from > $to) and (($time >= $from) or ($time <= $to))) return true;
			}
			// exact match
			if ((Filter::int($val, $min, $max, false) !== false) and ($time == $val)) return true;
			// recursive
			if (($val = $this->check_recursive($val)) and ($times % $val === 0)) return true;
		}
		return false;
	}
	
	protected function check_month($value)
	{
		return $this->check_($value, 1, 12, $this->time['month'], $this->time['months']);
	}
	
	protected function check_day($value)
	{
		return $this->check_($value, 0, 31, $this->time['day'], $this->time['days']);
	}
	
	protected function check_hour($value)
	{
		return $this->check_($value, 0, 23, $this->time['hour'], $this->time['hours']);
	}
	
	protected function check_min($value)
	{
		return $this->check_($value, 0, 59, $this->time['min'], $this->time['mins']);
	}
	
	protected function check_dow($value)
	{
		$values = explode(',', $value);
		foreach($values as $val) {
			if ($val == '*') return true;
			if ((Filter::int($val, 0, 7, false) !== false) and ($this->time['dow'] == $val)) return true;
		}
		return false;
	}
	
	protected function check_recursive($value)
	{
		if (strpos($value, '/') === 0) return substr($value, 1);
		if (strpos($value, '*/') === 0) return substr($value, 2);
		return false;
	}
	
	protected function build_regexp()
	{
		$cols = array(
			'min' 		=> '[0-5]?\d',
			'hour' 		=> '[01]?\d|2[0-3]',
			'day'		=> '0?[1-9]|[12]\d|3[01]',
			'month'		=> '[1-9]|1[012]',
			'dow'		=> '[0-6]'
		);

		$regex = '';
		foreach ($cols as $field => $value) {
			$interval = "($value)(\-($value))?";
			$regex .= "(?<$field>($interval)(\,($interval))*|\*|\*?\/($value))\s+";
		}
		$regex .= '(?<command>.*)';
		
		return "~^$regex$~";
	}

	// error handler function
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		$errortype = array(
			E_ERROR             => "Error",
			E_WARNING           => "Warning",
			E_PARSE             => "Parse Error",
			E_NOTICE            => "Notice",
			E_CORE_ERROR        => "Core Error",
			E_CORE_WARNING      => "Core Warning",
			E_COMPILE_ERROR     => "Compile Error",
			E_COMPILE_WARNING   => "Compile Warning",
			E_USER_ERROR        => "User Error",
			E_USER_WARNING      => "User Warning",
			E_USER_NOTICE       => "User Notice",
			E_STRICT            => "Runtime Notice",
			E_RECOVERABLE_ERROR => "Catchable fatal error",
			E_DEPRECATED        => "Deprecated Notice",
			E_USER_DEPRECATED   => "User Dreprecated Notice",
		);

		$current_job = static::$current_job;
		Logger::log("Cron job {$current_job} {$errortype[$errno]}: $errstr in $errfile line $errline", 'error');

		// Don't execute PHP internal error handler
		return true;
	}	
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		if ($this->file) {
			fclose($this->file);
		}
	}
}
