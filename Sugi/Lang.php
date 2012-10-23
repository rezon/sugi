<?php namespace Sugi;
/** 
 * Internationalization / Translation Class
 *
 * @example
 * <code>
 * 		Lang::
 * 		
 * 		// Display a translated message
 *    	echo Lang::trans('Hello World');
 *
 *		// With parameter replacement
 *   	echo Lang::trans('Hello, :user', array(':user' => $name));
 * </code>
 *  
 * @package Sugi
 * @version 20121023
 */
use Sugi\File;

/**
 * @todo: cache loaded files .po and .mo based on last modification time. Store them as array()
 */
class Lang
{
	/**
	 * Current language
	 * 
	 * @var string like: en, en-us, bg
	 */
	protected static $lang = 'en';

	/**
	 * Translation files search path
	 * 
	 * @var string
	 */
	protected static $path;

	/**
	 * Support for translation file extensions
	 * Note: checks for the file existence depends of the array order
	 * 
	 * @var array
	 */
	protected static $extentions = array('mo', 'po', 'php');

	/**
	 * Search order for translation files
	 * 
	 * @var array
	 */
	protected static $searchpaths = array('{path}/{lang}/{file}.{ext}', '{path}/{file}.{lang}.{ext}');

	/**
	 * All loaded translations
	 * 
	 * @var array
	 */
	protected static $lines = array();

	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public static function configure($config = array())
	{
		if (isset($config['lang'])) {
			static::language($config['lang']);
		}
		if (isset($config['path'])) {
			static::path($config['path']);
		}
	}

	/**
	 * Gets / sets current language
	 * 
	 * @param  string $lang - if provided sets new language
	 * @return string - current language
	 */
	public static function language($lang = null)
	{
		if (!is_null($lang)) {
			static::$lang = $lang;
		}
		return static::$lang;
	}

	/**
	 * Gets / sets the path where translations files are 
	 * @param string $path
	 * @return string - current search path
	 */
	public static function path($path = null)
	{
		if (!is_null($path)) {
			static::$path = rtrim($path, '/\\');
		}
		return static::$path;
	}

	/**
	 * Loads translation file
	 * 
	 * @param  string $file file should be without extension and within the path
	 * @return boolean
	 */
	public static function load($file)
	{
		// performance boost
		static $last_path = false;
		static $last_ext = false;
		if ($last_path and ($f = strtr($last_path, array('{file}' => $file))) and File::exists($f)) {
			if ($last_ext == 'mo') return static::loadMo($f);
			if ($last_ext == 'po') return static::loadPo($f);
			return static::loadPhp($f);
		}

		// search the language file
		foreach (static::$searchpaths as $search) {
			$path = strtr($search, array('{path}' => static::$path, '{lang}' => static::$lang, '{file}' => $file));
			foreach (static::$extentions as $ext) {
				$f = strtr($path, array('{ext}' =>$ext));
				if (File::exists($f)) {
					$last_path = strtr($search, array('{path}' => static::$path, '{lang}' => static::$lang, '{ext}' => $ext));
					$last_ext = $ext;
					if ($ext == 'mo') return static::loadMo($f);
					if ($ext == 'po') return static::loadPo($f);
					return static::loadPhp($f);
				}
			}
		}
		return false;
	}

	public static function get($key)
	{
		return !empty(static::$lines[$key]) ? static::$lines[$key] : $key;
	}

	/**
	 * Loads the php translations file
	 * 
	 * @param string $file
	 * @return boolean - FALSE on failure (file inclusion fails or file did not returned array)
	 */
	protected static function loadPhp($file) {
		if (($f = include($file)) === false) return false;
		if (!is_array($f)) return false;
		static::$lines = array_merge(static::$lines, $f);
		return true;
	}
	
