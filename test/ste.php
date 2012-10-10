<?php 
namespace Sugi;
use Sugi\Ste;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../Sugi/Ste.php";
include_once "../Sugi/File.php";

$tpl = new Ste();
$tpl->load('ste/index.html');
$tpl->set('title', 'STE');
$tpl->set(array('description' => 'Simple Template Engine', 'keywords' => 'php, template, engine, sugi'));
$tpl->set('home', array('link' => array('href' => 'index.php', 'title' => 'back')));
$tpl->loop('mainmenu', array(array('item' => 'one'), array('item' => 'two', 'current' => ' class="current"'), array('item' => 'three')));
$tpl->set('topmenu', array('user' => 'tzappa')); // this will never happen
$tpl->loop('topmenu', array(array('user' => 'ivan'), array('user' => 'dragan')));
$tpl->hide('hideme');
$tpl->hide('topmenu');
$tpl->unhide('topmenu');
echo $tpl->parse();
