<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_send_mail.func.php';

$action = param(1);

// hook user_action_before.php

if($action == 'login') {

	// hook user_login_get_post.php
	
	if($method == 'GET') {

		// hook user_login_get_start.php
		
		$referer = user_http_referer();
	
		$header['title'] = '用户登录';
		
		// hook user_login_get_end.php
		
		include './view/htm/user_login.htm';

	} else if($method == 'POST') {

		// hook user_login_post_start.php
		
		$email = param('email');			// 邮箱或者手机号
		$password = param('password');
		empty($email) AND message('email', '请填写 Email');
		if(is_email($email, $err)) {
			$user = user_read_by_email($email);
			empty($user) AND message('email', 'Email 不存在');
		} else {
			$user = user_read_by_username($email);
			empty($user) AND message('email', '用户名不存在');
		}

		md5($password.$user['salt']) != $user['password'] AND message('password', '密码错误');

		// 更新登录时间和次数
		user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		$uid = $user['uid'];
		
		// hook user_login_post_end.php
		
		message(0, lang('user_login_successfully'));

	}

// 注册第1步：校验 Email/code
} elseif($action == 'create') {

	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限，请明天再来。');
	user_check_flood($longip) AND message(-1, '您当前 IP 注册太频繁，请稍后再注册。');
	
	// hook user_create_get_post.php
	
	if($method == 'GET') {

		
		// hook user_create_get_start.php
		
		$referer = user_http_referer();
		$header['title'] = '创建用户';
		
		// hook user_create_get_end.php
		
		include './view/htm/user_create.htm';

	} else if($method == 'POST') {
				
		// hook user_create_post_start.php
		
		$email = param('email');
		$password = param('password');
		
		$email != _SESSION('create_email') AND message('sendinitpw', '请先点击获取初始密码');
		$password != _SESSION('create_pw') AND message('password', '初始密码不正确');
		
		$user = user_read_by_email($email);
		$user AND message('email', 'EMAIL 已经注册。');
		
		// email 注册
		$salt = xn_rand(16);
		$pwd = md5(md5($password).$salt);
		$gid = 101;
		$user = array (
			'username' => $email,
			'email' => $email,
			'password' => $pwd,
			'salt' => $salt,
			'gid' => $gid,	// 普通注册用户用户组
			'create_ip' => $longip,
			'create_date' => $time,
			'logins' => 1,
			'login_date' => $time,
			'login_ip' => $longip,
		);
		$uid = user_create($user);
		$uid === FALSE AND message('email', '用户注册失败');
		$user = user_read($uid);
	
		// 更新 session
		
		unset($_SESSION['create_email']);
		unset($_SESSION['create_pw']);
		
		// hook user_create_post_end.php
		
		message(0, lang('user_create_sucessfully'));
	}

// 获取初始密码
} elseif($action == 'sendinitpw') {
	
	// hook user_sendinitpw_start.php
	
	$conf['ipaccess_on'] AND $conf['user_create_email_on'] AND !ipaccess_check($longip, 'mails') AND message(-1, '您的 IP 今日发送邮件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check_freq($longip) AND message(0, '发送邮件比较耗费资源，请您休息一会再来。');
	
	$smtplist = include './conf/smtp.conf.php';
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
		
	$email = param('email');
	!is_email($email, $err) AND message('email', $err);
	$r = user_read_by_email($email);
	$r AND message('email', 'Email 已经被注册。');
	
	// 八位随机密码
	$rand = rand(10000000, 99999999);
	
	$_SESSION['create_email'] = $email;
	$_SESSION['create_pw'] = $rand;
	
	$subject = "您的注册初始密码为：$rand ，为了您的账户安全，请及时修改密码 - 【$conf[sitename]】";
	$message = $subject;
	
	// hookuser_sendinitpw_sendmail_before.php
	$r = DEBUG ? TRUE : xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hookuser_sendinitpw_sendmail_after.php
	
	if($r === TRUE) {
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'mails');
		message(0, lang('user_send_init_pw_sucessfully'));
	} else {
		message(1, $errstr);
	}
// 退出
} elseif($action == 'logout') {
	
	// hook user_logout_start.php
	
	$uid = 0;
	
	// hook user_logout_end.php
	
	message(0, lang('logout_success'));
	//message(0, jump('退出成功', './', 1));

// 获取当前用户的信息
} elseif($action == 'read') {
	
	// hook user_read_start.php
	
	$user = user_read($uid);
	
	empty($user) AND $user = user_guest();
	user_ajax_info($user);
	
	// hook user_read_end.php
	
	message(0, $user);

// 用户发表的主题
} elseif($action == 'thread') {

	// hook user_thread_start.php
	
	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10; //$conf['pagesize'];
	$totalnum = $_user['threads'];
	$pages = pages("user-thread-$_uid-{page}.htm", $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
		
	// hook user_thread_end.php
	
	include './view/htm/user_thread.htm';
	
// 找回密码第1步
} elseif($action == 'findpw') {
	
	// hook user_findpw_get_post.php
	
	if($method == 'GET') {

		// hook user_findpw_get_start.php
		
		$header['title'] = '找回密码';
		
		// hook user_findpw_get_end.php
		
		include './view/htm/user_findpw.htm';

	} else if($method == 'POST') {
		
		// hook user_findpw_post_start.php
		
		$email = param('email');
		!is_email($email, $err) AND message(1, $err);
		$user = user_read_by_email($email);
		!$user AND message(1, 'EMAIL 未被注册');
		
		$verifycode = param('verifycode');
		empty($verifycode) AND message(2, '请输入校验码');
		
		$email2 = $_SESSION['reset_email'];
		$verifycode2 = $_SESSION['reset_verifycode'];
		(empty($email2) || empty($verifycode2)) AND message(2, '请点击获取验证码');
		
		// 每小时只能尝试 5 次
		$verifytimes = intval($_SESSION['verifytimes']);
		$verifylastdate = intval($_SESSION['verifylastdate']);
		if($verifytimes > 5 && $time - $verifylastdate < 3600) {
			message(2, '请稍后重试，每个小时只能尝试5次。');
		}
		if($verifycode2 != $verifycode) {
			$verifytimes++;
			$_SESSION['verifytimes'] = $verifytimes;
			$_SESSION['verifylastdate'] = $time;
			message(2, '验证码不正确');
		}
		
		// hook user_findpw_post_end.php
		
		message(0, '检测通过，进入下一步');
	}
	
// 找回密码第2步
// 发送激活邮件/手机短信
} elseif($action == 'sendreset') {
	
	// hook user_sendreset_start.php
	
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
	
	$_SESSION['reset_email'] = $email;
	$_SESSION['reset_verifycode'] = $rand;
	
	$subject = "重设密码验证码：$rand - 【$conf[sitename]】";
	$message = $subject;
	
	// hook user_sendreset_send_mail_before.php
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	
	if($r === TRUE) {
		
		// hook user_sendreset_send_mail_ok.php
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'mails');
		message(0, '发送成功。');
	} else {
		// hook user_sendreset_send_mail_fail.php
		message(1, $errstr);
	}
	
// 找回密码第3步
} elseif($action == 'resetpw') {
	
	// hook user_resetpw_get_post.php
	
	$email = $_SESSION['reset_email'];
	$verifycode = $_SESSION['reset_verifycode'];
	(empty($email) || empty($verifycode)) AND message(0, '数据为空，请返回上一步重新填写。');
	
	$_user = user_read_by_email($email);
	empty($_user) AND message(0, '用户不存在');
	$_uid = $_user['uid'];
	
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = '重置密码';
		
		// hook user_resetpw_get_end.php
		
		include './view/htm/user_resetpw.htm';

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$password = param('password');
		$salt = $_user['salt'];
		$password = md5($password.$salt);
		user_update($_uid, array('password'=>$password));
		
		unset($_SESSION['reset_email']);
		unset($_SESSION['reset_verifycode']);
		
		// hook user_resetpw_post_end.php
		
		message(0, '修改成功');
		
	}

// hook user_action_add.php
	
} else {
	
	// hook user_profile_start.php
	
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
	
	// hook user_profile_start.php
	
	include './view/htm/user_profile.htm';
	
}

// 获取用户来路
function user_http_referer() {
	// hook user_http_referer_start.php
	$referer = param('referer'); // 优先从参数获取
	empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
	$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer); // 干掉特殊字符
	if(!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) || strpos($referer, 'user-login.htm') !== FALSE || strpos($referer, 'user-logout.htm') !== FALSE || strpos($referer, 'user-create.htm') !== FALSE || strpos($referer, 'user-setpw.htm') !== FALSE) {
		$referer = './';
	}
	// hook user_http_referer_end.php
	return $referer;
}

// 干掉敏感信息
function user_ajax_info(&$user) {
	// hook user_ajax_info_start.php
	if(isset($user['password'])) {
		user_safe_info($user);
	}
	
	// hook user_ajax_info_end.php
	
}

function user_auth_check($token) {
	// hook user_auth_check_start.php
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
	// hook user_auth_check_end.php
	return $_user;
}

?>
