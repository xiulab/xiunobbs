<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(XIUNOPHP_PATH.'xn_send_mail.func.php');

$action = param(1);

// hook user_action_before.php

if($action == 'login') {

	// hook user_login_get_post.php
	
	if($method == 'GET') {

		// hook user_login_get_start.php
		
		$referer = user_http_referer();
	
		$header['title'] = lang('user_login');
		
		// hook user_login_get_end.php
		
		include _include(APP_PATH.'view/htm/user_login.htm');

	} else if($method == 'POST') {

		// hook user_login_post_start.php
		
		$email = param('email');			// 邮箱或者手机号 / email or mobile
		$password = param('password');
		empty($email) AND message('email', lang('email_is_empty'));
		if(is_email($email, $err)) {
			$user = user_read_by_email($email);
			empty($user) AND message('email', lang('email_not_exists'));
		} else {
			$user = user_read_by_username($email);
			empty($user) AND message('email', lang('username_not_exists'));
		}

		!is_password($password, $err) AND message('password', $err);
		md5($password.$user['salt']) != $user['password'] AND message('password', lang('password_incorrect'));

		// 更新登录时间和次数
		// update login times
		user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		// 全局变量 $uid 会在结束后，在函数 register_shutdown_function() 中存入 session (文件: model/session.func.php)
		// global variable $uid will save to session in register_shutdown_function() (file: model/session.func.php)
		$uid = $user['uid'];
		
		$_SESSION['uid'] = $uid;
		
		user_token_set($uid);
		
		// hook user_login_post_end.php
		
		// 设置 token，下次自动登陆。
		
		message(0, lang('user_login_successfully'));

	}

} elseif($action == 'create') {

	// hook user_create_get_post.php
	
	if($method == 'GET') {
		
		// hook user_create_get_start.php
		
		$referer = user_http_referer();
		$header['title'] = lang('create_user');
		
		// hook user_create_get_end.php
		
		include _include(APP_PATH.'view/htm/user_create.htm');

	} else if($method == 'POST') {
				
		// hook user_create_post_start.php
		
		$email = param('email');
		$username = param('username');
		$password = param('password');
		empty($email) AND message('email', lang('please_input_email'));
		empty($username) AND message('username', lang('please_input_username'));
		empty($password) AND message('password', lang('please_input_password'));
		
		
		if($conf['user_create_email_on']) {
			$email != _SESSION('create_email') AND message('sendinitpw', lang('click_to_get_init_pw'));
			$password != md5(_SESSION('create_pw')) AND message('password', lang('init_pw_incorrect'));
		}
		
		!is_email($email, $err) AND message('email', $err);
		$_user = user_read_by_email($email);
		$_user AND message('email', lang('email_is_in_use'));
		
		!is_username($username, $err) AND message('username', $err);
		$_user = user_read_by_username($username);
		$_user AND message('email', lang('username_is_in_use'));
		
		!is_password($password, $err) AND message('password', $err);
		
		$salt = xn_rand(16);
		$pwd = md5($password.$salt);
		$gid = 101;
		$user = array (
			'username' => $username,
			'email' => $email,
			'password' => $pwd,
			'salt' => $salt,
			'gid' => $gid,
			'create_ip' => $longip,
			'create_date' => $time,
			'logins' => 1,
			'login_date' => $time,
			'login_ip' => $longip,
		);
		$uid = user_create($user);
		$uid === FALSE AND message('email', lang('user_create_failed'));
		$user = user_read($uid);
	
		// 更新 session
		
		unset($_SESSION['create_email']);
		unset($_SESSION['create_pw']);
		$_SESSION['uid'] = $uid;
		user_token_set($uid);
		
		$extra = array('token'=>user_token_gen($uid));
		
		// hook user_create_post_end.php
		
		message(0, lang('user_create_sucessfully'), $extra);
	}

} elseif($action == 'sendinitpw') {
	
	// hook user_sendinitpw_start.php
	 
	empty($conf['user_create_email_on']) AND message(-1, lang('email_verify_not_on'));
	
	$smtplist = include _include(APP_PATH.'conf/smtp.conf.php');
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
		
	$email = param('email');
	!is_email($email, $err) AND message('email', $err);
	$r = user_read_by_email($email);
	$r AND message('email', lang('email_is_in_use'));
	
	$rand = rand(100000, 999999);
	
	$_SESSION['create_email'] = $email;
	$_SESSION['create_pw'] = $rand;
	
	$subject = lang('email_create_init_pw_template', array('rand'=>$rand, 'sitename'=>$conf['sitename']));
	$message = $subject;
	
	// hook user_sendinitpw_sendmail_before.php
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hook user_sendinitpw_sendmail_after.php
	
	if($r === TRUE) {
		message(0, lang('user_send_init_pw_sucessfully'));
	} else {
		message(1, $errstr);
	}
	
} elseif($action == 'logout') {
	
	// hook user_logout_start.php
	
	$uid = 0;
	$_SESSION['uid'] = $uid;
	user_token_clear();
	
	// hook user_logout_end.php
	
	message(0, jump(lang('logout_successfully'), http_referer(), 1));
	//message(0, jump('退出成功', './', 1));

// 用户发表的主题
} elseif($action == 'thread') {

	// hook user_thread_start.php
	
	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 10;
	$totalnum = $_user['threads'];
	$pages = pages(url("user-thread-$_uid-{page}"), $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
		
	// hook user_thread_end.php
	
	include _include(APP_PATH.'view/htm/user_thread.htm');
	
// 重设密码第 1 步 | reset password first step
} elseif($action == 'resetpw') {
	
	// hook user_resetpw_get_post.php
	
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = lang('reset_pw');
		
		// hook user_resetpw_get_end.php
		
		include _include(APP_PATH.'view/htm/user_resetpw.htm');

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$email = param('email');
		empty($email) AND message('email', lang('please_input_email'));
		!is_email($email, $err) AND message('email', $err);
		$user = user_read_by_email($email);
		!$user AND message('email', lang('email_is_not_in_use'));
		
		$verify_code = param('verify_code');
		empty($verify_code) AND message('verify_code', lang('please_input_verify_code'));
		
		$resetpw_email = _SESSION('resetpw_email');
		$resetpw_verify_code = _SESSION('resetpw_verify_code');
		(!$resetpw_email || !$resetpw_verify_code) AND message('verify_code', lang('click_to_get_verify_code'));
		
		$resetpw_verify_times = intval(_SESSION('resetpw_verify_times'));
		$resetpw_verify_lastdate = intval(_SESSION('resetpw_verify_lastdate'));
		$times = 10;
		if($resetpw_verify_times > $times && $time - $resetpw_verify_lastdate < 3600) {
			message('verify_code', lang('verify_code_try_too_frequently', $times));
		}
		if($resetpw_verify_code != $verify_code) {
			$resetpw_verify_times++;
			$_SESSION['resetpw_verify_times'] = $resetpw_verify_times;
			$resetpw_verify_lastdate && $time - $resetpw_verify_lastdate > 3600 && $_SESSION['resetpw_verify_lastdate'] = $time;
			message('verify_code', lang('verify_code_incorrect'));
		} else {
			$_SESSION['resetpw_verify_ok'] = 1;
		}
		
		// hook user_resetpw_post_end.php
		
		message(0, lang('check_ok_to_next_step'));
	}

// 重设密码第 2 步 | reset password step 2
} elseif($action == 'resetpw_sendcode') {
	
	// hook user_sendreset_start.php
	
	!$conf['user_resetpw_on'] AND message(-1, lang('reset_pw_not_on'));
	$method != 'POST' AND message(-1, lang('method_error'));
	$email = param('email');
	empty($email) AND message('email', lang('email_is_empty'));
	!is_email($email, $err) AND message('email', $err);
	$r = user_read_by_email($email);
	!$r AND message('email', lang('email_is_not_in_use'));
	
	// 发送邮件 | send mail
	$smtplist = include _include(APP_PATH.'conf/smtp.conf.php');
	$n = array_rand($smtplist);
	$smtp = $smtplist[$n];
	$rand = rand(1000000, 999999);
	$_SESSION['resetpw_email'] = $email;
	$_SESSION['resetpw_verify_code'] = $rand;
	$subject = lang('reset_pw_email_template', array('rand'=>$rand, 'sitename'=>$conf['sitename']));
	$message = $subject;
	// hook user_sendreset_send_mail_before.php
	$r = xn_send_mail($smtp, $conf['sitename'], $email, $subject, $message);
	// hook user_sendreset_send_mail_after.php
	if($r === TRUE) {
		message(0, lang('send_successfully'));
	} else {
		message(-1, $errstr);
	}
	
// 重设密码第 3 步 | reset password step 3
} elseif($action == 'resetpw_complete') {
	
	// hook user_resetpw_get_post.php
	
	// 校验数据
	$email = _SESSION('resetpw_email');
	$resetpw_verify_ok = _SESSION('resetpw_verify_ok');
	(empty($email) || empty($resetpw_verify_ok)) AND message(-1, lang('data_empty_to_last_step'));
	
	$_user = user_read_by_email($email);
	empty($_user) AND message(-1, lang('email_not_exists'));
	$_uid = $_user['uid'];
	
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = lang('reset_pw');
		
		// hook user_resetpw_get_end.php
		
		include _include(APP_PATH.'view/htm/user_resetpw_complete.htm');

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$password = param('password');
		empty($password) AND message('password', lang('please_input_password'));
		
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
		
		message(0, lang('modify_successfully'));
		
	}

