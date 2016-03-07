<?php
$dir = getActiveFinder();
$text = exec('osascript -e \'return (display dialog "Filename" default answer "unnamed.txt")\'');
if (preg_match("~button returned:OK.*text returned:\s*(.*)$~", $text, $m)) {
	$file = $m[1];
	if (strlen($file)) {
		file_put_contents($dir.filename($file), "");
	}
}