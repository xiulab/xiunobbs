<?php exit;

if($action == 'update_log_read') {
	
	$logid = param(2, 0);
	$log = post_update_log_read($logid);
	empty($log) AND message(-1, lang('update_logid_not_exists'));
	
	$pid = $log['pid'];
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists:'));
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists:'));
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, lang('forum_not_exists:'));
	
	$fid = $thread['fid'];
	$allowtop = forum_access_mod($fid, $gid, 'allowtop');
	($gid > 5 || !$allowtop) AND message(-1, lang('user_group_insufficient_privilege'));
	
	$header['title'] = lang('post_create');
	$header['mobile_title'] = lang('post_create');
	$header['mobile_link'] = url("thread-$tid");
	
	include _include(APP_PATH.'plugin/xn_mod_enhance/view/htm/post_update_log_read.htm');

// 删除更新日志
} elseif($action == 'update_log_delete') {

	$logid = param(2, 0);
	$log = post_update_log_read($logid);
	empty($log) AND message(-1, lang('update_logid_not_exists'));
	
	$pid = $log['pid'];
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists:'));
	
	$thread = thread_read($post['tid']);
	empty($thread) AND message(-1, lang('thread_not_exists:'));
	
	$fid = $thread['fid'];
	
	// 判断版主权限
	$allowtop = forum_access_mod($fid, $gid, 'allowtop');
	($gid > 5 || !$allowtop) AND message(-1, lang('user_group_insufficient_privilege'));
	
	$r = post_update_log_delete($logid);
	
	message(0, '');

// 更新日志
} elseif($action == 'update_log_list') {
	
	$pid = param(2, 0);
	
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists:'));

	$thread = thread_read($post['tid']);
	empty($thread) AND message(-1, lang('thread_not_exists:'));
	
	$fid = $thread['fid'];
	$allowtop = forum_access_mod($fid, $gid, 'allowtop');
	($gid > 5 || !$allowtop) AND message(-1, lang('user_group_insufficient_privilege'));
	
	$post_update_log_list = array();
	if($post['allowdelete'] && $post['updates'] > 0) {
		$post_update_log_list = post_update_log_find_by_pid($post['pid']);
	}
	
	include _include(APP_PATH.'plugin/xn_mod_enhance/view/htm/post_update_log_list.htm');
	
}




?>