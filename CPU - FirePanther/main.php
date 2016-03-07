<?php
exec('ps -eo pcpu,pid,user,args | sort -k1 -r | head -10', $p);
foreach ($p as $pl) {
	$pl = trim($pl);
	$pl = preg_replace('~\s+~', ' ', $pl);
	$pls = explode(' ', $pl, 4);
	if ($pls[0] != '%CPU') {
		if (preg_match('~^(/.*/(.*)\.app)/Contents/MacOS/\\2($| )~U', $pls[3], $m))
			$file = $m[1];
		else
			$file = $pls[3];

		$prog = preg_replace('~^.*?/([^/]+?)( \-.+)?$~', '$1', $pls[3]);
		add($file, $pls[0].'% - '.$prog, $pls[1].' - '.$pls[2], 'fileicon:'.$file);
	}
}
show();