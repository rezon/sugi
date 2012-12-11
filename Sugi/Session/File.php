<?php namespace Sugi\Session;
/**
 * @package Sugi
 * @version 12.12.11
 */

/**
 * File driver for \Sugi\Session
 */
class File extends \Sugi\Session
{
	private $path; // FALSE if the default path is supposed to be where the php.ini points
	
	protected function __construct($config = array()) {
		// If the path is not set, it will be set on session_open();
		$this->path = (isset($config['path'])) ? (rtrim($config['path'], '/\\') . DIRECTORY_SEPARATOR) : false; 
	}
	
	protected function _open($save_path, $id) {
		if ($this->path === false) {
			$this->path = rtrim($save_path, '/') . '/';
		}
		return true;
	}

	protected function _read($id) {
		return (string) \Sugi\File::get($this->path . 'sess_' . $id);
	} 
	
	protected function _write($id, $data) {
		return \Sugi\File::put($this->path . 'sess_' . $id, $data, 0666);
	}
	
	protected function _destroy($id) {
		return (\Sugi\File::delete($this->path . 'sess_' . $id));
	}
	
	protected function _close() {
		return true;
	}

	protected function _gc($maxlifetime) {
		foreach (glob("{$this->path}sess_*") as $filename) {
			if (\Sugi\File::modified($filename) + $maxlifetime < time()) {
				\Sugi\File::delete($filename);
			}
		}
		return true;
	}
}
