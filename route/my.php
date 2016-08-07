<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

$user = user_read($uid);
user_login_check();

// hook my_action_before.php

if(empty($action)) {
	
	$header['title'] = lang('my_home');
	include './view/htm/my.htm';
	
} elseif($action == 'profile') {
	
	include './view/htm/my_profile.htm';

} elseif($action == 'password') {
	
	if($method == 'GET') {
		
		// hook my_password_get_start.php
		
		include './view/htm/my_password.htm';
		
	} elseif($method == 'POST') {
		
		// hook my_password_post_start.php
		
		$password_old = param('password_old');
		$password_new = param('password_new');
		md5($password_old.$user['salt']) != $user['password'] AND message('password_old', lang('old_password_incorrect'));
		$password_new = md5($password_new.$user['salt']);
		$r = user_update($uid, array('password'=>$password_new));
		$r !== FALSE AND message(-1, lang('password_modify_failed'));
		
		// hook my_password_post_end.php
		message(0, lang('password_modify_successfully'));
		
	}
	
} elseif($action == 'thread') {

	// hook my_thread_start.php
	
	$page = param(2, 1);
	$pagesize = 20;
	$totalnum = $user['threads'];
	$pagination = pagination(url('my-thread-{page}'), $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($uid, $page, $pagesize);
		
	// hook my_thread_end.php
	
	include './view/htm/my_thread.htm';

} elseif($action == 'avatar') {
	
	if($method == 'GET') {
		
		// hook my_avatar_get_start.php
		
		include './view/htm/my_avatar.htm';
	
	} else {
		
		// hook my_avatar_post_start.php
		
		$width = param('width');
		$height = param('height');
		$data = param('data', '', FALSE);
		
		empty($data) AND message(-1, lang('data_is_empty'));
		$data = base64_decode_file_data($data);
		$size = strlen($data);
		$size > 2048000 AND message(-1, lang('filesize_too_large', array('maxsize'=>'2M', 'size'=>$size)));
		
		$filename = "$uid.png";
		$dir = substr(sprintf("%09d", $uid), 0, 3).'/';
		$path = $conf['upload_path'].'avatar/'.$dir;
		$url = $conf['upload_url'].'avatar/'.$dir.$filename;
		!is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, lang('directory_create_failed')));
		
		file_put_contents($path.$filename, $data) OR message(-1, lang('write_to_file_failed'));
		
		user_update($uid, array('avatar'=>$time));
		
		// hook my_avatar_post_end.php
		
		message(0, array('url'=>$url));
		
	}
}

?>