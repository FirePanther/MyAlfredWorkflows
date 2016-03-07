<?php
/**
 * @author			Suat Secmen
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		some functions for alfred apps
 * @date			2014
 * @version			1.0
 */
ini_set('date.timezone', 'Europe/Berlin');

function htmldecode($text) {
	$text = html_entity_decode($text, ENT_QUOTES, "ISO-8859-1");
	$text = preg_replace('/&#(\d+);/me', "chr(\\1)", $text);
	$text = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $text);
	return $text;
}

function filename($fn) {
	$reserved = array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>');
	$fn = str_replace($reserved, ' ', $fn);
	return $fn;
}

function removeDir($p) {
	if (!is_dir($p))
		return;
	if (substr($p, -1) <> '/')
		$p .= '/';
	$s = @scandir($p);
	foreach ($s as $f) {
		if ($f == '.' || $f == '..')
			continue;
		if (is_dir($p.$f))
			removeDir($p.$f.'/');
		else
			@unlink($p.$f);
	}
	@rmdir($p);
}

function getActiveFinder() {
	$cache = '/tmp/alfred-finder.cache';
	if (is_file($cache) && filemtime($cache) > time() - 2) {
		$finder = file_get_contents($cache);
	} else {
		// activate finder
		$finder = @exec('osascript -e "tell application \\"Finder\\" to return POSIX path of (insertion location as alias)"');
		file_put_contents($cache, $finder);
	}
	return $finder;
}

function getPlist($f) {
	$dest = '/tmp/'.md5($f).'.plist';
	if (!is_file($dest) || filemtime($f) < filemtime($dest))
		@exec('plutil -convert xml1 "'.$f.'" -o '.$dest);
	return file_get_contents($dest);
}