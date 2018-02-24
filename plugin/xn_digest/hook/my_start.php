if($action == 'digest') {
	$page = param(2, 1);
	$pagesize = 20;
	
	$digests = $user['digests'];
	$pagination = pagination(url("my-thread-{page}"), $digests, $page, $pagesize);
	$threadlist = thread_digest_find_by_uid($uid, $page, $pagesize);
	
	include _include(APP_PATH.'plugin/xn_digest/view/htm/my_digest.htm');
	exit;
}