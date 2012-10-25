<?php
/**
 * Translations
 *
 * @package Sugi
 * @version 20121023
 */
namespace Sugi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../Sugi/Lang.php";
include_once "../Sugi/File.php";

Lang::configure(array('lang' => 'bg', 'path' => dirname(__FILE__).'/lang/'));
Lang::load('test');
echo Lang::get('test') . '<br />';
echo __('Hi, :user', array(':user' => 'Joe'));
