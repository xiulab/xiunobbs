$digest = param(2, 0);
if($digest == 1) {
	$thread_list_from_default = 0;
	$active = 'digest';
	$digests = thread_digest_count($fid);
	
	$pagination = pagination(url("$route-{page}-1"), $digests, $page, $pagesize);
	
	$threadlist = thread_digest_find_by_fid($fid, $page, $pagesize);
}