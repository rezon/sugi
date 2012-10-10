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
<<<<<<< HEAD
$tpl->set('home', array('link' => array('href' => '#', 'title' => 'top')));
=======
$tpl->set('home', array('link' => array('href' => 'index.php', 'title' => 'back')));
>>>>>>> 5675b147ba5bd59feda1700dca97f672b3c0d36c
echo $tpl->parse();
