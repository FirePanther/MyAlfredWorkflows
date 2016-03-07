<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		search serverside via console with and without regex
 * @date			2014
 * @version			1.0
 */

### get arguments
$argc = 1;
$argv = array('fpsearch');

if (strlen($q)) {
	$inQuote = 0;
	$strStart = 0;

	$q = trim($q).' ';

	for ($i = 0; $i < strlen($q); $i++) {
		$char = substr($q, $i, 1);

		if ($char == '\\') {
			$q = substr($q, 0, $i).substr($q, $i+1);
			continue;
		}

		if ($inQuote) {
			if ($char == '"') {
				$argv[] = substr($q, $strStart, $i - $strStart);
				$argc++;
			}
		} else {
			switch ($char) {
				case '"':
					$inQuote = 1;
					$strStart = $i + 1;
					continue;
				case ' ':
					$argv[] = substr($q, $strStart, $i - $strStart);
					$argc++;

					$strStart = $i + 1;
					continue;
			}
		}
	}
}

function argv($nr, $v1, $v2 = '') {
	global $argv;
	if (isset($argv[$nr])) {
		$a = mb_strtolower($argv[$nr]);
		if ($a == $v1 || strlen($v2) && $a == $v2)
			return true;
	}
	return false;
}

function addln($msg, $isFile = 0, $subtext = '') {
	if ($isFile) {
		$file = getcwd().$subtext.$msg;
		add($file, $msg, cleandir(substr($subtext, 1)), 'fileicon:'.$file);
	} else
		add('', $msg, cleandir($subtext));
}

if ($argc == 1 || argv(1, '--help', '-h')) {
	addln('fpsearch [-r] [-e txt,log] "SEARCH STRING"');
	addln('-R		Recursive (searches in subfolders)');
	addln('-r		The search string is a regex pattern');
	addln('-e		Extensions, comma separated');
	addln('-s		Limits the maximum file size (default: 20M)');
	addln('-word	Disallows the word "word"');
} else {
	$searchWord = array();
	$disallow = array();
	$regex = 0;
	$recursive = 0;
	$ext = array();
	$limit = $defaultlimit = 20*1024*1024;

	$extension = 0;
	$sizelimit = 0;
	$filename = array_shift($argv);
	while ($param = array_shift($argv)) {
		if ($extension) {
			if (strstr($param, ','))
				$ext = explode(',', $param);
			else
				$ext = array($param);
			$extension = 0;
			continue;
		}
		if ($sizelimit) {
			if (preg_match('~^\d+b?$~', $param))
				$sizelimit = $param;
			elseif (preg_match('~^(\d+)(k|m|g|t)b?$~i', $param, $m)) {
				$sizelimit = $m[1];
				switch (strtolower($m[2])) {
					case 't': $sizelimit *= 1024;
					case 'g': $sizelimit *= 1024;
					case 'm': $sizelimit *= 1024;
					case 'k': $sizelimit *= 1024;
						break;
				}
			}
			continue;
		}
		if ($param == '-r')
			$regex = 1;
		if ($param == '-R')
			$recursive = 1;
		elseif ($param == '-e')
			$extension = 1;
		elseif ($param == '-s')
			$sizelimit = 1;
		elseif (substr($param, 0, 1) == '-')
			$disallow[] = substr($param, 1);
		else
			$searchWord[] = $param;
	}

	$starttime = microtime(1);
	$files = 0;
	$dirs = 0;

	$lastinfo = microtime(1);
	chdir(getActiveFinder());
	add('', 'Searching for ›'.implode(', ', $searchWord).'‹', cleandir(getcwd(), 0));
	search_folder('./', $searchWord, $disallow, $regex, $recursive, $ext, $limit);
	addln('Search finished in '.round((microtime(1) - $starttime)*1000, 3).'ms', 0, 'Searched files/dirs: '.number_format($files, 0, ',', ' ').'/'.number_format($dirs, 0, ',', ' '));
}

function cleandir($d, $remCWD = 1) {
	if ($remCWD)
		$d = str_replace(getcwd().'/', './', $d);
	$d = str_replace($_SERVER['HOME'], '~', $d);
	if (strlen($d) > 65) {
		$d = substr($d, 0, 30).'…'.substr($d, -30);
	}
	return $d;
}

function search_folder($dir, $search, $disallow, $reg, $rec, $ext, $sl) {
	global $files, $dirs;
	if (!is_readable($dir)) return;
	$s = @scandir($dir);
	$dirs++;
	foreach ($s as $f) {
		if ($f == '.' || $f == '..')
			continue;
		if (!is_readable($dir.$f)) continue;
		if (is_dir($dir.$f)) {
			if ($rec)
				search_folder($dir.$f.'/', $search, $disallow, $reg, $rec, $ext, $sl);
		} else {
			$files++;
			if (count($ext)) {
				$found = 0;
				foreach ($ext as $e) {
					$e = strtolower(trim($e));
					if (substr($e, 0, 1) == '.')
						$e = substr($e, 1);
					if (strtolower(substr($f, -1-strlen($e))) == '.'.$e) {
						$found = 1;
						break;
					}
				}
				if (!$found)
					continue;
			}
			if (is_file($dir.$f))
				$size = filesize($dir.$f);
			else
				$size = 0;
			if ($size <= $sl) {
				$content = is_file($dir.$f) ? file_get_contents($dir.$f) : '';
				$ok = 1;

				// search allowed
				if (count($search)) {
					foreach ($search as $w) {
						if ($reg) {
							if (!preg_match('~'.$w.'~is', $content) && !preg_match('~'.$w.'~is', $f)) {
								$ok = 0;
								break;
							}
						} else {
							if (!stristr($content, $w) && !stristr($f, $w)) {
								$ok = 0;
								break;
							}
						}
					}
				}

				// search disallowed
				if ($ok && count($disallow)) {
					foreach ($disallow as $w) {
						if ($reg) {
							if (preg_match('~'.$w.'~is', $content)) {
								$ok = 0;
								break;
							}
						} else {
							if (stristr($content, $w)) {
								$ok = 0;
								break;
							}
						}
					}
				}

				if ($ok)
					print_file($dir, $f);
			}
		}
	}
}
show();

function print_file($d, $f) {
	addln($f, 1, substr($d, 1));
}