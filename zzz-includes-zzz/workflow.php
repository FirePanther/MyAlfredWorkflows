<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		workflow managing
 * @date			2014
 * @version			1.0
 */

$fp_items = array();
$fp_cache_data = array();

function add($return = '', $title = '', $text = '', $img = null, $cache = 1) {
	global $fp_items, $fp_cache_data;
	// default icon
	if ($img === null)
		$img = 'icon.png';

	// cache
	if ($cache)
		$fp_cache_data[] = array($return, $title, $text, $img);

	if (strstr($return, '{title}'))
		$return = str_replace('{title}', $title, $return);
	if (strstr($return, '{text}'))
		$return = str_replace('{text}', $title, $return);

	$fp_items[] = array($return, $title, $text, $img);
}

function show($cacheName = '') {
	global $fp_items, $fp_cache_data;
	$items = [];
	foreach($fp_items as $item) {
		$icon = [
			'path' => $item[3],
			//'type' => 'default'
		];
		if (substr($icon['path'], 0, 9) == 'fileicon:' || substr($icon['path'], 0, 9) == 'filetype:') {
			list($type, $path) = explode(':', $icon['path'], 2);
			$type = 'default';
			//$icon['type'] = $type;
			$icon['path'] = str_replace($_SERVER['HOME'].'/', '~/', $path);
			$item[2] = $icon['path'];
		}
		$items[] = [
			'arg' => $item[0],
			'title' => $item[1],
			'subtitle' => $item[2],
			'icon' => $icon
		];
	}
	/*
	$echo = '<?xml version="1.0"?>
<items>';
	foreach($fp_items as $item) {
		$echo .= _getItem($item[0], $item[1], $item[2], $item[3]);
	}
	$echo .= '</items>';
	*/
	echo json_encode(['items' => $items]);
	//_addCache($cacheName, $echo);
}

function _getItem($arg, $title, $subtitle, $icon, $extended = array()) {
	$iconAttr = '';
	if (substr($icon, 0, 9) == 'fileicon:' || substr($icon, 0, 9) == 'filetype:') {
		$iconAttr = ' type="'.substr($icon, 0, strpos($icon, ':')).'"';
		$icon = substr($icon, strpos($icon, ':')+1);
	}
	$xml = '<item arg="'.htmlspecialchars($arg).'" valid="yes">'.
			'<arg>'.htmlspecialchars($arg).'</arg>'.
			'<title>'.htmlspecialchars($title).'</title>'.
			'<subtitle>'.htmlspecialchars($subtitle).'</subtitle>'.
			'<icon'.$iconAttr.'>'.htmlspecialchars($icon).'</icon>';

	if (count($extended)) {
		foreach ($extended as $key => $body) {
			if (is_array($body)) {
				if (isset($body['attr']))
					$attr = ' '.$body['attr'];
				$body = $body['body'];
			} else
				$attr = '';
			$xml .= '<'.$key.$attr.'>'.$body.'</'.$key.'>';
		}
	}

	return $xml.'</item>';
}

function _addCache($cacheName, $xml) {
	if ($cacheName)
		file_put_contents('/tmp/history.'.md5($cacheName).'.log', $xml);
}

function get_cache($cacheName) {
	$cache = '/tmp/history.'.md5($cacheName).'.log';
	if (is_file($cache)) {
		echo file_get_contents($cache);
		exit;
	}
}

function icon($url, $ext = '', $filename = '', $override = 0) {
	$cache = '/tmp/';
	if ($filename)
		$cache .= filename($filename);
	else
		$cache .= 'icon.'.md5($url);
	$cache .= $ext;

	if (!$filename && !$ext)
		$cache .= '.png';

	if (!is_file($cache) || $override) {
		$fallback = 1;
		if ($url) {
			$img = @file_get_contents($url);
			if ($img) {
				@file_put_contents($cache, $img);
				$fallback = 0;
			}
		}
		if ($fallback)
			$cache = 'icon.png';
	}
	return $cache;
}

function ignore_short($q, $chars = 3, $wait = 1) {
	if (strlen($q) < $chars) {
		add('', '"'.$q.'" enthält zu wenige Zeichen...');
		show();
		if ($wait)
			sleep($wait);
		exit;
	}
}

function showEmpty($title = '') {
	if ($title)
		add('', $title);
	else {
		$plist = file_get_contents('info.plist');
		preg_match('~<key>description</key>\s*<string>(.*?)</string>~si', $plist, $m);
		add('', $m[1]);
	}
	show();
	exit;
}