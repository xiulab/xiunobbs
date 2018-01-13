if($action == 'digest') {

	$_uid = param(2, 1);
	$page = param(3, 1);
	$pagesize = 20;
	
	$_user = user_read($_uid);
	$digests = $_user['digests'];
	$pagination = pagination(url("user-$_uid-{page}-1"), $digests, $page, $pagesize);
	$threadlist = thread_digest_find_by_uid($_uid, $page, $pagesize);
	
	thread_list_access_filter($threadlist, $gid);
	
	$active = 'thread';
	if($ajax) {
		foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
		message(0, $threadlist);
	} else {
		include _include(APP_PATH.'view/htm/user.htm');
		exit;
	}
}