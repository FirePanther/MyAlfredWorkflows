<?php
$inc = glob(dirname(__FILE__).'/zzz-includes-zzz/*.php');
foreach ($inc as $i)
	include $i;