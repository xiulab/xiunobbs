if($action == 'digest') {

	$_uid = param(2, 1);
	$page = param(3, 1);
	$pagesize = 20;
	
	$_user = user_read($_uid);
	$digests = $_user['digests'];
	$pagination = pagination(url("user-digest-$_uid-{page}"), $digests, $page, $pagesize);
	$threadlist = thread_digest_find_by_uid($_uid, $page, $pagesize);
	
	thread_list_access_filter($threadlist, $gid);
	
	include _include(APP_PATH.'plugin/xn_digest/view/htm/user_digest.htm');
	exit;
}