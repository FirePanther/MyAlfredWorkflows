<?php
$type = $q[0];
$q = substr($q, 1);
if (exec('osascript -e "tell application \"System Events\" to return name of first process where it is frontmost"') == 'Finder') {
	if ($type == 'f') { // file
		$param = exec('osascript -e "tell application \\"Finder\\" to return POSIX path of (selection as alias)"');
	} else { // dir
		$param = getActiveFinder();
	}
} else $param = null;

if ($param) {
	// launch app with file/folder
	exec('open -a "'.$q.'" "'.$param.'"');
} else {
	// just launch app
	exec('open -a "'.$q.'"');
}
