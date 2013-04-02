<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Cfg;
use Sugi\File;

class CfgTest extends PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		// @define('TESTFILE', __DIR__.'/file.txt');
		Cfg::configure(__DIR__);
	}

	public function tearDown()
	{
		// @unlink(PHPFILE);
	}

	public function testGet()
	{
		$this->assertNull(Cfg::get('testkey1'));
		$this->assertEquals('foo', Cfg::get('testkey1', 'foo'));
		$this->assertNull(Cfg::set('testkey1', 'bar'));
		$this->assertEquals('bar', Cfg::get('testkey1', 'foo'));
		$this->assertNull(Cfg::get('key1.subkey1'));
		$this->assertEquals('foo', Cfg::get('key1.subkey1', 'foo'));
	}

	public function testSet()
	{
		Cfg::set('key1', array('subkey1' => 'bar', 'subkey2' => 'foobar', 'subkey3' => array('sub' => 'one')));
		$this->assertInternalType('array', Cfg::get());
		$this->assertInternalType('array', Cfg::get('key1'));
		$this->assertInternalType('array', Cfg::get('key1.'));
		$this->assertEquals('bar', Cfg::get('key1.subkey1'));
		$this->assertNull(Cfg::get('key1.subkey1.subsubkey'));
		$this->assertEquals('one', Cfg::get('key1.subkey3.sub'));
		$this->assertEquals('foo', Cfg::get('key1.subkey99', 'foo'));
	}

	public function testFilePHP()
	{
		$php = "<?php return array('key1' => 'value1', 'key2' => array('subkey1' => 'subvalue1'), 'key3' => 'value3');";
		File::put(__DIR__."/test.php", $php);

		$this->assertNull(Cfg::get('test.key99'));
		$this->assertEquals('foo', Cfg::get('test.key99', 'foo'));
		$this->assertEquals('value1', Cfg::get('test.key1'));
		$this->assertInternalType('array', Cfg::get('test.key2'));
		$this->assertEquals('subvalue1', Cfg::get('test.key2.subkey1'));
		$this->assertNull(Cfg::get('test.key2.subkey1.subkey'));

		File::delete(__DIR__."/test.php");
	}

	public function testFileAlias()
	{
		$php = "<?php return array('key1' => 'value1', 'key2' => array('subkey1' => 'subvalue1'), 'key3' => 'value3');";
		File::put(__DIR__."/test.php", $php);

		$this->assertNull(Cfg::get('test.key99'));
		$this->assertEquals('foo', Cfg::get('test.key99', 'foo'));
		$this->assertEquals('value1', Cfg::get('test.key1'));
		$this->assertInternalType('array', Cfg::get('test.key2'));
		$this->assertEquals('subvalue1', Cfg::get('test.key2.subkey1'));
		$this->assertNull(Cfg::get('test.key2.subkey1.subkey'));

		File::delete(__DIR__."/test.php");
	}

	public function testFileJson()
	{
		$json = 
			'{
				"key1":	"value1",
				"key2": {
					"subkey1": "subvalue1"
				},
				"key3": "value3"
			}';
		File::put(__DIR__."/test2.json", $json);

		$this->assertNull(Cfg::get('test2.key99'));
		$this->assertEquals('foo', Cfg::get('test2.key99', 'foo'));
		$this->assertEquals('value1', Cfg::get('test2.key1'));
		$this->assertEquals('value1', Cfg::get('test2.key1', 'foo'));
		$this->assertInternalType('array', Cfg::get('test2.key2'));
		$this->assertEquals('subvalue1', Cfg::get('test2.key2.subkey1'));
		$this->assertNull(Cfg::get('test2.key2.subkey1.subsubkey'));

		File::delete(__DIR__."/test2.json");
	}

// 	public function testFileNeon()
// 	{
// 		$neon = "
// # neon file - edit it now!

// name: Homer

// address:
// 	street: 742 Evergreen Terrace
// 	city: Springfield
// 	country: USA

// phones: { home: 555-6528, work: 555-7334 }

// children:
// 	- Bart
// 	- Lisa
// 	- Maggie

// pets: [cat, dog]
// ";
// 		File::put("test3.neon", $neon);
// 		$this->assertEquals('Homer', Cfg::test3('name'));
// 		$this->assertEquals(array('cat','dog'), Cfg::test3('pets'));
// 		$this->assertEquals('555-7334', Cfg::test3('phones.work'));
// 		$this->assertEquals('Lisa', Cfg::test3('children.1'));
// 		File::delete("test3.neon");
// 	}

	public function testFileNotFound()
	{
		$this->assertNull(Cfg::get("test99"));
	}
}
