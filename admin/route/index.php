<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'login') {

	if($method == 'GET') {

		// 判断已登录就跳转到后台首页
		if($gid == 1) {
			message(0, jump('您已经登录后台了，点击进入后台。', 'admin/', 5));
		}

		$header['title'] = '管理登陆';
		
		include "./admin/view/index_login.htm";

	} else if($method == 'POST') {

		$username = param('username');
		$password = param('password');

		$user = user_read_by_username($username);

		if(empty($user)) {
			xn_log('username: '.$username.' does not exist', 'admin_login_error');
			message(1, '用户名不存在');
		}

		if(md5($password.$user['salt']) != $user['password']) {
			xn_log('password error. uid:'.$user['uid'].' - ******'.substr($password, -6), 'admin_login_error');
			message(2, '密码错误');
		}

		// gid 不合格的用户不允许登录
		if($user['gid'] != 1) {
			xn_log('login illegal. uid:'.$user['uid'], 'admin_login_error');
			message(1, '无权访问');
		}

		// 更新登录信息
		user_update($user['uid'], array(
			'login_ip' => $longip,
			'login_date' => $time,
			'logins+' => 1,
		));

		user_token_set($user['uid'], $user['gid'], $user['password'], $user['avatar'], $user['username'], 'bbs');

		// 记录日志
		xn_log('login successed. uid:'.$user['uid'], 'admin_login');

		unset($user['password']);
		unset($user['password_sms']);
		unset($user['salt']);
		
		message(0, $user);

	}

} elseif ($action == 'logout') {

	user_token_clean('/', '', 'bbs');

	header('Location:index-login.htm');
	exit;

} elseif ($action == 'phpinfo') {
	
	phpinfo();
	exit;
	
} else {

	$header['title'] = '后台管理';
	
	$info = array();
	$info['disable_functions'] = ini_get('disable_functions');
	$info['allow_url_fopen'] = ini_get('allow_url_fopen') ? '是' : '否';
	$info['safe_mode'] = ini_get('safe_mode') ? '是' : '否';
	empty($info['disable_functions']) && $info['disable_functions'] = '无';
	$info['upload_max_filesize'] = ini_get('upload_max_filesize');
	$info['post_max_size'] = ini_get('post_max_size');
	$info['memory_limit'] = ini_get('memory_limit');
	$info['max_execution_time'] = ini_get('max_execution_time');
	$info['dbversion'] = $db->version();
	$info['SERVER_SOFTWARE'] = array_value($_SERVER, 'SERVER_SOFTWARE', '');
	$info['HTTP_X_FORWARDED_FOR'] = array_value($_SERVER, 'HTTP_X_FORWARDED_FOR', '');
	$info['REMOTE_ADDR'] = array_value($_SERVER, 'REMOTE_ADDR', '');
	
	
	$stat = array();
	$stat['threads'] = thread_count();
	$stat['posts'] = post_count();
	$stat['users'] = user_count();
	$stat['attachs'] = attach_count();
	$stat['disk_free_space'] = function_exists('disk_free_space') ? humansize(disk_free_space('./')) : '未知';
	
	$lastversion = get_last_version($stat);
	
	// 潜在错误检测，目录可写检测，避免搬家导致的问题。
	$check = array();
	$upload_tmp_dir = ini_get('upload_tmp_dir');
	if(!empty($upload_tmp_dir)) {
		$check['upload_path_check'] = is_writable($upload_tmp_dir) ? "<span class\"red\">$upload_tmp_dir 不可写</span>，上传功能会受到影响。" : "<span class=\"green\">$upload_tmp_dir 可写</span>";
	} else {
		$check['upload_path_check'] = "<span class=\"red\">php.ini 中未设置 upload_tmp_dir，可能会导致上传失败 </span>";
	}
	
	$check['php_ini'] = ini_get('upload_tmp_dir');
	
	include './admin/view/index.htm';

}

function get_last_version($stat) {
	global $conf, $time;
	$last_version = kv_get('last_version');
	if($time - $last_version > 86400) {
		kv_set('last_version', $time);
		$sitename = urlencode($conf['sitename']);
		$sitedomain = urlencode(http_url_path());
		$version = urlencode($conf['version']);
		return '<script src="http://custom.xiuno.com/version.htm?sitename='.$sitename.'&sitedomain='.$sitedomain.'&users='.$stat['users'].'&threads='.$stat['threads'].'&posts='.$stat['posts'].'&version='.$version.'"></script>';
	} else {
		return '';
	}
}

?>
