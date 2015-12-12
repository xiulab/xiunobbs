<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_send_mail.func.php';

$action = param(1);

if($action == 'login') {

	if($method == 'GET') {

		$referer = user_http_referer();
	
		$header['title'] = '用户登录';
		
		include './pc/view/user_login.htm';

	} else if($method == 'POST') {

		$account = param('account');			// 邮箱或者手机号
		$password = param('password');
		empty($account) AND message(1, '账号为空');
		if(is_email($account, $err)) {
			$user = user_read_by_email($account);
			empty($user) AND message(1, 'Email 不存在');
		} else {
			$user = user_read_by_username($account);
			empty($user) AND message(1, '用户名不存在');
		}

		md5($password.$user['salt']) != $user['password'] AND message(2, '密码错误');

		// 更新登录时间和次数
		user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		$uid = $user['uid'];
		$gid = $user['gid'];
		
		$user['token'] = user_token_set($uid, $gid, $user['password'], $user['avatar'], $user['username'], '', 86400 * 30);

		unset($user['password']);
		unset($user['password_sms']);
		unset($user['salt']);
		
		// 更新在线
		online_list_cache_delete();
		
		user_ajax_info($user);
		message(0, $user);

	}

// 注册第1步：校验 Email/code
} elseif($action == 'create') {

	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限，请明天再来。');
	user_check_flood($longip) AND message(3, '您当前 IP 注册太频繁，请稍后再注册。');
	
	if($method == 'GET') {

		$referer = user_http_referer();

		$header['title'] = '创建用户';
		
		include './pc/view/user_create.htm';

	} else if($method == 'POST') {
				
		$email = param('email');
		$verifycode = param('verifycode');
		
		!is_email($email, $err) AND message(1, $err);
		mb_strlen($email, 'UTF-8') > 40 AND message(1, 'EMAIL 最长为 40 个字符。');
		$user = user_read_by_email($email);
		$user AND message(1, 'EMAIL 已经注册。');
		
		
		if($conf['user_create_email_on']) {
			
			empty($verifycode) AND message(2, '请输入校验码。');
			$email2 = online_get('create_email');
			$verifycode2 = online_get('create_verifycode');
			(empty($email2) || empty($verifycode2)) AND message(2, '请点击获取验证码。');
			
			$verifycode2 != $verifycode AND message(2, '验证码不正确');
		} else {
			online_set('create_email', $email);
		}
		
		message(0, 'Email 可以注册。');
	}

	
// 注册第2步：发送激活邮件/手机短信
} elseif($action == 'sendactive') {
	
	!$conf['user_create_email_on'] AND message(-1, '当前未开启 Email 验证。');
	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check_freq($longip) AND message(0, '发送邮件比较耗费资源，请您休息一会再来。');
	
	$smtplist = include './conf/smtp.conf.php';
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
		
	$email = param('email');
	!is_email($email, $err) AND message(1, $err);
	$r = user_read_by_email($email);
	$r AND message(1, 'Email 已经被注册。');
	
	$rand = rand(1000, 9999);
	
	online_set('create_email', $email);
	online_set('create_verifycode', $rand);
	
	$subject = "账号注册验证码：$rand - 【$conf[sitename]】";
	$message = $subject;
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	
	if($r === TRUE) {
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'mails');
		message(0, '发送成功。');
	} else {
		message(1, $errstr);
	}

