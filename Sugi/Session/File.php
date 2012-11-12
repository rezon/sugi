<?php namespace Sugi\Session;
/**
 * File driver for \Sugi\Session
 *
 * @package Sugi
 * @version 12.11.12
 */

// includes \Sugi\File
include_once dirname(__DIR__) . '/File.php';

class File extends \Sugi\Session
{
	private $_path; // FALSE if the default path is supposed to be where the php.ini points
	
	protected function __construct($params) {
		var_dump($params);
		// If the path is not set, it will be set on session_open();
		$this->_path = (isset($params['path'])) ? (rtrim($params['path'], '/\\') . DIRECTORY_SEPARATOR) : false; 
	}
	
	protected function _open($save_path, $id) {
		if ($this->_path === false) {
			$this->_path = rtrim($save_path, '/') . '/';
		}
		return true;
	}

	protected function _read($id) {
		return (string) \Sugi\File::get($this->_path . 'sess_' . $id);
	} 
	
	protected function _write($id, $data) {
		return \Sugi\File::put($this->_path . 'sess_' . $id, $data, 0666);
	}
	
	protected function _destroy($id) {
		return (\Sugi\File::delete($this->_path . 'sess_' . $id));
	}
	
	protected function _close() {
		return true;
	}

	protected function _gc($maxlifetime) {
		foreach (glob("{$this->_path}sess_*") as $filename) {
			if (\Sugi\File::modified($filename) + $maxlifetime < time()) {
				\Sugi\File::delete($filename);
			}
		}
		return true;
	}
}
