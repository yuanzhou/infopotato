<?php
/**
 * Create image-based CAPTCHA
 *
 * @access	public
 * @param	array	array of data for the CAPTCHA
 * @param	string	path to create the image in
 * @param	string	URL to the CAPTCHA image folder
 * @param	string	server path to font
 * @return	string
 */
function image_captcha_function(array $data = NULL) {
	// The captcha function requires the GD image library.
	// Only the img_dir and img_url are required.
	// If a "word" is not supplied, the function will generate a random ASCII string. You might put together your own word library that you can draw randomly from.
	// If you do not specify a path to a TRUE TYPE font, the native ugly GD font will be used.
	// The "captcha" folder must be writable (666, or 777)
	// The "expiration" (in seconds) signifies how long an image will remain in the captcha folder before it will be deleted. The default is two hours.
	$defaults = array(
		'word' => '', 
		'img_dir' => '', 
		'img_url' => '', 
		'img_width' => '150', 
		'img_height' => '30', 
		'font_path' => '', 
		'expiration' => 7200
	);

	foreach ($defaults as $key => $val) {
		$$key = isset($data[$key]) ? $data[$key] : $val;
	}

	if ($img_dir === '' || $img_url === '' || ! @is_dir($img_dir) || ! is_writable($img_dir) || ! extension_loaded('gd')) {
		return FALSE;
	}

	// Remove old images
	$now = microtime(TRUE);
	$current_dir = @opendir($img_dir);
	while ($filename = @readdir($current_dir)) {
		if (substr($filename, -4) === '.jpg' && (str_replace('.jpg', '', $filename) + $expiration) < $now) {
			@unlink($img_dir.$filename);
		}
	}

	@closedir($current_dir);

	// Do we have a "word" yet?
    if (empty($word)) {
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$word = '';
		for ($i = 0, $mt_rand_max = strlen($pool) - 1; $i < 8; $i++) {
			$word .= $pool[mt_rand(0, $mt_rand_max)];
		}
	} elseif ( ! is_string($word)) {
		$word = (string) $word;
	}

	// Determine angle and position
	$length	= strlen($word);
	$angle = ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
	$x_axis	= rand(6, (360/$length)-16);
	$y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

	// Create image
	// PHP.net recommends imagecreatetruecolor(), but it isn't always available
	$im = function_exists('imagecreatetruecolor')
			? imagecreatetruecolor($img_width, $img_height)
			: imagecreate($img_width, $img_height);

	//  Assign colors
	$bg_color = imagecolorallocate ($im, 255, 255, 255);
	$border_color = imagecolorallocate ($im, 153, 102, 102);
	$text_color = imagecolorallocate ($im, 204, 153, 153);
	$grid_color	= imagecolorallocate($im, 255, 182, 182);
	$shadow_color = imagecolorallocate($im, 255, 240, 240);

	//  Create the rectangle
	ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

	//  Create the spiral pattern
	$theta = 1;
	$thetac = 7;
	$radius = 16;
	$circles = 20;
	$points = 32;

	for ($i = 0, $cp = ($circles * $points) - 1; $i < $cp; $i++) {
		$theta += $thetac;
		$rad = $radius * ($i / $points);
		$x = ($rad * cos($theta)) + $x_axis;
		$y = ($rad * sin($theta)) + $y_axis;
		$theta += $thetac;
		$rad1 = $radius * (($i + 1) / $points);
		$x1 = ($rad1 * cos($theta)) + $x_axis;
		$y1 = ($rad1 * sin($theta)) + $y_axis;
		imageline($im, $x, $y, $x1, $y1, $grid_color);
		$theta -= $thetac;
	}

	//  Write the text
	$use_font = ($font_path !== '' && file_exists($font_path) && function_exists('imagettftext'));

	if ($use_font === FALSE) {
		$font_size = 5;
		$x = rand(0, $img_width/($length/3));
		$y = 0;
	} else {
		$font_size	= 16;
		$x = rand(0, $img_width/($length/1.5));
		$y = $font_size + 2;
	}

	for ($i = 0; $i < $length; $i++) {
		if ($use_font === FALSE) {
			$y = rand(0 , $img_height/2);
			imagestring($im, $font_size, $x, $y, $word[$i], $text_color);
			$x += ($font_size * 2);
		} else {
			$y = rand($img_height/2, $img_height-3);
			imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, $word[$i]);
			$x += $font_size;
		}
	}


	//  Create the border
	imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

	//  Generate the image
	$img_name = $now.'.jpg';
	ImageJPEG($im, $img_dir.$img_name);
	$img = '<img src="'.$img_url.$img_name.'" style="width: '.$img_width.'; height: '.$img_height .'; border: 0;" alt=" " />';
	ImageDestroy($im);

	return array(
		'word' => $word, 
		'time' => $now, 
		'image' => $img
	);
}

/* End of file: ./system/functions/image_captcha_function.php */