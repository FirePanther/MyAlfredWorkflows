<?php
file_put_contents('/tmp/jqapi.zip', @fopen('http://www.jqapi.com/jqapi.zip', 'r'));
$zip = new ZipArchive;
if ($zip->open('/tmp/jqapi.zip') === true) {
	$dir = $_SERVER['HOME'].'/Documents/AlfredApp/Documentation/jquery';
	if (!is_dir($dir)) {
		$mkdir = @mkdir($dir, 0777, 1);
		if (!$mkdir)
			die('mkdir fehlgeschlagen ('.$dir.')');
	}
	$zip->extractTo($dir.'/');
	$zip->close();
} else
	die('Es ist ein Fehler beim Entpacken aufgetreten.');

die('Neueste Dokumentation erfolgreich heruntergeladen');