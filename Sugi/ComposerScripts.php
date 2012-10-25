<?php namespace Sugi;
/**
 * Composer Hooks
 *
 * @package Sugi
 * @version 20121025
 */

use Composer\Script\Event;

class ComposerScripts
{
	public static function preInstall(Event $event)
	{
		$composer = $event->getComposer();
		if (static::confirm("Start installation?")) {
			// do stuff

		} 
		else {
			exit("Aborted\n");
		}
	}

	public static function postInstall(Event $event)
	{
		$composer = $event->getComposer();
		// do stuff

		echo "Installation completed\n";
	}

	public static function preUpdate(Event $event)
	{
		$composer = $event->getComposer();
		if (static::confirm("Start update?")) {
			// do stuff

		}
		else {
			exit("Aborted\n");
		}
	}

	public static function postUpdate(Event $event)
	{
		$composer = $event->getComposer();
		// do stuff

		echo "\nUpdate completed\n";
	}

	public static function postPackageInstall(Event $event)
	{
		$installedPackage = $event->getOperation()->getPackage();
		// do stuff
		$name = $installedPackage->getPrettyName();
		if ($name == 'twitter/bootstrap') {
			echo "Twitter Bootstrap installed\n";
		}
		else {
			echo "Package $name installed\n";
		}
	}

	public static function postPackageUpdate(Event $event)
	{
		$updatedPackage = $event->getOperation()->getPackage();
		// do stuff
		$name = $installedPackage->getPrettyName();
		if ($name == 'twitter/bootstrap') {
			echo "Twitter Bootstrap udapted\n";
		}
		else {
			echo "Package $name updated\n";
		}
	}

    public static function confirm($message)
    {
        echo "$message [ Yes | no ] ";
        return strncasecmp(trim(fgets(STDIN)), 'n', 1);
    }
}
