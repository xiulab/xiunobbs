<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

$user = user_read($uid);
user_login_check($user);

// 账户（密码、头像），主题
if(empty($action)) {
	
	$header['title'] = '个人中心';
	include './view/htm/my.htm';
	
} elseif($action == 'profile') {
	
	include './view/htm/my_profile.htm';

} elseif($action == 'password') {
	
	if($method == 'GET') {
		
		include './view/htm/my_password.htm';
		
	} elseif($method == 'POST') {
		
		$password_old = param('password_old');
		$password_new = param('password_new');
		md5($password_old.$user['salt']) != $user['password'] AND message('password_old', '旧密码不正确');
		$password_new = md5($password_new.$user['salt']);
		$r = user_update($uid, array('password'=>$password_new));
		$r !== FALSE ? message(0, '密码修改成功') : message(-1, '密码修改失败');
		
	}
	
} elseif($action == 'thread') {

	$page = param(2, 1);
	$pagesize = 20;
	$totalnum = $user['threads'];
	$pagination = pagination(url('my-thread-{page}'), $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($uid, $page, $pagesize);
		
	include './view/htm/my_thread.htm';

} elseif($action == 'avatar') {
	
	if($method == 'GET') {
		
		include './view/htm/my_avatar.htm';
	
	} else {
		
		$width = param('width');
		$height = param('height');
		$data = param('data', '', FALSE);
		
		empty($data) AND message(-1, '数据为空');
		$data = base64_decode_file_data($data);
		$size = strlen($data);
		$size > 2048000 AND message(-1, '文件尺寸太大，不能超过 2M，当前大小：'.$size);
		
		$filename = "$uid.png";
		$dir = substr(sprintf("%09d", $uid), 0, 3).'/';
		$path = $conf['upload_path'].'avatar/'.$dir;
		$url = $conf['upload_url'].'avatar/'.$dir.$filename;
		!is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, '目录创建失败'));
		
		file_put_contents($path.$filename, $data) OR message(-1, '写入文件失败');
		
		user_update($uid, array('avatar'=>$time));
		
		message(0, array('url'=>$url));
		
	}
}

?>
