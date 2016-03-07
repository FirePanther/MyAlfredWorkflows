<?php
include '../workflows.php';
if (preg_match('~\?q=([^&\#]+)~', $q, $m)) {
	$query = $name = $m[1];
	if (strpos($q, '#') !== false)
		$name = urldecode(substr($q, strpos($q, '#')+1));
	$icon = icon('http://maps.googleapis.com/maps/api/staticmap?center='.$query.
		'&zoom=14&size=640x480&scale=2&language=de&sensor=false&maptype=roadmap'.
		'&markers=color:red%7C'.$query.'&format=png32', '.png', $name, 1);
	quicklook($icon);
}