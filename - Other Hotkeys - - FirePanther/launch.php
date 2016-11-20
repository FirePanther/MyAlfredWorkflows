<?php
$type = $q[0];
$q = substr($q, 1);
if (exec('osascript -e "tell application \"System Events\" to return name of first process where it is frontmost"') == 'Finder') {
	if ($type == 'f') { // file
		$param = exec("osascript -e \"tell application \\\"Finder\\\"
	set fs to selection
	set lst to \\\"\\\"
	set i to 0
	repeat (count of fs) times
		set i to i + 1
		set lst to lst & POSIX path of (item i of fs as alias) & return
	end repeat
	return lst
end tell\"");
		$param = explode("\r", $param);
	} else { // dir
		$param = [getActiveFinder()];
	}
} else $param = null;

if ($param) {
	// launch app with file/folder
	foreach ($param as $a) {
		exec('open -a "'.$q.'" "'.$a.'"');
	}
} else {
	// just launch app
	exec('open -a "'.$q.'"');
}
