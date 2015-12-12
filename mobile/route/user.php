<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'login') {

	if($method == 'GET') {

		$referer = user_http_referer();

		$header['title'] = '用户登录';
		
		include './mobile/view/user_login.htm';

	}

} elseif($action == 'create') {

	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限，请明天再来。');
	user_check_flood($longip) AND message(3, '您当前 IP 注册太频繁，请稍后再注册。');
	
	if($method == 'GET') {

		$referer = user_http_referer();

		$header['title'] = '创建用户';
		
		include './mobile/view/user_create.htm';

	}

// 设置密码，创建用户
} elseif($action == 'setpw') {
	
	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限，请明天再来。');
	
	$email = online_get('create_email');
	$verifycode = online_get('create_verifycode');

	(empty($email) || ($conf['user_create_email_on'] && empty($verifycode))) AND message(-1, '请返回填写数据');
	
	$user = user_read_by_email($email);
	$user AND message(1, 'EMAIL 已经注册。');
	
	if($method == 'GET') {
		
		include './mobile/view/user_setpw.htm';
		
	}
	
} elseif($action == 'logout') {
	
	$user = user_guest();
	user_token_clean('/', '', 'bbs');
	
	$uid = 0;
	$gid = 0;
	
	// 更新在线
	online_save(TRUE);
	online_list_cache_delete();
	
	header('Location: ./');

// 获取当前用户的信息
} elseif($action == 'read') {
	
	$user = user_read($uid);
	$agreelist = myagree_find_by_uid($uid);
	
	empty($user) AND $user = user_guest();
	user_ajax_message($user);


} elseif($action == 'agree') {

	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10;
	$totalnum = $_user['myagrees'];
	$pages = simple_pages("mobile/user-agree-$_uid-{page}.htm", $totalnum, $page, $pagesize);
	$threadlist = myagree_find_by_uid($_uid, $page, $pagesize);
		
	include './mobile/view/user_agree.htm';

} elseif($action == 'thread') {

	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10; //$conf['pagesize'];
	$totalnum = $_user['threads'];
	$pages = simple_pages("mobile/user-thread-$_uid-{page}.htm", $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
		
	include './mobile/view/user_thread.htm';
	
} elseif($action == 'resetpw') {
	
	$email = online_get('reset_email');
	$verifycode = online_get('reset_verifycode');
	(empty($email) || empty($verifycode)) AND message(0, '数据为空，请返回上一步重新填写。');
	
	$_user = user_read_by_email($email);
	empty($_user) AND message(0, '用户不存在');
	$_uid = $_user['uid'];
	
	if($method == 'GET') {

		$header['title'] = '重置密码';
		
		include './mobile/view/user_resetpw.htm';

	}
		
} else {
	
	$_uid = param(1, 0);
	$pid = param(2, 0); // 接受 pid，通过 pid 查询 userip
	if($_uid == 0) {
		$post = post_read($pid);
		$_ip = long2ip($post['userip']);
		$_ip_url = xn_urlencode($_ip);
		$banip = banip_read_by_ip($_ip);
		$_user = user_guest();
	} else {
		$banip = array();
		$_user = user_read($_uid);
		$_ip = long2ip($_user['create_ip']);
		$_ip_url = xn_urlencode($_ip);
	}
	
	$header['title'] = $_user['username'];
	
	include './mobile/view/user_profile.htm';
}

// 获取用户来路
function user_http_referer() {
	$referer = param('referer'); // 优先从参数获取
	empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
	$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer); // 干掉特殊字符
	if(!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) || strpos($referer, 'user-login.htm') !== FALSE || strpos($referer, 'user-logout.htm') !== FALSE || strpos($referer, 'user-create.htm') !== FALSE) {
		$referer = 'mobile/';
	}
	return $referer;
}

// 干掉敏感信息
function user_ajax_info(&$user) {
	if(isset($user['password'])) {
		user_safe_info($user);
	}
	
	// 获取用户关注的信息，最近100条，仅仅返回 pid
	$myagreelist = myagree_find_by_uid($user['uid'], 1, 100);
	foreach ($myagreelist as $k=>$v) {
		$myagreelist[$k] = $k;
	}
	$user['myagreelist'] = $myagreelist;
	message(0, $user);
	
}

?>