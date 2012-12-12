<?php
/**
 * @package Sugi
 * @category tests
 * @version 12.12.12
 */
error_reporting(-1);
use Sugi\Config;
use Sugi\File;

class ConfigTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass()
	{
		// @define('TESTFILE', __DIR__.'/file.txt');
	}

	public function tearDown()
	{
		// @unlink(PHPFILE);
	}

	public function testGet()
	{
		$this->assertNull(Config::get('test'));
		$this->assertEquals('foo', Config::get('test', 'foo'));
		$this->assertNull(Config::set('test', 'bar'));
		$this->assertEquals('bar', Config::get('test', 'foo'));
		$this->assertNull(Config::get('key1.subkey1'));
		$this->assertEquals('foo', Config::get('key1.subkey1', 'foo'));
	}

	public function testSet()
	{
		Config::set('test', 'bar');
		Config::set('key1', array('subkey1' => 'bar', 'subkey2' => 'foobar', 'subkey3' => array('sub' => 'one')));
		$this->assertInternalType('array', Config::get());
		$this->assertInternalType('array', Config::get('key1'));
		$this->assertInternalType('array', Config::get('key1.'));
		$this->assertEquals('bar', Config::get('key1.subkey1'));
		$this->assertNull(Config::get('key1.subkey1.subsubkey'));
		$this->assertEquals('one', Config::get('key1.subkey3.sub'));
		$this->assertEquals('foo', Config::get('key1.subkey99', 'foo'));
	}

	public function testFilePHP()
	{
		$php = "<?php return array('key1' => 'value1', 'key2' => array('subkey1' => 'subvalue1'), 'key3' => 'value3');";
		File::put("test.conf.php", $php);

		$this->assertNull(Config::file('test', 'key99'));
		$this->assertEquals('foo', Config::file('test', 'key99', 'foo'));
		$this->assertEquals('value1', Config::file('test', 'key1'));
		$this->assertInternalType('array', Config::file('test', 'key2'));
		$this->assertEquals('subvalue1', Config::file('test', 'key2.subkey1'));
		$this->assertNull(Config::file('test', 'key2.subkey1.subkey'));

		File::delete("test.conf.php");
	}

	public function testFileAlias()
	{
		$php = "<?php return array('key1' => 'value1', 'key2' => array('subkey1' => 'subvalue1'), 'key3' => 'value3');";
		File::put("test.conf.php", $php);

		$this->assertNull(Config::test('key99'));
		$this->assertEquals('foo', Config::test('key99', 'foo'));
		$this->assertEquals('value1', Config::test('key1'));
		$this->assertInternalType('array', Config::test('key2'));
		$this->assertEquals('subvalue1', Config::test('key2.subkey1'));
		$this->assertNull(Config::test('key2.subkey1.subkey'));

		File::delete("test.conf.php");
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
		File::put("test2.json", $json);

		$this->assertNull(Config::test2('key99'));
		$this->assertEquals('foo', Config::test2('key99', 'foo'));
		$this->assertEquals('value1', Config::test2('key1'));
		$this->assertEquals('value1', Config::test2('key1', 'foo'));
		$this->assertInternalType('array', Config::test2('key2'));
		$this->assertEquals('subvalue1', Config::test2('key2.subkey1'));
		$this->assertNull(Config::test2('key2.subkey1.subsubkey'));

		File::delete("test2.json");
	}

	public function testFileNeon()
	{
		$neon = "
# neon file - edit it now!

name: Homer

address:
	street: 742 Evergreen Terrace
	city: Springfield
	country: USA

phones: { home: 555-6528, work: 555-7334 }

children:
	- Bart
	- Lisa
	- Maggie

pets: [cat, dog]
";
		File::put("test3.neon", $neon);
		$this->assertEquals('Homer', Config::test3('name'));
		$this->assertEquals(array('cat','dog'), Config::test3('pets'));
		$this->assertEquals('555-7334', Config::test3('phones.work'));
		$this->assertEquals('Lisa', Config::test3('children.1'));
		File::delete("test3.neon");
	}

	public function testFileNotFound()
	{
		$this->assertNull(Config::test99());
	}

}
