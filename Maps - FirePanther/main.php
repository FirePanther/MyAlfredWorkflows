<?php
$q = trim($q);

if ($q == '') {
	add('http://maps.apple.com/', 'Apple Maps öffnen');
	show();
	exit;
}

add('http://maps.apple.com/?'.urlencode($q), 'Auf Maps nach "'.$q.'" suchen', '', null, 0);
if (strlen($q) < 5) {
	show();
	exit;
}

//get_cache('maps:'.$q);

$iconStyle = 'roadmap'; // street, roadmap (oder: satellite, hybrid, terrain)

$results = 5;
$search = 'http://maps.googleapis.com/maps/api/geocode/json?hl=de&address=deutschland+berlin+'.urlencode($q).'&sensor=false';

$json = file_get_contents($search);
$array = @json_decode($json, 1);

$results = $array['results'];
$comps = array();

foreach ($results as $result) {
	$components = $result['address_components'];
	foreach ($components as $comp) {
		$comps[$comp['types'][0]] = $comp['short_name'];
	}
	$nr = $route = $sublocality = $locality = $plz = 0;
	if (isset($comps['street_number']))
		$nr = $comps['street_number'];
	if (isset($comps['route']))
		$route = str_replace('Straße', 'Str.', str_replace('straße', 'str.', $comps['route']));
	if (isset($comps['sublocality']))
		$sublocality = $comps['sublocality'];
	if (isset($comps['locality']))
		$locality = str_replace('Berlin', 'Bln', $comps['locality']);
	if (isset($comps['postal_code']))
		$plz = $comps['postal_code'];
	
	$title = '';
	if ($route && $plz)
		$title = $route.($nr ? ' '.$nr : '').' ('.$plz.($locality ? ' '.$locality : '').')';
	else {
		if ($sublocality)
			$title = $sublocality;
		if ($locality) {
			if ($title)
				$title .= ', ';
			$title .= $locality;
		}
		if ($plz) {
			if ($title)
				$title .= ' ('.$plz.')';
			else
				$title = $plz;
		}
	}
	$lat = $result['geometry']['location']['lat'];
	$lng = $result['geometry']['location']['lng'];
	if ($iconStyle == 'street')
		// street view
		$icon = icon('http://maps.googleapis.com/maps/api/streetview?location='.$lat.','.$lng.'&size=100x100&sensor=false');
	else
		// google maps
		$icon = icon('http://maps.googleapis.com/maps/api/staticmap?center='.$lat.','.$lng.'&zoom=12&size=46x46&scale=2&language=de&sensor=false&maptype='.$iconStyle.'&format=png32');
	add('http://maps.apple.com/?q='.$lat.','.$lng.'#'.urlencode($title), $title, $result['formatted_address'], $icon);
}
show('maps:'.$q);