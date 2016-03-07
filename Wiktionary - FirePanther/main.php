<?php
get_cache('wiktionary'.filemtime(__FILE__).':'.trim($q));
$json = file_get_contents('https://www.googleapis.com/customsearch/v1?googlehost=google.com&safe=medium&key='.$key.'&cx='.$cx.'&q='.urlencode($q));
$array = @json_decode($json, 1);

$maxlength = 50;

$wordchars = utf8_decode('a-zÄÖÜäöü_-');

if (!isset($array['items'])) exit;

$gs = $array['items'];

$defaultTexts = array(
	'Aus Wiktionary, dem freien Wörterbuch.',
	'Wechseln zu: Navigation,',
	'[Bearbeiten]'
);

$c = 0;
foreach ($gs as $i) {
	if (preg_match('~^[a-z ]+:~i', $i['title']))
		continue;
	$title = trim(substr($i['title'], 0, strpos($i['title'], ' – ')));
	$snippet = str_replace($title, '~', $i['snippet']);
	$snippet = trim(str_replace($defaultTexts, '', $snippet));
	$snippet = preg_replace('~\~\.\s*~', '', $snippet);
	$snippet = preg_replace('~\s+~', ' ', $snippet);
	if (strlen($title) < $maxlength*.8 && strlen($snippet) > $maxlength*2) {
		$subtitle = substr($snippet, 0, $maxlength-(strlen($title)+3));
		$subtitle = utf8_encode(preg_replace('~[^'.$wordchars.']['.$wordchars.']+[^'.$wordchars.']*$~i', '', utf8_decode($subtitle)));
		if (strlen($subtitle) > 10) {
			$title .= ' » '.$subtitle;
			$snippet = trim(substr($snippet, strlen($subtitle)));
		}
	}
	add($i['link'], ($title), $snippet, isset($i['pagemap']['cse_thumbnail'][0]['src']) ? icon($i['pagemap']['cse_thumbnail'][0]['src']) : null);
	if (++$c >= 5)
		break;
}
show('wiktionary'.filemtime(__FILE__).':'.trim($q));