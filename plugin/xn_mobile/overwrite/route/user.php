<?php

!defined('DEBUG') AND exit('Access Denied.');

include APP_PATH.'plugin/xn_mobile/model/sms.func.php';

$action = param(1);

is_numeric($action) AND $action = '';

// hook user_start.php
	
$kv_mobile = kv_get('mobile_setting');
$login_type = $kv_mobile['login_type'];

if(empty($action)) {

        // hook user_index_start.php

        $_uid = param(1, 0);
        empty($_uid) AND $_uid = $uid;
        $_user = user_read($_uid);
        
       // empty($_user) AND message(-1, lang('user_not_exists'));
        $header['title'] = $_user['username'];
        $header['mobile_title'] = $_user['username'];

        // hook user_index_end.php

	include _include(APP_PATH.'view/htm/user.htm');
	
} elseif($action == 'thread') {

        // hook user_thread_start.php

        $_uid = param(2, 0);
        empty($_uid) AND $_uid = $uid;
        $_user = user_read($_uid);
        
        empty($_user) AND message(-1, lang('user_not_exists'));
        $header['title'] = $_user['username'];
        $header['mobile_title'] = $_user['username'];

        $page = param(3, 1);
        $pagesize = 20;
        $totalnum = $_user['threads'];
        $pagination = pagination(url("user-thread-$_uid-{page}"), $totalnum, $page, $pagesize);
        $threadlist = mythread_find_by_uid($_uid, $page, $pagesize);
        thread_list_access_filter($threadlist, $gid);

        // hook user_thread_end.php
       
	include _include(APP_PATH.'view/htm/user_thread.htm');
	
} elseif($action == 'login') {

	// hook user_login_get_post.php

	if($method == 'GET') {

		// hook user_login_get_start.php
		
		$referer = user_http_referer();
			
		$header['title'] = lang('user_login');
		
		// hook user_login_get_end.php
		
		include _include(APP_PATH.'view/htm/user_login.htm');

	} else if($method == 'POST') {

		// hook user_login_post_start.php

		$mobile = param('mobile');			// 邮箱或者手机号 / email or mobile
		empty($mobile) AND message('mobile', lang('please_input').lang('login_type_'.$login_type));
		if(is_mobile($mobile, $err)) {
			 $_user = user_read_by_mobile($mobile);
			 empty($_user) AND message('mobile', lang('mobile_not_exists'));
		} elseif(is_email($mobile, $err)) {
			($login_type != 1 && $login_type != 2) AND message('mobile', lang('not_allow_login_by_email'));
			$_user = user_read_by_email($mobile);
			 empty($_user) AND message('mobile', lang('email_not_exists'));
		} else {
			($login_type != 1 && $login_type != 3) AND message('mobile', lang('not_allow_login_by_username'));
			$_user = user_read_by_username($mobile);
			 empty($_user) AND message('mobile', lang('username_not_exists'));
		}
		
		
		$password = param('password');
		!is_password($password, $err) AND message('password', $err);
		$check = (md5($password.$_user['salt']) == $_user['password']);
		// hook user_login_post_password_check_after.php
		!$check AND message('password', lang('password_incorrect'));

		// 更新登录时间和次数
		// update login times
		user_update($_user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

		// 全局变量 $uid 会在结束后，在函数 register_shutdown_function() 中存入 session (文件: model/session.func.php)
		// global variable $uid will save to session in register_shutdown_function() (file: model/session.func.php)
		$uid = $_user['uid'];
		
		$_SESSION['uid'] = $uid;
		
		user_token_set($_user['uid']);
		
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
		
		$mobile = param('mobile');
		$username = param('username');
		$password = param('password');
		$code = param('code');
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		empty($username) AND message('username', lang('please_input_username'));
		empty($password) AND message('password', lang('please_input_password'));
		
		if($kv_mobile['user_create_on']) {
			$sess_mobile = _SESSION('user_create_mobile');
			$sess_code = _SESSION('user_create_code');
			empty($sess_code) AND message('code', lang('click_to_get_verify_code'));
			empty($sess_mobile) AND message('code', lang('click_to_get_verify_code'));
			$mobile != $sess_mobile AND message('code', lang('verify_code_incorrect'));
			$code != $sess_code AND message('code', lang('verify_code_incorrect'));
		}
		
		// 手机是否注册
		!is_mobile($mobile, $err) AND message('mobile', $err);
		$_user = user_read_by_mobile($mobile);
		$_user AND message('mobile', lang('mobile_is_in_use'));
		
		// 用户名是否注册
		!is_username($username, $err) AND message('username', $err);
		$_user = user_read_by_username($username);
		$_user AND message('username', lang('username_is_in_use'));
		
		// 密码长度是否正确
		!is_password($password, $err) AND message('password', $err);
		
		$salt = xn_rand(16);
		$randuid = xn_rand(8);
		$pwd = md5($password.$salt);
		$gid = 101;
		$hostname = _SERVER('HTTP_HOST');
		$email = "$randuid@$hostname";
		$_user = array (
			'username' => $username,
			'email' => $email,
			'mobile' => $mobile,
			'password' => $pwd,
			'salt' => $salt,
			'gid' => $gid,
			'create_ip' => $longip,
			'create_date' => $time,
			'logins' => 1,
			'login_date' => $time,
			'login_ip' => $longip,
		);
		$uid = user_create($_user);
		$uid === FALSE AND message('mobile', lang('user_create_failed'));
		$_user = user_read($uid);
		user_update($uid, array('email'=>$uid.'@'.$hostname));
	
		// 更新 session
		
		unset($_SESSION['user_create_mobile']);
		unset($_SESSION['user_create_code']);
		$_SESSION['uid'] = $uid;
		user_token_set($uid);
		
		$extra = array('token'=>user_token_gen($uid));
		
		// hook user_create_post_end.php
		
		message(0, lang('user_create_sucessfully'), $extra);
	}

} elseif($action == 'logout') {
	
	// hook user_logout_start.php
	
	$uid = 0;
	$_SESSION['uid'] = $uid;
	user_token_clear();
	
	// hook user_logout_end.php
	
	message(0, jump(lang('logout_successfully'), http_referer(), 1));
	//message(0, jump('退出成功', './', 1));
	
// 重设密码第 1 步 | reset password first step
} elseif($action == 'resetpw') {
	
	// hook user_resetpw_get_post.php
	
	empty($kv_mobile['user_resetpw_on']) AND message(-1, lang('user_resetpw_not_on'));
		
	if($method == 'GET') {

		// hook user_resetpw_get_start.php
		
		$header['title'] = lang('resetpw');
		
		// hook user_resetpw_get_end.php
		
		include _include(APP_PATH.'view/htm/user_resetpw.htm');

	} else if($method == 'POST') {
		
		// hook user_resetpw_post_start.php
		
		$mobile = param('mobile');
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		!is_mobile($mobile, $err) AND message('mobile', $err);
		
		$_user = user_read_by_mobile($mobile);
		!$_user AND message('mobile', lang('mobile_is_not_in_use'));

		$code = param('code');
		empty($code) AND message('code', lang('please_input_verify_code'));
		
		$sess_mobile = _SESSION('user_resetpw_mobile');
		$sess_code = _SESSION('user_resetpw_code');
		empty($sess_code) AND message('code', 'click_to_get_verify_code');
		empty($sess_mobile) AND message('code', 'click_to_get_verify_code');
		$mobile != $sess_mobile AND message('code', lang('verify_code_incorrect'));
		$code != $sess_code AND message('code', lang('verify_code_incorrect'));
	
		$_SESSION['resetpw_verify_ok'] = 1;
		
		// hook user_resetpw_post_end.php
		
		message(0, lang('check_ok_to_next_step'));
	}


// 重设密码第 3 步 | reset password step 3
} elseif($action == 'resetpw_complete') {
	
	// hook user_resetpw_complate_get_post.php
	
	// 校验数据
	$mobile = _SESSION('user_resetpw_mobile');
	$resetpw_verify_ok = _SESSION('resetpw_verify_ok');
	(empty($mobile) || empty($resetpw_verify_ok)) AND message(-1, lang('data_empty_to_last_step'));
	
	$_user = user_read_by_mobile($mobile);
	empty($_user) AND message(-1, lang('mobile_not_exists'));
	$_uid = $_user['uid'];
	
	if($method == 'GET') {
		// hook user_resetpw_complate_get_start.php
		
		$header['title'] = lang('resetpw');
		
		// hook user_resetpwcomplate__get_end.php
		include _include(APP_PATH.'view/htm/user_resetpw_complete.htm');

	} else if($method == 'POST') {
		// hook user_resetpw_complate_post_start.php
		
		$password = param('password');
		empty($password) AND message('password', lang('please_input_password'));
		
		$salt = $_user['salt'];
		$password = md5($password.$salt);
		user_update($_uid, array('password'=>$password));
		
		!is_password($password, $err) AND message('password', $err);
		
		unset($_SESSION['user_resetpw_mobile']);
		unset($_SESSION['user_resetpw_code']);
		unset($_SESSION['resetpw_verify_ok']);
		
		// hook user_resetpw_complate_post_end.php
		
		message(0, lang('modify_successfully'));
		
	}

// 发送验证码
} elseif($action == 'send_code') {
	
	$method != 'POST' AND message(-1, lang('method_error'));
	
	// hook user_sendcode_start.php
	
	$action2 = param(2);
	
	// 创建用户
	if($action2 == 'user_create') {
		
		$mobile = param('mobile');
		
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		!is_mobile($mobile, $err) AND message('mobile', $err);
		empty($kv_mobile['user_create_on']) AND message(-1, lang('mobile_verify_not_on'));
		$_user = user_read_by_mobile($mobile);
		!empty($_user) AND message('mobile', lang('mobile_is_in_use'));
		
		$code = rand(100000, 999999);
		$_SESSION['user_create_mobile'] = $mobile;
		$_SESSION['user_create_code'] = $code;
		
	/*
	// 验证码登陆
	} elseif($action2 == 'user_login') {
		
		$mobile = param('mobile');
		
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		!is_mobile($mobile, $err) AND message('mobile', $err);
		empty($kv_mobile['user_create_on']) AND message(-1, lang('mobile_verify_not_on'));
		$user = user_read_by_mobile($mobile);
		empty($user) AND message('mobile', lang('mobile_is_not_in_use'));
		
		$code = rand(100000, 999999);
		$_SESSION['user_login_mobile'] = $mobile;
		$_SESSION['user_login_code'] = $code;
	*/
	
	// 重置密码，往老地址发送
	} elseif($action2 == 'user_resetpw') {
		
		$mobile = param('mobile');
		
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		!is_mobile($mobile, $err) AND message('mobile', $err);
		empty($kv_mobile['user_resetpw_on']) AND message(-1, lang('user_resetpw_not_on'));
		$_user = user_read_by_mobile($mobile);
		empty($_user) AND message('mobile', lang('mobile_is_not_in_use'));
		
		$code = rand(100000, 999999);
		$_SESSION['user_resetpw_mobile'] = $mobile;
		$_SESSION['user_resetpw_code'] = $code;

	
	// 绑定 mobile，往新号码发送
	} elseif($action2 == 'bind_mobile') {
		
		empty($user) AND message(-1, lang('please_login'));
		$mobile = param('mobile');
		
		empty($mobile) AND message('mobile', lang('please_input_mobile'));
		!is_mobile($mobile, $err) AND message('mobile', $err);
		empty($kv_mobile['user_create_on']) AND message(-1, lang('mobile_verify_not_on'));
		$_user = user_read_by_mobile($mobile);
		!empty($_user) AND message('mobile', lang('mobile_is_in_use'));
		
		$code = rand(100000, 999999);
		$_SESSION['user_bind_mobile'] = $mobile;
		$_SESSION['user_bind_code'] = $code;
		
	// 重新绑定 mobile，往老手机发送
	} elseif($action2 == 'change_mobile') {
		
		empty($user) AND message(-1, lang('please_login'));
		$mobile = $user['mobile'];
		
		empty($kv_mobile['user_create_on']) AND message(-1, lang('mobile_verify_not_on'));
		
		$code = rand(100000, 999999);
		$_SESSION['user_change_mobile'] = $mobile;
		$_SESSION['user_change_code'] = $code;
		
	} else {
		
		message(-1, 'action2 error');
	}
	
	// hook user_send_code_before.php
	$r = sms_send_code($mobile, $code);
	// hook user_send_code_after.php
	
	if($r === TRUE) {
		message(0, lang('send_successfully'));
	} else {
		xn_log($errstr, 'send_mail_error');
		message(-1, $errstr);
	}

// 简单的同步登陆实现：| sync login implement simply
/* 
	将用户信息通过 token 传递给其他系统 | send user information to other system by token
	两边系统将 auth_key 设置为一致，用 xn_encrypt() xn_decrypt() 加密解密。all subsystem set auth_key to correct by xn_encrypt() xn_decrypt()
*/
} elseif($action == 'synlogin') {

	// 检查过来的 token | check token
	$token = param('token');
	$return_url = param('return_url');
	
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
		$s = xn_encrypt($s);
		
		// 将 token 附加到 URL，跳转回去 | add token into URL, jump back
		$url = xn_urldecode($return_url).'?token='.$s;
		//$url = xn_url_add_arg($return_url, 'token', $s);
		http_location($url);
	}

} else {
	
}

// hook user_end.php

// 获取用户来路
if(!function_exists('user_http_referer')) {
	function user_http_referer() {
		// hook user_http_referer_start.php
		$referer = param('referer'); // 优先从参数获取 | GET is priority
		empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
		
		$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer); // 干掉特殊字符 strip special chars
		
		if(
			!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) 
			|| strpos($referer, 'user-login.htm') !== FALSE 
			|| strpos($referer, 'user-logout.htm') !== FALSE 
			|| strpos($referer, 'user-create.htm') !== FALSE 
			|| strpos($referer, 'user-setpw.htm') !== FALSE 
			|| strpos($referer, 'user-resetpw_complete.htm') !== FALSE
		) {
			$referer = './';
		}
		// hook user_http_referer_end.php
		return $referer;
	}
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