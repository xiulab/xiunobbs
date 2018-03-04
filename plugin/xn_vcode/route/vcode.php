<?php

!defined('DEBUG') AND exit('Access Denied.');

//$action = param(1);

$width = 150;
$height = 50;
$font_size = 25;
$font_file = APP_PATH.'plugin/xn_vcode/font/arvo_regular.ttf';  // 免费开源字体文件位置

$im = imagecreatetruecolor($width, $height);
imagefill($im, 0, 0, vcode_random_bg($im));
vcode_add_impurity($im, $width, $height);

// 验证码位数
$count = 4;
$word = xn_rand($count);

vcode_add_word($im, $word, $count, $width, $height, $font_size, $font_file);

// 记录到 Session
$_SESSION["vcode"] = $word;

header("Content-Type: image/jpeg");
imagejpeg($im, NULL, 30); // 降低画质，增加难度
imagedestroy($im);

function vcode_add_word($im, $word, $count, $width, $height, $font_size, $font_file) {
	$padding = 10;
	$x = $padding;
	$y = $height - $padding;
	for($i = 0; $i < $count; $i++){
		imagettftext($im, $font_size, mt_rand(-30, 30), $x, $y, vcode_random_fg($im), $font_file, $word[$i]);
		$x += $font_size + 4;
	}
}

// 添加干扰
function vcode_add_impurity($im, $width, $height) {
	// 干扰直线
	for($i = 0; $i < 5; ++ $i) {
		imageline($im, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), vcode_random_fg($im));
	}
	
	// 干扰弧线
	for($i = 0; $i < 6; ++ $i) {
		imagearc($im, mt_rand(-$width, $width), mt_rand(-$height, $height), mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, 360), mt_rand(0, 360), vcode_random_fg($im));
	} 
	
	// 干扰点
	for($i = 0; $i < 50; ++ $i) {
		imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), vcode_random_fg($im));
	}
}

function vcode_random_bg($im){
	return imagecolorallocate($im, mt_rand(0, 128), mt_rand(0, 128),  mt_rand(0, 128));
}

function vcode_random_fg($im){
	return imagecolorallocate($im, mt_rand(100, 255), mt_rand(100, 255),  mt_rand(100, 255));
}

?>