	/**
	 * Loads the binary .mo translation file
	 *
	 * @param str $file - binary .mo file to load
	 * @return boolean
	 */
	protected static function loadMo($file) {
		$data = File::get($file);

		if ($data) {
			$header = substr($data, 0, 20);
			$header = unpack("L1magic/L1version/L1count/L1o_msg/L1o_trn", $header);
			extract($header);

			if ((dechex($magic) == '950412de' || dechex($magic) == 'ffffffff950412de') && $version == 0) {
				for ($n = 0; $n < $count; $n++) {
					$r = unpack("L1len/L1offs", substr($data, $o_msg + $n * 8, 8));
					$msgid = substr($data, $r["offs"], $r["len"]);

					$r = unpack("L1len/L1offs", substr($data, $o_trn + $n * 8, 8));
					$msgstr = substr($data, $r["offs"], $r["len"]);

					if (strpos($msgstr, "\000")) {
						$msgstr = explode("\000", $msgstr);
					}
					static::$lines[$msgid] = $msgstr;
				}
			}
		}
	}

	/**
	 * Loads the text .po translations file
	 *
	 * @param str $file - text .po file to load
	 * @return boolean
	 */
	protected static function loadPo($file) {
		$fp = fopen($file, 'r');
		if ($fp === false) return false;
		
		$type = 0;
		$translations = array();
		$translationKey = "";
		$header = "";

		do {
			$line = trim(fgets($fp));
			if ($line == "" || $line[0] == "#") {
				continue;
			}
			if (preg_match("/msgid[[:space:]]+\"(.+)\"$/i", $line, $regs)) {
				$type = 1;
				$translationKey = stripcslashes($regs[1]);
			}
			elseif (preg_match("/msgid[[:space:]]+\"\"$/i", $line, $regs)) {
				$type = 2;
				$translationKey = "";
			}
			elseif (preg_match("/^\"(.*)\"$/i", $line, $regs) && ($type == 1 || $type == 2 || $type == 3)) {
				$type = 3;
				$translationKey .= stripcslashes($regs[1]);
			}
			elseif (preg_match("/msgstr[[:space:]]+\"(.+)\"$/i", $line, $regs) && ($type == 1 || $type == 3) && $translationKey) {
				$translations[$translationKey] = stripcslashes($regs[1]);
				$type = 4;
			}
			elseif (preg_match("/msgstr[[:space:]]+\"\"$/i", $line, $regs) && ($type == 1 || $type == 3) && $translationKey) {
				$type = 4;
				$translations[$translationKey] = "";
			}
			elseif (preg_match("/^\"(.*)\"$/i", $line, $regs) && $type == 4 && $translationKey) {
				$translations[$translationKey] .= stripcslashes($regs[1]);
			}
			elseif (preg_match("/^\"(.*)\"$/i", $line, $regs) && $type == 6 && $translationKey) {
				$type = 6;
			}
			elseif (preg_match("/msgstr[[:space:]]+\"(.+)\"$/i", $line, $regs) && $type == 2 && !$translationKey) {
				$header .= stripcslashes($regs[1]);
				$type = 5;
			}
			elseif (preg_match("/msgstr[[:space:]]+\"\"$/i", $line, $regs) && !$translationKey) {
				$header = "";
				$type = 5;
			}
			elseif (preg_match("/^\"(.*)\"$/i", $line, $regs) && $type == 5) {
				$header .= stripcslashes($regs[1]);
			}
			else {
				unset($translations[$translationKey]);
				$type = 0;
				$translationKey = "";
			}
		} while (!feof($fp));
		fclose($fp);

		static::$lines = array_merge(static::$lines, $translations);
		return true;
	}
}


if (!function_exists('__')) {
	/**
	 * Translation / Internationalization function. The PHP function
	 *
	 *    __('Welcome back, :user', array(':user' => $name));
	 *
	 * @param string - text to translate
	 * @param array - values to replace in the translated text
	 * @return string - translated text
	 */
	function __($string, array $values = null) {

		$string = Lang::get($string);
		
		return empty($values) ? $string : strtr($string, $values);
	}
}
