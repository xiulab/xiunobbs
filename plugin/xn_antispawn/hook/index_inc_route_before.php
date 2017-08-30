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

	$__anti_xss_value = $__anti_xss__cookie;
	if($__anti_xss__cookie != $__anti_xss__post) {
		$__anti_xss_value = $__anti_xss__post;
	}

	$arr = explode('_', $__anti_xss_value);
	$__time = $arr[0];
	$__hash = $arr[1];

	if($time - $__time > 6 * 3600) {
		message(-1, '表单超过 6 个小时没有活动，请重新提交。');
	}

	if($__hash != md5($ip.$useragent.$conf['auth_key'].$__time)) {
		message(-1, '检测到不安全的 POST 提交，可以通过关闭防灌水插件(xn_antispawn)来解决此问题。');
	} else {
		//$_SESSION['__anti_xss__'] = '';
	}



	/*
	if(!($__anti_xss__post == $__anti_xss__cookie)) {
		message(-1, 'CSRF checked!');
	}
	*/
}

?>