<?php namespace Sugi;
/**
 * @package Sugi
 * @version 12.11.21
 */

/**
 * Filters - a helper class which wraps a filter_var() function available in PHP >= 5.2
 */
class Filter
{
	/**
	 * Validates integer value
	 * 
	 * @param mixed $value - integer or string
	 * @param integer $min_range
	 * @param integer $max_range
	 * @param mixed $default_value - this is what will be returned if the filter fails 
	 * @return mixed
	 */
	public static function int($value, $min_range = false, $max_range = false, $default_value = false)
	{
		$options = array('options' => array());
		if (isset($default_value)) $options['options']['default'] = $default_value;
		if (!is_null($min_range) AND ($min_range !== false)) $options['options']['min_range'] = $min_range;
		if (!is_null($max_range) AND ($max_range !== false)) $options['options']['max_range'] = $max_range;
		// We realy DO NOT need to validate user inputs like 010 or 0x10
		// If in the code we use something like static::int(010) this is the 
		// same as static::int(8) - so it will pass and return 8
		// But if we read it from user input, a file etc, it should fail by default
		// example - right padding some currencies like 0010.00 USD   
		// $options['flags'] = FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX;
		return filter_var($value, FILTER_VALIDATE_INT, $options);
	}
	
	/**
	 * Validates string value
	 * 
	 * @param string $value
	 * @param integer $min_length
	 * @param mixed $max_length
	 * @param mixed $default_value
	 * @return mixed
	 */
	static function str($value, $min_length = 0, $max_length = false, $default_value = false)
	{
		$value = trim($value);
		if (!empty($min_length) AND (mb_strlen($value, 'UTF-8') < $min_length)) return $default_value;
		if (!empty($max_length) AND (mb_strlen($value, 'UTF-8') > $max_length)) return $default_value;
		return (string)$value; 
	}
	
