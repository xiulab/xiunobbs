
if($action == 'post') {
	
	// hook my_post_start.php
	
	$page = param(2, 1);
	$pagesize = 20;
	
	$totalnum = $user['posts'];
	$pagination = pagination(url("my-post-{page}"), $totalnum, $page, $pagesize);
	$postlist = post_find_by_uid($uid, $page, $pagesize);
	
	post_list_access_filter($postlist, $gid);

	// hook my_post_end.php
	
	$active = 'thread';
	if($ajax) {
		foreach($postlist as &$postlist) $post = post_safe_info($post);
		message(0, $postlist);
	} else {
		include _include(APP_PATH.'view/htm/my_post.htm');
	}
	
}