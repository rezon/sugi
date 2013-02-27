<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\File;

class FileTest extends PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		@define('TESTFILE', __DIR__.'/file.txt');
	}

	public function tearDown()
	{
		@unlink(TESTFILE);
	}

	public function testExists()
	{
		$this->assertFileNotExists(TESTFILE);
		$this->assertFalse(File::exists(TESTFILE));
		file_put_contents(TESTFILE, 'Hello World');
		$this->assertFileExists(TESTFILE);
		$this->assertTrue(File::exists(TESTFILE));
		$this->assertFalse(File::exists(__DIR__)); // this is a path!
	}

	public function testIsReadable()
	{
		$this->assertTrue(File::readable(__FILE__));
		$this->assertFalse(File::readable(TESTFILE));
		file_put_contents(TESTFILE, 'Hello World');
		$this->assertTrue(File::readable(TESTFILE));
	}

	public function testExtReturnsFileExtension()
	{
		$this->assertEquals('php', File::ext(__FILE__));
		$this->assertEquals('php2', File::ext(__FILE__.'2'));
		$this->assertEquals('', File::ext(__DIR__));
	}

	public function testGet()
	{
		$this->assertNull(File::get(TESTFILE));
		$this->assertEquals('default', File::get(TESTFILE, 'default'));
		file_put_contents(TESTFILE, 'Hello World');
		$this->assertEquals('Hello World', File::get(TESTFILE));
		$this->assertNull(File::get(__DIR__)); // cannot get directory
	}

	public function testPut()
	{
		$this->assertEquals(11, File::put(TESTFILE, 'Hello World'));
		$this->assertFileExists(TESTFILE);
		$this->assertEquals('Hello World', file_get_contents(TESTFILE));
		$this->assertFalse(File::put(__DIR__, 'test')); // cannot put in directory
	}

	public function testPutOnWriteProtectedFiles()
	{
		file_put_contents(TESTFILE, 'foo');
		chmod(TESTFILE, 0444);
		$this->assertFalse(File::put(TESTFILE, 'Hello World'));
		$this->assertEquals('foo', file_get_contents(TESTFILE));
	}

	public function testAppend()
	{
		file_put_contents(TESTFILE, 'Hello');
		$this->assertEquals(6, File::append(TESTFILE, ' World'));
		$this->assertEquals('Hello World', file_get_contents(TESTFILE));
		$this->assertFalse(File::append(__DIR__, ' World'));
	}


	public function testAppendOnWriteProtectedFiles()
	{
		file_put_contents(TESTFILE, 'Hello');
		chmod(TESTFILE, 0444);
		$this->assertFalse(File::append(TESTFILE, ' World'));
		$this->assertEquals('Hello', file_get_contents(TESTFILE));
	}

	public function testAppendOnNonExistingFile()
	{
		$this->assertEquals(6, File::append(TESTFILE, ' World'));
		$this->assertEquals(' World', file_get_contents(TESTFILE));
	}

	public function testDelete()
	{
		$this->assertTrue(File::delete(TESTFILE));
		file_put_contents(TESTFILE, 'Hello World');
		$this->assertTrue(File::delete(TESTFILE));
		$this->assertFalse(file_exists(TESTFILE));
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 */	
	public function testChmod()
	{
		file_put_contents(TESTFILE, 'Hello World');
		$this->assertTrue(File::chmod(TESTFILE, 0444));
		file_put_contents(TESTFILE, 'foo');
	}

	public function testChmodFailures()
	{
		$this->assertFalse(File::chmod(TESTFILE, 0444));
		$this->assertFalse(File::chmod(__DIR__, 0775));
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 */	
	public function testPutWithChmod()
	{
		$this->assertEquals(11, File::put(TESTFILE, 'Hello World', 0444));
		$this->assertEquals('Hello World', file_get_contents(TESTFILE));
		file_put_contents(TESTFILE, 'foo');
	}
}
