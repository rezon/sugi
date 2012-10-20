<?php namespace Sugi;
/**
 * JS packer and compressor
 * Packs and compresses several .js files into a single .js file with unique name
 *
 * Usage:
 * <code>
 * 		// configuration
 * 		$config = array(
 * 			// save path, which should be publicly visible from web
 * 			'output_path' 	=> WWWPATH.'js/',
 * 			
 * 			// default include path, where your uncompressed and uncompiled files are
 * 			'input_path' 	=> APPPATH.'js/'
 * 		);
 *
 * 
 * 		$js = new Jspacker($config);
 * 
 * 		// one file
 * 		$js->add('jquery-1.5.2.js');
 * 		// several files at once
 * 		$css->add(array('jquery-1.8.2.min.js', 'common.js'));
 * 
 * @package Sugi
 * @version 20121019
 */
use Sugi\File;

/**
 * JSpacker class definition.
 */
class Jspacker
{
	private $_output_path = '';
	private $_input_path = '';
	private $_files = array();
	private $_lastmtime = 0;
	
	/**
	 * Jspacker constructor
	 * 
	 * @param array $config 
	 *        'output_path' - the directory where cached files will be created. This should be within your DOCUMENT ROOT and be visible from web. The server must have write permissions for this path. 
	 *        'input_path' the directory where actual uncompressed files are. This can be anywhere in the server.
	 */
	public function __construct($config = array())
	{
		if (isset($config['output_path'])) $this->_output_path = $config['output_path'];
		if (isset($config['input_path'])) $this->_input_path = $config['input_path'];
	}
	
	/**
	 * Add another js file(s)
	 * 
	 * @param mixed - array of files or a single file. The file can be absolute path or relative to the input_path
	 * @return boolean
	 */
	public function add($file)
	{
		if (is_array($file)) {
			$res = true;
			foreach ($file as $f) {
				if (!$this->append_file($f)) $res = false;
			}
			return $res;
		}
		return $this->append_file($file);
	}
	
	/**
	 * Packing all js files in one
	 * if $_compression is true the pack will be compressed
	 * 
	 * @param boolean $save
	 * @param boolean $compress - if this is set to FALSE it will not compress the file even if we had added file with compress option (good for debuging)
	 * @return string if $save is TRUE result will be the js filename otherwise js string
	 */
	public function pack($save = true, $compress = true)
	{
		// Generates a hash of all the files
		$hash = $this->_hash(array($this->_files, $this->_lastmtime));

		// Check there is a packed version
		$filename = ($compress) ? "_{$hash}.js" : "__{$hash}.js";
		$output = $this->_output_path.$filename;
		if (File::exists($output)) {
			return ($save) ? $filename : File::get($output);
		}

		// The output file does not exists
		$buffer = '';
		foreach ($this->_files as $file) {
			// if we don't want compression at all or the file is already minifed
			if (!$compress or (strpos($file, '.min.') !== false)) {
				$buffer .= File::get($file) . "\n";
			}
			else {
				$buffer .= $this->_compress(File::get($file)) . "\n";
			}
		}
		
		// Save combined file
		if ($save) {
			file_put_contents($output, $buffer);
			return $filename;
		}

		return $buffer;
	}

	/**
	 * Add one file in the pack
	 *
	 * @param  string $file
	 * @return boolean - FALSE if the file is not found
	 */
	public function append_file($file)
	{
		// Check the file is within default input path
		if ($mtime = File::modified($this->_input_path.$file)) {
			$file = $this->_input_path.$file; 
		}
		elseif (!$mtime = File::exists($file)) {
			trigger_error("file $file does not exists");
			return false;
		}
		// add a file to the pack
		$this->_files[] = $file;

		// last modified time of the pack will be the latest time
		$this->_lastmtime = max($mtime, $this->_lastmtime);
		
		return true;
	}

	/**
	 * Makes a hash from an array
	 *
	 * @param  array $array
	 * @return string
	 */
	protected function _hash($array)
	{
		$hash = '';
		foreach ($array as $value) {
			if (is_array($value)) {
				$hash .= $this->_hash($value);
			}
			else {
				$hash .= $value;
			}
		}
		return md5($hash);
	}

	/**
	 * Shrinks javascript
	 * 
	 * @param  string $buffer
	 * @return string
	 */
	private function _compress($buffer)
	{
		return \JShrink\Minifier::minify($buffer, array('flaggedComments' => false));
	}
}
