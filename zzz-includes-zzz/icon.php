<?php
/**
 * @author			FirePanther
 * @copyright		FirePanther (http://firepanther.pro/)
 * @description		icon generator
 * @date			2014
 * @version			1.0
 */

function mergeIcon($main) {
	$str = getcwd().'|'.$main;
	if (!function_exists('imagecreatefrompng'))
		return '';
	

	if (preg_match('~^(\d+)x(\d+)$~', $main, $match)) {
		$w = $match[1];
		$h = $match[2];
		$img = imagecreatetruecolor($w, $h);
	} else {
		$img = imagecreatefromextension($main);
		$w = imagesx($img);
		$h = imagesy($img);
	}
	imagealphablending($img, 1);
	imagesavealpha($img, 1);
	
	$args = func_get_args();
	array_shift($args);
	
	foreach ($args as $arg) {
		if (!$arg) continue;
		$str .= '|'.$arg;
		
		if (substr($arg, 0, 2) == 'a:') {
			$arg = explode(':', $arg);
			switch ($arg[1]) {
				case 'alpha':
					imagefilter_opacity($img, $arg[2]);
					break;
				case 'grey':
					imagefilter($img, IMG_FILTER_GRAYSCALE);
					break;
				case 'contrast':
					imagefilter($img, IMG_FILTER_CONTRAST, $arg[2]);
					break;
				case 'brightness':
					imagefilter($img, IMG_FILTER_BRIGHTNESS, $arg[2]);
					break;
				case 'negate':
					imagefilter($img, IMG_FILTER_NEGATE);
					break;
				case 'colorize':
					if (!isset($arg[5]))
						$arg[5] = 255;
					imagefilter($img, IMG_FILTER_COLORIZE, $arg[2], $arg[3], $arg[4], $arg[5]);
					break;
				case 'blur':
					for ($i = 1; $i <= (isset($arg[2]) ? $arg[2] : 1); $i++) {
						imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
					}
					break;
				case 'pixelate':
					if (!isset($arg[3]))
						$arg[3] = false;
					imagefilter($img, IMG_FILTER_PIXELATE, $arg[2], $arg[3]);
					break;
			}
		} else {
			list($img2, $size) = imagecreatefromextension($arg, 1);
			imagealphablending($img2, 1);
			imagesavealpha($img2, 1);
			imagecopyresized($img, $img2, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
			imagedestroy($img2);
		}
	}
	$dir = '/tmp/icon.'.md5($str).filemtime(__FILE__).'.png';
	imagepng($img, $dir);
	imagedestroy($img);
	return $dir;
}


function imagefilter_opacity(&$img, $opacity) {
	if (!isset($opacity))
		return false;
	$opacity /= 100;
	
	//get image width and height
	$w = imagesx($img);
	$h = imagesy($img);
	
	//turn alpha blending off
	imagealphablending($img, false);
	
	//find the most opaque pixel in the image (the one with the smallest alpha value)
	$minalpha = 127;
	for($x = 0; $x < $w; $x++) {
		for($y = 0; $y < $h; $y++) {
			$alpha = (imagecolorat($img, $x, $y) >> 24) & 0xFF;
			if($alpha < $minalpha)
				$minalpha = $alpha;
		}
	}
	
	//loop through image pixels and modify alpha for each
	for($x = 0; $x < $w; $x++) {
		for($y = 0; $y < $h; $y++) {
			//get current alpha value (represents the TANSPARENCY!)
			$colorxy = imagecolorat($img, $x, $y);
			$alpha = ($colorxy >> 24) & 0xFF;
			
			//calculate new alpha
			if($minalpha !== 127)
				$alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
			else
				$alpha += 127 * $opacity;
			
			//get the color index with new alpha
			$alphacolorxy = imagecolorallocatealpha($img, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
			
			//set pixel with the new color + opacity
			if(!imagesetpixel($img, $x, $y, $alphacolorxy))
				return false;
		}
	}
	return true;
}

function imagecreatefromextension($img, $returnArray = 0) {
	$size = getimagesize($img);
	switch ($size['mime']) {
		case 'image/jpeg':
			$image = imagecreatefromjpeg($img);
			break;
		case 'image/gif':
			$image = imagecreatefromgif($img);
			break;
		default:
			$image = imagecreatefrompng($img);
	}
	return $returnArray ? array($image, $size) : $image;
}