<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(APP_PATH.'plugin/xn_qq_login/model/qq_login.func.php');

$action = param(1);

$return_url = http_url_path().url('qq_login-return');

if(empty($action)) {
	
	$link = qq_login_link($return_url);
	
	http_location($link);

} elseif($action == 'return') {
	
	$qq_login = kv_get('qq_login');
	$appid = $qq_login['appid'];
	$appkey = $qq_login['appkey'];
	
	//$state = param('state');
	$code = param('code');
	
	// token 保存起来，提高速度
	$token = qq_login_get_token($appid, $appkey, $code, $return_url);
	!$token AND message($errno, $errstr);
	
	// 获取 openid
	$openid = qq_login_get_openid_by_token($token);
	if(!$openid) {
		message(-1, '获取 openid 失败，错误原因：'.$errstr);
	}
	// 如果有 openid，则直接自动登陆
	$user = qq_login_read_user_by_openid($openid);
	if(!$user) {
		$qquser = qq_login_get_user_by_openid($openid, $token, $appid);
		$user = qq_login_create_user($qquser['nickname'], $qquser['figureurl_qq_2'], $openid);
	}
	
	$uid = $user['uid'];
	
	user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));
	
	$_SESSION['uid'] = $uid;
	user_token_set($uid);
	
	message(0, jump('登陆成功', http_referer(), 2));
}


?>
