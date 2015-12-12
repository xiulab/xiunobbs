<?php

define('DEBUG', 0); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';
include './plugin/xn_qq_login/qq_login.func.php';

$grouplist = group_list_cache();
$forumlist = forum_list_cache();

$action = param('action');

$http_url_path = http_url_path();
$return_url = $http_url_path.'qq_login.php?action=return_url';

if($action == 'login') {
	
	$link = qq_login_link($return_url);
	header("Location: $link");

// return url
} elseif($action == 'return_url') {
	
	$qq = kv_get('qq_login');
	$appid = $qq['appid'];
	$appkey = $qq['appkey'];
	//$state = param('state');
	$code = param('code');
	
	// token 保存起来，提高速度
	$token = qq_login_get_token($appid, $appkey, $code, $return_url);
	!$token AND message($errno, $errstr);
	
	// 获取 openid
	$openid = qq_login_get_openid_by_token($token);
	
	// 如果有 openid，则直接自动登陆
	$user = qq_login_read_user_by_openid($openid);
	if(!$user) {
		$qquser = qq_login_get_user_by_openid($openid, $token, $appid);
		$user = qq_login_create_user($qquser['nickname'], $qquser['figureurl_qq_2'], $openid);
	}
	
	$r = user_token_set($user['uid'], $user['gid'], $user['password'], $user['avatar'], $user['username'], 'bbs');
	
	// 登陆成功
	header('Location: ../../');
	exit;
}


?>