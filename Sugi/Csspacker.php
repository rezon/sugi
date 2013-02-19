<?php namespace Sugi;
/** 
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\File;

/**
 * CSS packer and compressor
 * Packs and compresses several .css files in one .css file with unique name 
 * If .less file is given it will be processed with PHP less compiler
 *
 * Usage:
 * <code>
 * 		// configuration
 * 		$config = array(
 * 			// save path, which should be publicly visible from web
 * 			'output_path' 	=> WWWPATH.'css/',
 * 			
 * 			// default include path, where your uncompressed and uncompiled files are
 * 			'input_path' 	=> APPPATH.'css/'
 * 		);
 *
 * 
 * 		$css = new Csspacker($config);
 * 
 * 		// one file
 * 		$css->add('reset.css');
 * 		
 * 		// several files at once
 * 		$css->add(array('common.css', 'index.css'));
 *   	
 *   	// one less file which will be compiled as a single file, but MAY include
 *   	// other .less files with @import directive
 *   	$css->add('bootstrap.less');
 *   	
 *   	// several .less files which will be merged and then compiled, but not
 *   	// automatic inclusion is possible with @import !!
 *   	$css->add(array('reset.less', 'variables.less', 'grid.less'));
 * </code>
 * 
 * Note: 
 *		Inclusion of CSS files with @import are NOT automatically added to the package
 *  	if the input (master) file is .css 
 *   	.less files do import other .less files, but only the master file is checked 
 *    	against the modifications. This means that if you have changed something only 
 *     	in an imported file, the packer will not compile them.
 * Note:
 * 		No error will be triggered if @imported file does not exists. This is not a bug,
 *   	since we might want to import files in a default manner
 */
class Csspacker
{
	private $_output_path = '';
	private $_input_path = '';
	private $_files = array();
	private $_lastmtime = 0;
	
	/**
	 * CSSpacker constructor
	 * 
	 * @param array $config - 'output_path' the directory where cached files will be created. This should be within your DOCUMENT ROOT and be visible from web. The server must have write permissions for this path.; 'input_path' - the directory where actual uncompressed files are. This can be anywhere in the server.
	 */
	public function __construct(array $config = array())
	{
		if (isset($config['output_path'])) $this->_output_path = $config['output_path'];
		if (isset($config['input_path'])) $this->_input_path = $config['input_path'];
	}	

	/**
	 * Add another css file(s)
	 * 
	 * @param mixed array of files or a single file. The file can be absolute path or relative to the input_path
	 * @return boolean
	 */
	public function add($file)
	{
		if (is_array($file)) {
			return $this->append_files($file);
		}
		return $this->append_file($file);
	}

	
	/**
	 * Packing all css files in one
	 * if $_compression is true the pack will be compressed
	 * 
	 * @param boolean $save - if true we will return filename, otherwise we will return compressed string 
	 * @param boolean $compress - if false no compression will be made
	 * @return string if $save is true result will be the css filename otherwise string
	 */
	public function pack($save = true, $compress = true)
	{
		// Generates a hash of all the files
		$hash = $this->_hash(array($this->_files, $this->_lastmtime));

		// Check there is a packed version
		$filename = ($compress) ? "_{$hash}.css" : "__{$hash}.css";
		$output = $this->_output_path.$filename;
		if (File::exists($output)) {
			return ($save) ? $filename : File::get($output);
		}

		// The output file does not exists combine all files
		$buffer = $this->_combine($this->_files);

		// compress
		if ($compress) $buffer = $this->_compress($buffer);
		
		// Save combined and compressed file
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
		$this->_lastmtime  = max($mtime, $this->_lastmtime);
		
		return true;
	}

	/**
	 * Add some files in the pack (as a separate pack)
	 *
	 * @param  array  $files
	 * @return boolean - FALSE if any file is missing
	 */
	public function append_files($files = array())
	{
		$pack = array();
		foreach ($files as $file) {
			// Check the file is within default input path
			if ($mtime = File::modified($this->_input_path.$file)) {
				$pack[] = $this->_input_path.$file; 
			}
			elseif (!$mtime = File::exists($file)) {
				trigger_error("file $file does not exists");
				return false;
			}
			else {
				$pack[] = $file;
			}
			$this->_lastmtime = max($mtime, $this->_lastmtime);
		}

		$this->_files[] = $pack;

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
	 * Combines all added files in a string
	 * 
	 * @param  array  $files
	 * @param  boolean $level1
	 * @return string
	 */
	protected function _combine($files, $level1 = true)
	{
		$buffer = '';
		$less = false;
		foreach ($files as $file) {
			if (is_array($file)) {
				$buffer .= $this->_combine($file, false);
				continue;
			}
			if (substr($file, -5) === '.less') {
				// if one .less file is added as a single file
				// we will compress it immediately
				if ($level1) {
					$lessc = new \lessc();
					$buffer .= $lessc->compileFile($file);
					continue;
				}
				$less = true;
			}
			$buffer .= File::get($file) . "\n";
		}

		if ($less) {
			$lessc = new \lessc();
			$buffer = $lessc->compile($buffer) . "\n";
		}

		return $buffer;
	}

	/**
	 * Compressor
	 * 
	 * @var string $buffer - input (uncompressed string)
	 * @return string - compressed version of the input
	 */
	protected function _compress($buffer)
	{
		// Remove all comments
		$pattern = '!/\*[^*]*\*+([^/][^*]*\*+)*/!';
		$buffer = preg_replace($pattern, '', $buffer);
	
		// Remove new lines, tabs and some spaces
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", ' {', '} ', ';}', '; }'), array('',     '',   '',   '',   '{',  '}',  '}' , '}'), $buffer);

		// Remove multiple spaces
		$buffer = preg_replace(array('!\s+!', '!(\w+:)\s*([\w\s,#]+;?)!'), array(' ', '$1$2'), $buffer);

		return $buffer;
	}
}