// 注册第3步：设置密码，创建用户
} elseif($action == 'setpw') {
	
	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限，请明天再来。');
	
	$email = online_get('create_email');
	$verifycode = online_get('create_verifycode');
	
	empty($email) AND message(-1, '请返回填写数据');

	$user = user_read_by_email($email);
	$user AND message(1, 'EMAIL 已经注册。');
	
	if($method == 'GET') {
		
		include './pc/view/user_setpw.htm';
		
	} else {
		
		// 已经加密过的
		$password = param('password');
		strlen($password) !=  32 AND message(1, '密码格式不正确。');
		
		// email 注册
		$salt = rand(100000, 999999);
		$pwd = md5($password.$salt);
	
		$user = array (
			'username' => $email,
			'email' => $email,
			'password' => $pwd,
			'salt' => $salt,
			'gid' => 101,	// 普通注册用户用户组
			'create_ip' => $longip,
			'create_date' => $time,
			'logins' => 1,
			'login_date' => $time,
			'login_ip' => $longip,
		);
		$uid = user_create($user);
		$uid === FALSE AND message(1, '用户注册失败。');
		$user = user_read($uid);
	
		$gid = $user['gid'];
		
		$user['token'] = user_token_set($uid, $gid, $user['password'], $user['avatar'], $user['username'], 'bbs');
	
		// 更新在线
		online_list_cache_delete();
		
		online_unset('create_email');
		online_unset('create_verifycode');
		
		message(0, $user);
	}

// 退出
} elseif($action == 'logout') {
	
	$user = user_guest();
	user_token_clean();
	
	$uid = 0;
	$gid = 0;
	
	// 更新在线
	online_save(TRUE);
	online_list_cache_delete();
	
	message(0, jump('退出成功', './', 1));

// 获取当前用户的信息
} elseif($action == 'read') {
	
	$user = user_read($uid);
	$agreelist = myagree_find_by_uid($uid);
	
	empty($user) AND $user = user_guest();
	user_ajax_info($user);
	message(0, $user);

// 用户发表的喜欢
} elseif($action == 'agree') {

	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10; //$conf['pagesize'];
	$totalnum = $_user['myagrees'];
	$pages = pages("user-agree-$_uid-{page}.htm", $totalnum, $page, $pagesize);
	$threadlist = myagree_find_by_uid($_uid, $page, $pagesize);
		
	include './pc/view/user_agree.htm';

// 用户发表的主题
} elseif($action == 'thread') {

	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10; //$conf['pagesize'];
	$totalnum = $_user['threads'];
	$pages = pages("user-thread-$_uid-{page}.htm", $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
		
	include './pc/view/user_thread.htm';
	
// 找回密码第1步
} elseif($action == 'findpw') {
	
	if($method == 'GET') {

		$header['title'] = '找回密码';
		
		include './pc/view/user_findpw.htm';

	} else if($method == 'POST') {
		
		$email = param('email');
		!is_email($email, $err) AND message(1, $err);
		$user = user_read_by_email($email);
		!$user AND message(1, 'EMAIL 未被注册');
		
		$verifycode = param('verifycode');
		empty($verifycode) AND message(2, '请输入校验码');
		
		$email2 = online_get('reset_email');
		$verifycode2 = online_get('reset_verifycode');
		(empty($email2) || empty($verifycode2)) AND message(2, '请点击获取验证码');
		
		// 每小时只能尝试 5 次
		$verifytimes = intval(online_get('verifytimes'));
		$verifylastdate = intval(online_get('verifylastdate'));
		if($verifytimes > 5 && $time - $verifylastdate < 3600) {
			message(2, '请稍后重试，每个小时只能尝试5次。');
		}
		if($verifycode2 != $verifycode) {
			$verifytimes++;
			online_set('verifytimes', $verifytimes);
			online_set('verifylastdate', $time);
			message(2, '验证码不正确');
		}
		
		message(0, '检测通过，进入下一步');
	}
	
// 找回密码第2步
// 发送激活邮件/手机短信
} elseif($action == 'sendreset') {
	
	!$conf['user_find_pw_on'] AND message(-1, '当前未开启找回密码功能。');
	$conf['ipaccess_on'] AND $conf['user_find_pw_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check_freq($longip) AND message(0, '发送邮件比较耗费资源，请您休息一会再来。');
	
	$smtplist = include './conf/smtp.conf.php';
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
		
	$email = param('email');
	!is_email($email, $err) AND message(1, $err);
	$r = user_read_by_email($email);
	!$r AND message(1, 'Email 未被注册。');
	
	$rand = rand(100000, 999999);
	
	online_set('reset_email', $email);
	online_set('reset_verifycode', $rand);
	
	$subject = "重设密码验证码：$rand - 【$conf[sitename]】";
	$message = $subject;
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	
	if($r === TRUE) {
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'mails');
		message(0, '发送成功。');
	} else {
		message(1, $errstr);
	}
	
// 找回密码第3步
} elseif($action == 'resetpw') {
	
	$email = online_get('reset_email');
	$verifycode = online_get('reset_verifycode');
	(empty($email) || empty($verifycode)) AND message(0, '数据为空，请返回上一步重新填写。');
	
	$_user = user_read_by_email($email);
	empty($_user) AND message(0, '用户不存在');
	$_uid = $_user['uid'];
	
	if($method == 'GET') {

		$header['title'] = '重置密码';
		
		include './pc/view/user_resetpw.htm';

	} else if($method == 'POST') {
		
		$password = param('password');
		$salt = $_user['salt'];
		$password = md5($password.$salt);
		user_update($_uid, array('password'=>$password));
		
		online_unset('reset_email');
		online_unset('reset_verifycode');
		
		message(0, '修改成功');
		
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
		$banip = banip_read_by_ip($_ip);
		$_ip_url = xn_urlencode($_ip);
		empty($_user) AND message(0, '用户不存在');
	}
	
	$header['title'] = $_user['username'];
	
	include './pc/view/user_profile.htm';
	
}

// 获取用户来路
function user_http_referer() {
	$referer = param('referer'); // 优先从参数获取
	empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
	$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer); // 干掉特殊字符
	if(!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) || strpos($referer, 'user-login.htm') !== FALSE || strpos($referer, 'user-logout.htm') !== FALSE || strpos($referer, 'user-create.htm') !== FALSE || strpos($referer, 'user-setpw.htm') !== FALSE) {
		$referer = './';
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
	
}

function user_auth_check($token) {
	global $time;
	$auth = param(2);
	$s = decrypt($auth);
	empty($s) AND message(-1, '解密失败');
	$arr = explode('-', $s);
	count($arr) != 3 AND message(-1, '数据解密失败');
	list($_ip, $_time, $_uid) = $arr;
	$_user = user_read($_uid);
	empty($_user) AND message(-1, '用户不存在');
	$time - $_time > 3600 AND message(-1, '链接已经过期');
	return $_user;
}

?>
