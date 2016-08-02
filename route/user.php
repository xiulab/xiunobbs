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

		!is_password($password, $err) AND message('password', $err);
		md5($password.$user['salt']) != $user['password'] AND message('password', '密码错误');

		// 更新登录时间和次数
		user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		$uid = $user['uid'];
		
		// hook user_login_post_end.php
		
		message(0, lang('user_login_successfully'));

	}

} elseif($action == 'create') {

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
		$username = param('username');
		$password = param('password');
		empty($email) AND message('email', '请填写邮箱');
		empty($username) AND message('username', '请填写用户名');
		empty($password) AND message('password', '请填写密码');
		
		
		if($conf['user_create_email_on']) {
			$email != _SESSION('create_email') AND message('sendinitpw', '请先点击获取初始密码');
			$password != _SESSION('create_pw') AND message('password', '初始密码不正确');
		}
		
		!is_email($email, $err) AND message('email', $err);
		$_user = user_read_by_email($email);
		$_user AND message('email', 'EMAIL 已经注册');
		
		!is_username($username, $err) AND message('username', $err);
		$_user = user_read_by_username($username);
		$_user AND message('email', '用户名已经存在');
		
		!is_password($password, $err) AND message('password', $err);
		
		// email 注册
		$salt = xn_rand(16);
		$pwd = md5(md5($password).$salt);
		$gid = 101;
		$user = array (
			'username' => $username,
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
		
		$extra = array('token'=>user_token_gen($uid));
		
		// hook user_create_post_end.php
		
		message(0, lang('user_create_sucessfully'), $extra);
	}

// 获取初始密码
} elseif($action == 'sendinitpw') {
	
	// hook user_sendinitpw_start.php
	 
	empty($conf['user_create_email_on']) AND message(-1, '未开启邮箱验证。');
	
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
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hookuser_sendinitpw_sendmail_after.php
	
	if($r === TRUE) {
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

// 获取当前用户的信息，可以提供给接口
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
	$pages = pages(url("user-thread-$_uid-{page}"), $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
		
	// hook user_thread_end.php
	
	include './view/htm/user_thread.htm';
	
// 找回密码第1步
} elseif($action == 'resetpw') {
	
	// hook user_resetpw_get_post.php
	
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = '找回密码';
		
		// hook user_resetpw_get_end.php
		
		include './view/htm/user_resetpw.htm';

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$email = param('email');
		empty($email) AND message('email', '请填写邮箱');
		!is_email($email, $err) AND message('email', $err);
		$user = user_read_by_email($email);
		!$user AND message('email', '邮箱未被注册'); // 此处可能被利用，扫描试探，用来撞裤
		
		$verify_code = param('verify_code');
		empty($verify_code) AND message('verify_code', '请输入校验码');
		
		$resetpw_email = _SESSION('resetpw_email');
		$resetpw_verify_code = _SESSION('resetpw_verify_code');
		(!$resetpw_email || !$resetpw_verify_code) AND message('verify_code', '请点击获取验证码');
		
		// 每小时只能尝试 5 次
		$resetpw_verify_times = intval(_SESSION('resetpw_verify_times'));
		$resetpw_verify_lastdate = intval(_SESSION('resetpw_verify_lastdate'));
		if($resetpw_verify_times > 10 && $time - $resetpw_verify_lastdate < 3600) {
			message('verify_code', '请稍后重试，每个小时只能尝试 10 次。');
		}
		if($resetpw_verify_code != $verify_code) {
			$resetpw_verify_times++;
			$_SESSION['resetpw_verify_times'] = $resetpw_verify_times;
			$resetpw_verify_lastdate && $time - $resetpw_verify_lastdate > 3600 && $_SESSION['resetpw_verify_lastdate'] = $time;
			message('verify_code', '验证码不正确');
		} else {
			$_SESSION['resetpw_verify_ok'] = 1;
		}
		
		// hook user_resetpw_post_end.php
		
		message(0, '检测通过，进入下一步');
	}
	
// 找回密码: 发送验证码
} elseif($action == 'resetpw_sendcode') {
	
	// hook user_sendreset_start.php
	
	// 校验数据
	!$conf['user_resetpw_on'] AND message(-1, '当前未开启找回密码功能。');
	$method != 'POST' AND message(-1, lang('method_error'));
	$email = param('email');
	empty($email) AND message('email', '邮箱不能为空');
	!is_email($email, $err) AND message('email', $err);
	$r = user_read_by_email($email);
	!$r AND message('email', '邮箱未被注册。');
	
	// 发送邮件
	$smtplist = include './conf/smtp.conf.php';
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
	$rand = rand(100000, 999999);
	$_SESSION['resetpw_email'] = $email;
	$_SESSION['resetpw_verify_code'] = $rand;
	$subject = "重设密码验证码：$rand - 【$conf[sitename]】";
	$message = $subject;
	// hook user_sendreset_send_mail_before.php
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hook user_sendreset_send_mail_after.php
	if($r === TRUE) {
		message(0, '发送成功。');
	} else {
		message(-1, $errstr);
	}
	
// 找回密码第3步
} elseif($action == 'resetpw_complete') {
	
	// hook user_resetpw_get_post.php
	
	// 校验数据
	$email = _SESSION('resetpw_email');
	$resetpw_verify_ok = _SESSION('resetpw_verify_ok');
	(empty($email) || empty($resetpw_verify_ok)) AND message(-1, '数据为空，请返回上一步重新填写。');
	
	$_user = user_read_by_email($email);
	empty($_user) AND message(-1, '邮箱不存在');
	$_uid = $_user['uid'];
	
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = '重置密码';
		
		// hook user_resetpw_get_end.php
		
		include './view/htm/user_resetpw_complete.htm';

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$password = param('password');
		empty($password) AND message('password', '请填写密码');
		
		$salt = $_user['salt'];
		$password = md5($password.$salt);
		user_update($_uid, array('password'=>$password));
		
		!is_password($password, $err) AND message('password', $err);
		
		unset($_SESSION['resetpw_email']);
		unset($_SESSION['resetpw_verify_code']);
		unset($_SESSION['resetpw_verify_times']);
		unset($_SESSION['resetpw_verify_lastdate']);
		unset($_SESSION['resetpw_verify_ok']);
		
		// hook user_resetpw_post_end.php
		
		message(0, '修改成功');
		
	}

// hook user_action_add.php
	
} else {
	
	// hook user_profile_start.php
	
	$_uid = param(1, 0);
	$_user = user_read($_uid);
	
	empty($_user) AND message(-1, '用户不存在');
	
	$header['title'] = $_user['username'];
	$header['mobile_title'] = $_user['username'];
	
	$page = param(2, 1);
	$pagesize = 10;
	$pagination = pagination(url("user-$_uid-{page}"), $_user['threads'], $page, $pagesize);
	
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
	
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
