<?php exit;

if($action == 'update_log') {
	
	$pid = param(2, 0);
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists:'));
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists:'));
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, lang('forum_not_exists:'));
	
	$header['title'] = lang('post_create');
	$header['mobile_title'] = lang('post_create');
	$header['mobile_link'] = url("thread-$tid");

	include _include(APP_PATH.'plugin/xn_mod_enhance/view/htm/post_update_log.htm');
}




?>