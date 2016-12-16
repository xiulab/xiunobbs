<?php exit;

if($method == 'POST') {
	if(param('anti_spam_key') != setting_get('anti_spam_key')) {
		message(-1, 'anti spam enabled');
	}
	// 检测 Referer
	$referer = _SERVER('HTTP_REFERER');
	$http_url_path = http_url_path();
	if($http_url_path != substr($referer, 0, strlen($http_url_path))) {
		message(-1, 'referer error');
	}
	
	
	$__anti_xss__post = _POST('__anti_xss__');
	$__anti_xss__cookie = _COOKIE('__anti_xss__');
	if(!($__anti_xss__post == $__anti_xss__cookie)) {
		message(-1, 'CSRF checked!');
	}
	
}
?>