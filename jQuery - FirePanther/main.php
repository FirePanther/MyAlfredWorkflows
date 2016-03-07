<?php
if (trim($q) == '')
	showEmpty('jQuery Manual');

$doc = $_SERVER['HOME'].'/Documents/AlfredApp/Documentation/jquery';

// missing doc
if (!is_dir($doc)) {
	add('', 'Dokumentation nicht gefunden', 'Bitte führe mit CMD-Taste ausführen.');
	show();
	exit;
}

if (substr($q, 0, 1) <> '.' && substr($q, 0, 1) <> ' ' || strlen($q) == 1)
	die();

$q = trim(substr($q, 1));

$found = array();

$entries = $doc.'/docs/entries';
$s = glob($entries.'/*.json');

$q_l = strtolower($q);
foreach ($s as $f) {
	$p = 0;
	if (strlen($q_l) > 3) {
		$json = strtolower(file_get_contents($f));
		$p += substr_count($json, $q_l);
	}

	$name = basename($f, '.json');
	$name2 = str_replace('jQuery.', '', $name);
	$name_l = strtolower($name);
	if ($name == $q_l)
		$p += 1000;
	elseif (preg_match('~\b'.preg_quote($q_l).'~', $name_l))
		$p += 500 - (strlen($name2) - strlen($q_l));
	elseif (strpos($name_l, $q_l) !== false)
		$p += 100;

	if ($p)
		$found[] = array($p, $f, $name);
}
if (count($found)) {
	rsort($found);
	$num = 0;
	while (++$num < 5 && count($found)) {
		$get = array_shift($found);
		$json = file_get_contents($get[1]);
		$array = @json_decode($json, 1);
		if (!$array)
			continue;

		// icon
		$icon = mergeIcon('icon.png', $array['removed'] ? 'removed.png' : ($array['deprecated'] ? 'deprecated.png' : ''), $get[0] < 250 ? 'a:alpha:30' : '');

		// show
		add('file:///Users/iPanther/Documents/AlfredApp/Documentation/jquery/index.html#p='.$get[2], str_replace('jQuery.', '$.', $array['name']), ($array['removed'] ? 'REMOVED - ' : ($array['deprecated'] ? 'DEPRECATED - ' : '')).$array['desc'], $icon);
	}
	show();
}
