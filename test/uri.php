<?php namespace Sugi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../Sugi/URI.php";

echo "current() = " . URI::current() . '<br />';
echo "segments() = "; var_dump(URI::segments()); echo '<br />';
echo "segments('foo/bar/sugi') = "; var_dump(URI::segments('foo/bar/sugi')); echo '<br />';
echo "segment(1) = " .  URI::segment(1) . '<br />';
echo "segment(22) = "; var_dump(URI::segment(22)); echo '<br />';
echo "segment(23, 'defalutsegment') = "; var_dump(URI::segment(23, 'defaultsegment')); echo '<br />';
