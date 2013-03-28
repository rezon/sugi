<?php
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace Sugi\Config;

use \Sugi\Config\Neon;
use \SugiPHP\Config\LoaderInterface;
use \SugiPHP\Config\LocatorInterface;

class NeonLoader implements LoaderInterface
{
	protected $locator;

	public function __construct(LocatorInterface $locator = null)
	{
		$this->locator = $locator;
	}

	public function load($resource)
	{
		// check the extension. If it's not provided we'll add .neon
		if (pathinfo($resource, PATHINFO_EXTENSION) === "") {
			$resource .= ".neon";
		}

		$file = false;

		if ($this->locator) {
			// pass it to the locator (if set) and than include the file
			$file = $this->locator->locate($resource);
		} elseif (is_file($resource) and is_readable($resource)) {
			// check if the $resource is a real file and include it
			$file = $resource;
		}

		if ($file) {
			$contents = file_get_contents($file);
			return Neon::decode($contents);
		}

		return null;
	}
}
