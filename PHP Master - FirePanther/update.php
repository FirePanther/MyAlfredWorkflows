<?php
file_put_contents('/tmp/php.tar.gz', @fopen('http://de1.php.net/get/php_manual_en.tar.gz/from/this/mirror', 'r'));
$gz = gzopen('/tmp/php.tar.gz', 'r');
file_put_contents('/tmp/php.tar', $gz);
unlink('/tmp/php.tar.gz');
$dir = $_SERVER['HOME'].'/Documents/AlfredApp/Documentation/php';
removeDir($dir);
$zip = new PharData('/tmp/php.tar');
if ($zip) {
	if (!is_dir($dir)) {
		$mkdir = @mkdir($dir, 0777, 1);
		if (!$mkdir)
			die('mkdir fehlgeschlagen ('.$dir.')');
	}
	$zip->extractTo($dir.'/');
	unlink('/tmp/php.tar');
	
	$s = scandir($dir);
	foreach ($s as $f) {
		if ($f == '.' || $f == '..')
			continue;
		if (is_dir($dir.'/'.$f)) {
			$ndir = $dir.'/'.$f.'/';
			break;
		}
	}
	$s = glob($ndir.'*');
	foreach ($s as $f) {
		if (is_file($f) && substr($f, -5) == '.html' && substr(basename($f), 0, 9) == 'function.') {
			parseFile($f, $dir.'/'.substr(basename($f), 9));
		}
	}
	removeDir($ndir);
} else
	die('Es ist ein Fehler beim Entpacken aufgetreten.');

die('Neueste Dokumentation erfolgreich heruntergeladen');

function parseFile($f, $nf) {
	$src = file_get_contents($f);
	
	if (strpos($src, '</head>') !== false)
		$src = substr($src, strpos($src, '</head>')+7);
	
	$src = preg_replace('~\s+~', ' ', $src);
	if (strpos($src, '<div class="refsect1 examples"') !== false)
		$src = substr($src, 0, strpos($src, '<div class="refsect1 examples"'));
	if (strpos($src, '<div class="refsect1 notes"') !== false)
		$src = substr($src, 0, strpos($src, '<div class="refsect1 notes"'));
	if (strpos($src, '<div class="refsect1 seealso"') !== false)
		$src = substr($src, 0, strpos($src, '<div class="refsect1 seealso"'));
	
	// create json
	$function = '';
	$params = '';
	$type = '';
	
	$verinfo = '';
	$desc1 = '';
	$desc2 = '';
	$paramdesc = '';
	$returnvalue = '';
	
	$deprecated = '';
	$removed = '';
	if (strpos($src, '<h3 class="title">Description</h3>') !== false) {
		$s = substr($src, strpos($src, '<h3 class="title">Description</h3>')+34);
		$s = substr($s, 0, strpos($s, '</div>'));

		$function = substr($s, 0, strpos($s, '</strong></span>'));
		$params = substr($s, strpos($s, '</strong></span>'));
		$function = preg_replace('~<[^>]*>~s', '', $function);
		$function = trim(preg_replace('~\s+~s', ' ', $function));
		$params = preg_replace('~<[^>]*>~s', '', $params);
		$params = plaintext(preg_replace('~\s+~s', ' ', $params));

		$type = plaintext(substr($function, 0, strpos($function, ' ')));
		$function = plaintext(substr($function, strpos($function, ' ')+1));
		
		// texts for good results
		if (preg_match('~<p class="verinfo">(.*?)</p>~s', $src, $m))
			$verinfo = plaintext($m[1]);
		if (preg_match('~<span class="dc-title">(.*?)</span>~s', $src, $m))
			$desc1 = plaintext($m[1]);
		if (preg_match('~<p class="para rdfs-comment">(.*?)</p>~s', $src, $m))
			$desc2 = plaintext($m[1]);
		if (preg_match('~<div class="refsect1 parameters"[^>]*>(.*?)</div>~s', $src, $m)) {
			if (preg_match('~<dl>(.*?)</dl>~s', $m[1], $m2))
				$paramdesc = plaintext($m2[1]);
		}
		if (preg_match('~<div class="refsect1 returnvalues"[^>]*>(.*?)</div>~s', $src, $m)) {
			if (preg_match('~<p class="para">(.*?)</p>~s', $m[1], $m2))
				$returnvalue = plaintext($m2[1]);
		}
		
		// deprecated or removed
		$deprecated = strpos($src, 'This extension is deprecated as of') !== false || strpos($src, '<em class="emphasis">DEPRECATED</em>') !== false;
		$removed = strpos($src, '<em class="emphasis">REMOVED</em> as of ') !== false;
	}
	if (!$function)
		$function = str_replace('-', '_', basename($nf, '.html'));
	
	file_put_contents($nf, json_encode(array(
		'func' => $function,
		'params' => $params,
		'type' => $type,
		'verinfo' => $verinfo,
		'desc1' => $desc1,
		'desc2' => $desc2,
		'paramdesc' => $paramdesc,
		'returnvalue' => $returnvalue,
		'deprecated' => $deprecated,
		'removed' => $removed
	)));
}

function plaintext($t) {
	return html_entity_decode(trim(preg_replace('~<[^\s][^>]*>~', '', $t)));
}