<?php
$q = preg_replace('~\s~', '', $q);
if (!preg_match('~^[012]?[\d]{1,2}\.[012]?[\d]{1,2}\.[012]?[\d]{1,2}\.[012]?[\d]{1,2}$~', $q))
	die();

get_cache('ip'.filemtime(__FILE__).':'.$q);

$json = file_get_contents('http://ip-api.com/json/'.urlencode($q));
$array = $data = json_decode($json, 1);
unset($array['status']);
unset($array['query']);
unset($array['as']);

// résumé
if ($array['country'] && $array['countryCode']) {
	$array['country'] .= ' ('.$array['countryCode'].')';
	unset($array['countryCode']);
}
if ($array['lat'] && $array['lon']) {
	$array['lat, lon'] = $array['lat'].', '.$array['lon'];
	unset($array['lat']);
	unset($array['lon']);
}
if ($array['isp'] && $array['org']) {
	$array['isp, org'] = ($array['isp'] == $array['org'] ? $array['isp'] : $array['isp'].', '.$array['org']);
	unset($array['isp']);
	unset($array['org']);
	unset($array['as']);
}
if ($array['region'] && $array['regionName']) {
	$array['region'] = $array['regionName'].' ('.$array['region'].')';
	unset($array['regionName']);
}
if ($array['timezone']) {
	ini_set('date.timezone', $array['timezone']);
	$array['timezone'] .= ' ('.date('d.m.Y - H:i').')';
}
if ($array['city'] && $array['zip']) {
	$array['city'] = $array['city'].' ('.$array['zip'].')';
	unset($array['zip']);
}

foreach ($array as $k => $v) {
	if (!$v)
		continue;
	add('', $v, $k, getIcon($k, $v));
}

show('ip'.filemtime(__FILE__).':'.$q);

function getIcon($k, $v) {
	global $data;
	switch($k) {
		case 'country':
		case 'countryCode':
			$v = strtolower($data['countryCode']);
			if (is_file('icons/flags/'.$v.'.png'))
				return 'icons/flags/'.$v.'.png';
			else
				return null;
			break;
		case 'lat, lon':
			return icon('http://maps.googleapis.com/maps/api/staticmap?center='.str_replace(' ', '', $v).'&zoom=12&size=46x46&scale=2&language=de&sensor=false&maptype=roadmap&format=png32');
			break;
		case 'timezone':
			return 'icons/timezone.png';
			break;
		case 'region':
		case 'regionName':
			return 'icons/region.png';
			break;
		case 'isp, org':
			return 'icons/isp.png';
			break;
		case 'city':
			return 'icons/city.png';
			break;
		default:
			return null;
	}
}