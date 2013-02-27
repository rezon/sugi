<?php
/**
 * @package  Sugi
 * @category tests
 * @author   Plamen Popov <tzappa@gmail.com>
 * @license  http://opensource.org/licenses/mit-license.php (MIT License)
 */

use Sugi\Hash;

class HashTests extends PHPUnit_Framework_TestCase
{
	
	public function testMake()
	{
		// hash is 60 chars
		$this->assertRegExp('#^\$2a\$[0-9]{2}\$\S{53}$#', Hash::make("SecretPassword"));
		// 2hashes are not equal
		$this->assertNotSame(Hash::make("SecretPassword"), Hash::make("SecretPassword"));
	}

	public function testCheckPassword()
	{
		$hash = Hash::make("SecretPassword");
		$this->assertTrue(Hash::check($hash, "SecretPassword"));
		$this->assertFalse(Hash::check($hash, "WrongPassword"));
	}
}
