<?php

use Sugi\Form;
use Sugi\Ste;


include "common.php";

error_reporting(E_ALL);
ini_set('display_errors',1);


$form = new Form();
$form->addCheckbox("terms", "1" , 1)->rule("required", "Please agree with term of use");

$form->addSelect("test_select", "color" , array(
	"" => "-- Choose color --",
	1 => "Braun",
	2 => "Red",
	3 => "Blue",
))->rule("required", "Please choose a color");

$countries = array(
	"" => "-- Choose country --",
	"europe" => array(
		1 => "France",
		2 => "Bulgaria",
	),
	"asia" => array(
		3 => "China",
		4 => "Japan",
		5 => array("label" => "India", "class" => "test" , "id" => "test" )	,
));

$select = $form->addSelect("test_select2", "country" , $countries)->value(2)->rule("required", "Please choose a country");

//$select->getOption(3)->setSelected();
$select->value(3);
$select->getOption(3)->attribute('class','test');

$form->addMultipleSelect("test_mselect2", "mcountry" , $countries)->value(array(1,3))->rule("required", "Please choose a country");


$form->addMultipleSelect("test_mselect", "mcolor" , array(
	1 => "Braun",
	2 => "Red",
	3 => "Blue",
))->value(array(2));

$form->addRadio("test_radio", "hair" , array(
	1 => "Braun",
	2 => "Red",
	3 => "Blue",
))->value(3)->rule("required","Please choose a color");

$form->addCheckboxList("test_checkboxList", "hair" , array(
	1 => "Braun",
	2 => "Red",
	3 => "Blue",
))->value(array(3))->rule("required","Please choose a color");

$form->addTextarea("test_area", "Description")->value("asasa");
$form->addText("fname", "First Name:")->rule('regexp' , 'Името трябва да съдържа поне една голяма буква',  '/[A-Z]/');

$form->addText("email", "Email:")
	->rule('email' , 'should be valid email')
	->rule('callback' , 'should be \'email@domain.com\'', 'callmeback');

function callmeback($val) {	return $val == 'email@domain.com';}

$form->addText("url", "URL:")->rule('url' , 'should be a valid url');

$form->addUpload("file", "test")->rule("required"	);

$form->addSubmit("submit", "Send");
$form->addSubmit("submit2", "Send2");

$tpl = new Ste();
$tpl->load('ste/form.html');

$tpl->set('form', $form->toArray());
$tpl->set('data', var_export($form->data(),true));
//$tpl->set('form_code', htmlspecialchars($form));

if ($form->submitted()) {
	$data = $form->data();
	if ($data['test_area'] == '11') $form->addError('test_area', 'Could not be 11');   
	if ($form->valid()) {
		$tpl->hide('form');
	}
} 


header('Content-Type: text/html; charset=utf-8');
echo $tpl->parse();
