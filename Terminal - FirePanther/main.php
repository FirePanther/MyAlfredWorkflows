<?php
function commandMan($cmd) {
	exec('man '.$cmd, $man);
	$help = '';
	$next = 0;
	$lineNr = 0;
	foreach ($man as $line) {
		$lineNr++;
		if ($lineNr > 10) break;
		if (md5($line) == 'aad4bd03e41e7858f0ba7f202d970501') { // NAME
			$next = 1;
			continue;
		}
		if ($next || strstr($line, ' -- ')) {
			$help = $line;
			if (strstr($help, ' -- '))
				$help = substr($help, strpos($help, ' -- ')+4);
			elseif (strstr($help, ' - '))
				$help = substr($help, strpos($help, ' - ')+3);
			break;
		}
	}
	return $help;
}

$q_l = strtolower(rtrim($q));
$qname = preg_replace('~[^a-zA-Z0-9 _\-]+~', '_', $q_l);

$command = $q_l;
if (strstr($command, ' '))
	$command = substr($command, 0, strpos($command, ' '));

$doc = 'Befehl "'.$command.'", keine Dokumentation gefunden.';
if (strlen($command) && $help = commandMan($command)) {
	$doc = $help;
}

if ($q) {
	add($q, 'Ausführen: '.$q, $doc);

	$historyDir = $_SERVER['HOME'].'/Dropbox/Configs/AlfredApp/sync/terminal-history/';
	if (!is_dir($historyDir))
		@mkdir($historyDir, 0777, 1);

	$history = array();
	$s = glob($historyDir.$qname.'*');
	foreach($s as $f) {
		$a = @json_decode(file_get_contents($f), 1);
		$query = $a['q'];
		$num = $a['n'];
		$history[$num.'_'.filemtime($f)] = array($num, $query, filemtime($f));
	}
	krsort($history);
	foreach($history as $h) {
		add($h[1], $h[1], $h[0].'x ausgeführt, zuletzt am '.date('d.m.y - H:i', $h[2]).' Uhr', 'icon-history.png');
	}
} else {
	add('', 'Terminal öffnen');
}
show();