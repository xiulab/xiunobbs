<?php exit;

if($action == 'login') {
	
	header('Location: http://passport.kanxue.com/user-login.htm');
	exit;
	
} elseif($action == 'create') {
	
	header('Location: http://passport.kanxue.com/user-create.htm');
	exit;
	
} elseif($action == 'logout') {
	
	header('Location: http://passport.kanxue.com/user-logout.htm');
	exit;
	
} else {
	
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
	
	// hook user_profile_end.php
	
	include _include(APP_PATH.'view/htm/user_profile.htm');
	
}
exit;

?>

