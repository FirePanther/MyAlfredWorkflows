<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		communication functions for alfred
 * @date			2014
 * @version			1.0
 */

function alfred_send() {
	$vars = func_get_args();
	$send = array();
	foreach ($vars as $v) {
		global $$v;
		$send[$v] = $$v;
	}
	$json = json_encode($send);
	$id = hash('crc32b', $json);
	file_put_contents('/tmp/'.$id.'.json', $json);
	return $id;
}
function alfred_get($queryname = 'q') {
	global $$queryname;
	$get = @json_decode(@file_get_contents('/tmp/'.$$queryname.'.json'), 1);
	
	foreach ($get as $k => $v) {
		global $$k;
		$$k = $v;
	}
}

function alfred_set_action($array) {
	file_put_contents('/tmp/alfredaction.log', json_encode($array));
}
function alfred_get_action() {
	if (is_file('/tmp/alfredaction.log')) {
		$json = file_get_contents('/tmp/alfredaction.log');
		return @json_decode($json, 1);
	} else
		return array();
}