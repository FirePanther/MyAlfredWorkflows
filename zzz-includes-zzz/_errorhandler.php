<?php
$extensionFirstWorkingDir = getcwd();
if (!isset($errorlog)) $errorlog = true;
set_error_handler('error_handler');

function error_handler($errno, $errstr, $errfile, $errline) {
	global $extensionFirstWorkingDir, $errorlog;
	
	if ($errorlog) {
		$error = '';
		switch ($errno) {
			case E_USER_ERROR:
				$error .= "My ERROR [$errno] $errstr\n";
				$error .= "  Fatal error on line $errline in file $errfile";
				$error .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
				$error .= "Aborting...\n";
				break;
			case E_USER_WARNING:
				$error .= "My WARNING [$errno] $errstr\n";
				break;
			case E_USER_NOTICE:
				$error .= "My NOTICE [$errno] $errstr\n";
				break;
			default:
				$error .= "Unknown error type: [$errno] $errstr\n";
				break;
		}
		file_put_contents($extensionFirstWorkingDir.'/_error.log', "$error\n$errfile ($errline)");
		add($extensionFirstWorkingDir.'/_error.log', 'ERROR: see in "_error.log" file', $extensionFirstWorkingDir.'/_error.log');
		show();
		exit;
	}
}