<?php
//include '../consoletest.php';
$terminal = '/Applications/Utilities/Terminal.app/Contents/MacOS/Terminal';
$terminalName = 'Terminal';
$ascommand = 'do script with command';
$iterm = '/Applications/iTerm.app/Contents/MacOS/iTerm';
if (is_file($iterm)) {
	$terminal = $iterm;
	$terminalName = 'iTerm';
	$ascommand = 'tell the first session of the first terminal to write text';
}

$finder = getActiveFinder();
exec('osascript -e \'tell application "'.$terminalName.'"\' -e \'activate\' -e \''.$ascommand.' "cd \\"'.$finder.'\\"'."\n".'clear'."\n".str_replace('"', '\\"', $q).'"\' -e \'end tell\'');

$q_l = strtolower(trim($q));
$qname = preg_replace('~[^a-zA-Z0-9 \-]+~', '_', $q_l);

// save history
$historyDir = $_SERVER['HOME'].'/Dropbox/Configs/AlfredApp/sync/terminal-history/';
$cache = $historyDir.$qname.'-'.md5($q_l);

$num = 0;
if (is_file($cache)) {
	$a = @json_decode(file_get_contents($cache), 1);
	$q = $a['q'];
	$num = $a['n'];
}
file_put_contents($cache, json_encode(array(
	'q' => $q,
	'n' => ++$num
)));