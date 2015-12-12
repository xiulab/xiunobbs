<?php

// 创建新帖
!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';
include './xiunophp/image.func.php';

$action = param(1);

$user = user_read($uid);
if($action == 'update') {

	$pid = param(2);
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(1, '板块不存在:'.$fid);
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
	!$allowupdate AND !$post['allowupdate'] AND message(-1, '无权编辑该贴');
	
	if($method == 'GET') {
		
		$forumarr = xn_json_encode(arrlist_key_values($forumlist, 'fid', 'name'));
		$post['message'] = htmlspecialchars($post['message']);
		include './mobile/view/post_update.htm';
	}
} else {
	
	message(-1, '没有此功能');
	
}

?>