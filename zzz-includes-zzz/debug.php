<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		debugging functions
 * @date			2014
 * @version			1.0
 */

function debug($v) {
	add($v, str_replace("\n", " ", print_r($v, 1)), 'Debugger');
	show();
	exit;
}
function debuglog($fn, $source = '') {
	file_put_contents('/tmp/'.filename($fn).'.log', $source);
}