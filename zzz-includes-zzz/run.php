<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		run/execute apps, scripts and commands
 * @date			2014
 * @version			1.0
 */

function quicklook($p) {
	exec('killall qlmanage'); // you have to kill, else the old one will freeze :/
	exec('qlmanage -p "'.$p.'" > /dev/null 2>&1 &');
}

function aexec($cmd, $runtime = 5, $showcmd = null) {
	$response = array();

	$descriptorspec = array(
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);
	$pipes = null;

	// fixes "term environment variable not set" error message
	$cmd = "export TERM=\${TERM:-dumb}\n$cmd";

	$process = proc_open($cmd, $descriptorspec, $pipes);

	$emptyarr = array();
	$start = microtime(1);

	$errbuf = '';
	if (is_resource($process)) {
		while (($buffer = fgets($pipes[1], 1024)) != NULL
				|| ($errbuf = fgets($pipes[2], 1024)) != NULL) {
			if (!isset($flag)) {
				$pstatus = proc_get_status($process);
				$first_exitcode = $pstatus["exitcode"];
				$flag = true;
			}
			if (strlen($buffer))
				$response[] = rtrim($buffer);
			if (strlen($errbuf))
				$response[] = "Error: " . $errbuf;
			if (microtime(1) - $start > $runtime) {
				$response[] = 'Exit: Timeout ('.$runtime.'s)';
				break;
			}
		}

		fclose($pipes[1]);
		proc_terminate($process);
	}
	return $response;
}