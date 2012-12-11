<?php 
/**
 * STE demo
 *
 * @package Sugi
 * @version 12.12.11
 */

include "common.php";

use Sugi\Ste;

$tpl = new Ste();
$tpl->load('ste/index.html');
$tpl->set('title', 'STE');
$tpl->set(array('description' => 'Simple Template Engine', 'keywords' => 'php, template, engine, sugi'));
$tpl->set('home', array('link' => array('href' => 'index.php', 'title' => 'back')));
$tpl->loop('mainmenu', array(array('item' => 'one'), array('item' => 'two', 'current' => ' class="current"'), array('item' => 'three')));

$tpl->loop('block', array(
		array('li'=>'1', 'nested'=>array(array('li'=>'1.1'), array('li'=>'1.2',
			'nested2' => array(array('li'=>'1.2.1'), array('li'=>'1.2.2')),
		), array('li'=>'1.3'))), 
		array('li'=>'2', 'nested'=>array(array('li'=>'2.1'), array('li'=>'2.2'))),
		array('li'=>'3'), // FIXME: there should be no output for nested (currently it has)
		array('li'=>'4', 'nested'=>false),
	)
);

$tpl->register_function('test', function ($id, $html, $else = '') {
	if ($id >= 3) return $html;
	return $else;
});
$tpl->hide('hidden');

$tpl->set('inc', 'one.html'); // dynamic file inclusion
$tpl->set('bravo', 'Hurray!');

echo $tpl->parse();
