if($action == 'digest') {
	$page = param(2, 1);
	$pagesize = 20;
	
	$digests = $user['digests'];
	$pagination = pagination(url("user-$uid-{page}-1"), $digests, $page, $pagesize);
	$threadlist = thread_digest_find_by_uid($uid, $page, $pagesize);
	
	$active = 'thread';
	if($ajax) {
		foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
		message(0, $threadlist);
	} else {
		include _include(APP_PATH.'plugin/xn_digest/view/htm/my_digest.htm');
	}
}