<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/image.func.php';

$action = param(1);

$user = user_read($uid);
user_login_check($user);

empty($action) AND $action = 'profile';

if($action == 'profile') {
	
	if($method == 'GET') {

		$header['title'] = '个人中心';
		
		include './mobile/view/my_profile.htm';
	
	}
	
} elseif($action == 'password') {
	
	if($method == 'GET') {
		
		$header['title'] = '修改密码';
		include './mobile/view/my_password.htm';
		
	}
	
} elseif($action == 'thread') {

	$page = param(2, 1);
	$pagesize = 20;
	$totalnum = $user['threads'];
	$pages = simple_pages('mobile/my-thread-{page}.htm', $totalnum, $page, $pagesize);
	$threadlist = mythread_find_by_uid($uid, $page, $pagesize);
		
	include './mobile/view/my_thread.htm';
	
} elseif($action == 'agree') {

	$page = param(2, 1);
	$pagesize = 20; // $conf['pagesize']
	$totalnum = $user['myagrees'];
	$pages = simple_pages('mobile/my-agree-{page}.htm', $totalnum, $page, $pagesize);
	$threadlist = myagree_find_by_uid($uid, $page, $pagesize);
		
	include './mobile/view/my_agree.htm';
	
}

?>