// 简单的同步登陆实现：| sync login implement simply
/* 
	将用户信息通过 token 传递给其他系统 | send user information to other system by token
	两边系统将 auth_key 设置为一致，用 xn_encrypt() xn_decrypt() 加密解密。all subsystem set auth_key to correct by xn_encrypt() xn_decrypt()
*/
} elseif($action == 'synlogin') {

	// 检查过来的 token | check token
	$token = param(2);
	$return_url = param(3);
	$s = xn_decrypt($token);
	!$s AND message(-1, lang('unauthorized_access'));
	list($_time, $_useragent) = explode("\t", $s);
	$useragent != $_useragent AND message(-1, lang('authorized_get_failed'));
	
	empty($_SESSION['return_url']) AND $_SESSION['return_url'] = $return_url;
	if(!$uid) {
		http_location(url('user-login'));
	} else {
		$return_url = _SESSION('return_url');
		
		empty($return_url) AND message(-1, lang('request_synlogin_again'));
		unset($_SESSION['return_url']);
		
		$arr = array(
			'uid'=>$user['uid'],
			'gid'=>$user['gid'],
			'username'=>$user['username'],
			'avatar_url'=>$user['avatar_url'],
			'email'=>$user['email'],
			'mobile'=>$user['mobile'],
		);
		$s = xn_json_encode($arr);
		$s = xn_encrypt($s, $key);
		
		// 将 token 附加到 URL，跳转回去 | add token into URL, jump back
		$return_url = xn_urldecode($return_url);
		$url = xn_url_add_arg($return_url, 'token', $s);
		http_location($url);
	}
	
} else {
	
	// hook user_profile_start.php
	
	$_uid = param(1, 0);
	$_user = user_read($_uid);
	$page = param(2, 1);
	$pagesize = 10;
	$thread_list_from_default = 1;
	$active = 'default';
	
	empty($_user) AND message(-1, lang('user_not_exists'));
	$header['title'] = $_user['username'];
	$header['mobile_title'] = $_user['username'];
	
	// hook user_profile_thread_list_before.php
	
	if($thread_list_from_default == 1) {
		$pagination = pagination(url("user-$_uid-{page}"), $_user['threads'], $page, $pagesize);
		$threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
	}
	
	// hook user_profile_start.php
	
	include _include(APP_PATH.'view/htm/user_profile.htm');
	
}

