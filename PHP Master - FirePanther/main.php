<?php
if (trim($q) == '')
	showEmpty('PHP Manual');

$doc = $_SERVER['HOME'].'/Documents/AlfredApp/Documentation/php/';

// missing doc
if (!is_dir($doc)) {
	add('', 'Dokumentation nicht gefunden', 'Bitte führe mit CMD-Taste ausführen.');
	show();
	exit;
}

ignore_short($q);

$qe = trim(preg_replace('~[^a-z0-9\s]+~', ' ', strtolower($q)));
if (strstr($qe, ' '))
	$qe = explode(' ', $qe);
else
	$qe = array($qe);

$q_cache = $qe;
sort($q_cache);
$q_cache = implode(',', $q_cache);
get_cache('php'.filemtime(__FILE__).':'.$q_cache);

$found = array();

$docs = glob($doc.'*.html');
$qe_num = count($qe);
foreach ($docs as $d) {
	$n = basename($d, '.html');
	$docsrc = strtolower(file_get_contents($d));
	
	$hits = implode('-', $qe) == $n ? 5000 : 0;
	if (!$hits) {
		foreach ($qe as $h) {
			if (preg_match('~^'.$h.'~', $n))
				$hits += 800 - (strlen($n) - strlen($h))*10;
			elseif (preg_match('~\b'.$h.'\b~', $n))
				$hits += 500;
			elseif (preg_match('~\b'.$h.'~', $n))
				$hits += 400;
			elseif (strstr($n, $h))
				$hits += 100;
			$hits += substr_count($docsrc, $h);
		}
	}
	
	if ($hits) {
		$found[] = array($hits, $n);
	}
}

rsort($found);
while (count($found) > 5) {
	array_pop($found);
}
foreach ($found as $f) {
	### func, params, type, verinfo, desc1, desc2, paramdesc, returnvalue, deprecated, removed
	$a = phpfunc($f[1]);
	
	$icon = mergeIcon('icon.png', strpos($a['verinfo'], 'PHP 4') !== false ? '4.png' : '', strpos($a['verinfo'], 'PHP 5') !== false ? '5.png' : '', $a['removed'] ? 'removed.png' : ($a['deprecated'] ? 'deprecated.png' : ''), $f[0] < count($qe) * 400 ? 'a:alpha:30' : '');

	add('function.'.$f[1].'.php', $a['func'], ($a['removed'] ? 'REMOVED - ' : ($a['deprecated'] ? 'DEPRECATED - ' : '')).$a['type'].' '.$a['params'], $icon);
}

show('php'.filemtime(__FILE__).':'.$q_cache);

function phpfunc($func) {
	global $doc;
	$json = file_get_contents($doc.$func.'.html');
	$array = @json_decode($json, 1);

	return $array;
}