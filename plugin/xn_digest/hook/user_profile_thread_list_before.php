
$digest = param(3, 0);
if($digest == 1) {
	$thread_list_from_default = 0;
	$active = 'digest';
	$digests = $_user['digests'];
	$pagination = pagination(url("user-$_uid-{page}-1"), $digests, $page, $pagesize);
	$threadlist = thread_digest_find_by_uid($_uid, $page, $pagesize);
}