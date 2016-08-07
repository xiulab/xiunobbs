<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'login') {

	if($method == 'GET') {

		$header['title'] = lang('admin_login');
		
		include "./view/htm/index_login.htm";

	} else if($method == 'POST') {

		$password = param('password');

		if(md5($password.$user['salt']) != $user['password']) {
			xn_log('password error. uid:'.$user['uid'].' - ******'.substr($password, -6), 'admin_login_error');
			message('password', lang('password_incorrect'));
		}

		admin_token_set();

		// 记录日志
		xn_log('login successed. uid:'.$user['uid'], 'admin_login');

		message(0, jump(lang('login_successfully'), '.'));

	}

} elseif ($action == 'logout') {

	admin_token_clean();

	message(0, jump(lang('logout_successfully'), './'));

} elseif ($action == 'phpinfo') {
	
	phpinfo();
	exit;
	
} else {

	$header['title'] = lang('admin_page');
	
	$info = array();
	$info['disable_functions'] = ini_get('disable_functions');
	$info['allow_url_fopen'] = ini_get('allow_url_fopen') ? lang('yes') : lang('no');
	$info['safe_mode'] = ini_get('safe_mode') ? lang('yes') : lang('no');
	empty($info['disable_functions']) && $info['disable_functions'] = lang('none');
	$info['upload_max_filesize'] = ini_get('upload_max_filesize');
	$info['post_max_size'] = ini_get('post_max_size');
	$info['memory_limit'] = ini_get('memory_limit');
	$info['max_execution_time'] = ini_get('max_execution_time');
	$info['dbversion'] = $db->version();
	$info['SERVER_SOFTWARE'] = _SERVER('SERVER_SOFTWARE');
	$info['HTTP_X_FORWARDED_FOR'] = _SERVER('HTTP_X_FORWARDED_FOR');
	$info['REMOTE_ADDR'] = _SERVER('REMOTE_ADDR');
	
	
	$stat = array();
	$stat['threads'] = thread_count();
	$stat['posts'] = post_count();
	$stat['users'] = user_count();
	$stat['attachs'] = attach_count();
	$stat['disk_free_space'] = function_exists('disk_free_space') ? humansize(disk_free_space('./')) : lang('unknown');
	
	$lastversion = get_last_version($stat);
	
	include './view/htm/index.htm';

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
