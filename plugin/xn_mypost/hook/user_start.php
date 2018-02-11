
if($action == 'post') {
	
	// hook user_post_start.php
	
	$_uid = param(2, 0);
	$_user = user_read($_uid);
	
	$page = param(3, 1);
	$pagesize = 20;
	$totalnum = $_user['posts'];
	$pagination = pagination(url("user-post-$_uid-{page}"), $totalnum, $page, $pagesize);
	$postlist = post_find_by_uid($_uid, $page, $pagesize);
	
	post_list_access_filter($postlist, $gid);
	
	// hook user_post_end.php
	
	include _include(APP_PATH.'plugin/xn_mypost/view/htm/user_post.htm');
}