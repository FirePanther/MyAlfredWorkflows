<?php
$weekdays = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');

if (!$q) {
	// no param, show current time
	$title = time();
	$sub = date('d.m.Y - H:i:s');
} elseif (preg_match("~^\d+\.?\d+$~", $q)) {
	// timestamp to date
	$title = time2date($q);
	$sub = $q;
} else {
	// date to timestamp
	$months = array(
		'jan',
		'feb',
		'm.{1,3}r',
		'apr',
		'mai',
		'jun',
		'jul',
		'aug',
		'sep',
		'o(k|c)t',
		'nov',
		'de(z|c)'
	);
	foreach ($months as $index => $month) {
		$monNr = $index + 1;
		if ($monNr < 10)
			$monNr = '0'.$monNr;
		$q = preg_replace('~\s*'.$month.'[a-zA-Z]*\s*~', '.'.$monNr.'.', $q);
	}
	$q = preg_replace('~[/.-]\.~', '.', $q);
	
	# format day
	if (preg_match('~(?:^|\D)(\d{1,2})\s*[.-/]\s*(\d{1,2})\s*[.-/]\s*(\d{4})(?:\D|$)~', $q, $m))
		$day = formatDay($m[1], $m[2], $m[3]);
	elseif (preg_match('~(?:^|\D)(\d{4})\s*[.-/]\s*(\d{1,2})\s*[.-/]\s*(\d{1,2})(?:[^\d:]|$)~', $q, $m))
		$day = formatDay($m[3], $m[2], $m[1]);
	elseif (preg_match('~(?:^|\D)(\d{1,2})\s*[.-/]\s*(\d{1,2})\s*[.-/]\s*(\d{2})(?:[^\d:]|$)~', $q, $m))
		$day = formatDay($m[1], $m[2], $m[3]);
	elseif (preg_match('~(?:^|\D)(\d{2})\s*[.-/]\s*(\d{1,2})\s*[.-/]\s*(\d{1,2})(?:[^\d:]|$)~', $q, $m))
		$day = formatDay($m[3], $m[2], $m[1]);
	else
		debug('Konnte den Tag nicht ermitteln.');
	
	# format time
	if (preg_match('~(?:^|\D)(\d{1,2})\s*:\s*(\d{1,2})\s*(?:\(|:)(\d{1,2})(?:\D|$)~', $q, $m)) // with seconds
		$time = ($m[1] < 10 ? '0' : '').round($m[1]).':'.($m[2] < 10 ? '0' : '').round($m[2]).':'.($m[3] < 10 ? '0' : '').round($m[3]);
	elseif (preg_match('~(?:^|\D)(\d{1,2})\s*:\s*(\d{1,2})(?:\D|$)~', $q, $m))
		$time = ($m[1] < 10 ? '0' : '').round($m[1]).':'.($m[2] < 10 ? '0' : '').round($m[2]);
	else
		$time = '';

	$ts = strtotime($day.' '.$time);
	$title = $ts;
	$sub = time2date($ts);
}
add($title, $title, $sub, 'time.png');
show();

function time2date($ts) {
	global $weekdays;
	$date = $weekdays[date('w', $ts)].date(", d.m.Y - H:i:s", $ts);
	return preg_replace('~:00$~', '', $date);
}

function formatDay($d, $m, $y) {
	if ($y < 100)
		$y = ($y < 70 ? '20' : '19').$y;
	return $d.'.'.$m.'.'.$y;
}