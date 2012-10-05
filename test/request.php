<?php
/**
 * Request
 *
 * @package Sugi
 * @version 20121004
 */
namespace Sugi;

include_once "../Sugi/Request.php";
include_once "../Sugi/URI.php";

echo "protocol() = " . Request::protocol() . '<br />';
echo "host() = " .  Request::host() . '<br />';
echo "base() = " .  Request::base() . '<br />';
echo "uri() = " . Request::uri() . '<br />';
echo "full() = " . Request::full() . '<br />';
echo "ip() = " . Request::ip() . '<br />';
echo "cli() = "; var_dump(Request::cli()); echo '<br />';
echo "ajax() = "; var_dump(Request::ajax()); echo '<br />';