// hook user_end.php

// 获取用户来路
function user_http_referer() {
	// hook user_http_referer_start.php
	$referer = param('referer'); // 优先从参数获取 | GET is priority
	empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
	$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer); // 干掉特殊字符 strip special chars
	if(!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) || strpos($referer, 'user-login.htm') !== FALSE || strpos($referer, 'user-logout.htm') !== FALSE || strpos($referer, 'user-create.htm') !== FALSE || strpos($referer, 'user-setpw.htm') !== FALSE || strpos($referer, 'user-resetpw_complete.htm') !== FALSE) {
		$referer = './';
	}
	// hook user_http_referer_end.php
	return $referer;
}

function user_auth_check($token) {
	// hook user_auth_check_start.php
	global $time;
	$auth = param(2);
	$s = decrypt($auth);
	empty($s) AND message(-1, lang('decrypt_failed'));
	$arr = explode('-', $s);
	count($arr) != 3 AND message(-1, lang('encrypt_failed'));
	list($_ip, $_time, $_uid) = $arr;
	$_user = user_read($_uid);
	empty($_user) AND message(-1, lang('user_not_exists'));
	$time - $_time > 3600 AND message(-1, lang('link_has_expired'));
	// hook user_auth_check_end.php
	return $_user;
}

?>
