<pre>
<?php

require 'Neon.php';

$data = Neon::decode('
# neon file - edit it now!

name: Homer

address:
	street: 742 Evergreen Terrace
	city: Springfield
	country: USA

phones: { home: 555-6528, work: 555-7334 }

children:
	- Bart
	- Lisa
	- Maggie


entity: Column(type="integer")

');

print_r($data);


echo '<hr>';

$neon = Neon::encode($data, Neon::BLOCK);

echo $neon;