	/**
	 * Validates string and is removing tags from it
	 * 
	 * @param string $value
	 * @param integer $min_length
	 * @param mixed $max_length
	 * @param mixed $default_value
	 * @return mixed
	 */
	static function plain($value, $min_length = 0, $max_length = false, $default_value = false)
	{
		$value = strip_tags($value);
		return static::str($value, $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validates URL
	 * Does not validate FTP URLs like ftp://example.com. It only accepts http or https
	 * http://localhost is also not valid since we want some user's url, not localhost
	 * http://8.8.8.8 is not accepted, it's IP, not URL
	 *  
	 * @param string $value - URL to filter
	 * @param mixed $default_value - return value if filter fails 
	 * @return mixed
	 */
	static function url($value, $default_value = false)
	{
		$protocol = 'http(s)?://'; // starting with http:// or https:// no more protocols are accepted
		$userpass = "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
		$domain = '([\w_-]+\.)+[\w_-]{2,}'; // at least x.xx
		$port = '(\:[0-9]{2,5})?';// starting with colon and folowed by 2 upto 5 digits
		$path = "(\/([\w%+\$_-]\.?)+)*\/?"; // almost anything
		$query = "(\?[a-z+&\$_.-][\w;:@/&%=+,\$_.-]*)?";
		$anchor = "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";		
		return (preg_match('~^'.$protocol.$userpass.$domain.$port.$path.$query.$anchor.'$~iu', $value)) ? $value : $default_value;
	}
	
	/**
	 * Validates email
	 * 
	 * @param string $value
	 * @param mixed $default_value - default value to return on validation failure
	 * @param bool $check_mx_record - check existance of MX record. If check fails default value will be returned
	 * @return mixed
	 */
	static function email($value, $default_value = false, $check_mx_record = false)
	{
		if (!$value = filter_var($value, FILTER_VALIDATE_EMAIL)) {
			return $default_value;
		}
		$dom = explode('@', $value);
		$dom = array_pop($dom);
		if (!static::url('http://'.$dom)) return $default_value;
		return (!$check_mx_record OR checkdnsrr($dom, 'MX')) ? $value : $default_value;
	}

	/**
	 * Validates skype names
	 * Skype Name must be between 6 and 32 characters. It must start with a letter and can contain only letters, numbers, full stop (.), comma (,), dash (-), underscore (_)
	 *  
	 * @param string $value - skype name to validate
	 * @param mixed $default_value - return value if filter fails
	 * @return mixed - string on success (value) or $default_value on failure
	 */
	static function skype($value, $default_value = false)
	{
		return (preg_match('~^[a-z]([a-z0-9-_,\.]){5,31}$~i', $value)) ? $value : $default_value;
	}

	/**
	 * Validates key existence in the given array
	 * 
	 * @param mixed $key
	 * @param array $array
	 * @param mixed $default_value
	 * @return mixed
	 */
	public static function key($key, $array, $default_value = null)
	{
		return (isset($array) and is_array($array) and array_key_exists($key, $array)) ? $array[$key] : $default_value;
	}

	/**
	 * Validates $_GET[$key] value
	 * 
	 * @param string $key - key parameter of $_GET
	 * @param mixed $default_value - return value if filter fails
	 * @return mixed - string on success ($_GET[$key] value) or $default_value on failure
	 */
	public static function get($key, $default_value = null)
	{
		return Filter::key($key, $_GET, $default_value);
	}

	/**
	 * Validates $_POST[$key] value
	 * 
	 * @param string $key - key parameter of $_POST
	 * @param mixed $default_value - return value if filter fails
	 * @return mixed - string on success ($_POST[$key] value) or $default_value on failure
	 */
	public static function post($key, $default_value = null)
	{
		return Filter::key($key, $_POST, $default_value);
	}

	/**
	 * Validates $_COOKIE[$key] value
	 * 
	 * @param string $key - key parameter of $_COOKIE
	 * @param mixed $default_value - return value if filter fails
	 * @return mixed - string on success ($_COOKIE[$key] value) or $default_value on failure
	 */
	public static function cookie($key, $default_value = null)
	{
		return Filter::key($key, $_COOKIE, $default_value);
	}

	
	/**
	 * Validates $_SESSION[$key] value
	 * 
	 * @param string $key - key parameter of $_SESSION
	 * @param mixed $default_value - return value if key is not found
	 * @return mixed - string on success ($_SESSION[$key] value) or $default_value on failure
	 */
	public static function session($key, $default_value = null)
	{
		return Filter::key($key, $_SESSION, $default_value);
	}
	
	/**
	 * Validate string from GET parameter - $_GET['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function get_str($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::str(static::get($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validate string from POST paramether - $_POST['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function post_str($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::str(static::post($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validate string from COOKIE - $_COOKIE['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function cookie_str($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::str(static::cookie($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validates plain text from GET paramether - $_GET['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function get_plain($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::plain(static::get($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validates plain text from POST paramether - $_POST['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function post_plain($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::plain(static::post($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validates plain text from COOKIE - $_COOKIE['key']
	 * 
	 * @param string $key
	 * @param integer $min_length
	 * @param mixed $max_length - integer or false when there is no limit
	 * @param mixed $default_value - default value will be returned when validation fails
	 * @return mixed
	 */
	static function cookie_plain($key, $min_length = 0, $max_length = false, $default_value = false)
	{
		return static::plain(static::cookie($key), $min_length, $max_length, $default_value);
	}
	
	/**
	 * Validate integer from GET parameter - $_GET['key']
	 * 
	 * @param string $key
	 * @param mixed $min_range - integer or false not to check
	 * @param mixed $max_range - integer or false when there is no limit
	 * @param mixed $default_value - integer will be returned when validation succeeds, or default value of failure
	 * @return mixed
	 */
	static function get_int($key, $min_range = false, $max_range = false, $default_value = false)
	{
		return static::int(static::get($key), $min_range, $max_range, $default_value);
	}

	/**
	 * Validate integer from POST parameter - $_POST['key']
	 * 
	 * @param string $key
	 * @param mixed $min_range - integer or false not to check
	 * @param mixed $max_range - integer or false when there is no limit
	 * @param mixed $default_value - integer will be returned when validation succeeds, or default value of failure
	 * @return mixed
	 */
	static function post_int($key, $min_range = false, $max_range = false, $default_value = false)
	{
		return static::int(static::post($key), $min_range, $max_range, $default_value);
	}
	
	/**
	 * Validate integer from COOKIE - $_COOKIE['key']
	 * 
	 * @param string $key
	 * @param mixed $min_range - integer or false not to check
	 * @param mixed $max_range - integer or false when there is no limit
	 * @param mixed $default_value - integer will be returned when validation succeeds, or default value of failure
	 * @return mixed
	 */
	static function cookie_int($key, $min_range = false, $max_range = false, $default_value = false)
	{
		return static::int(static::cookie($key), $min_range, $max_range, $default_value);
	}
}
