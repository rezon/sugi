<?php
/**
 * Hash
 *
 * @package Sugi
 * @version 20121007
 */
namespace Sugi;

include_once "../Sugi/Hash.php";

echo Hash::make('pass'); echo '<br/>';
echo $hash = Hash::make('pass'); echo '<br/>';

var_dump(Hash::check($hash, 'pass'));  echo '<br/>';
var_dump(Hash::check($hash, 'PaSS'));  echo '<br/>';

