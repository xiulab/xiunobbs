<?php

// 创建新帖
!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

$user = user_read($uid);
empty($user) AND $user = user_guest();

// 帖子列表数据，返回 html 格式
if($action == 'list') {
	
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
	
	!forum_access_user($fid, $gid, 'allowread') AND message(10, '您（'.$user['groupname'].'）无权限查看此版块');
	
	// 只提取前 100 个用户，过多用户展示无意义。
	$agreelist = post_agree_find_by_pid($pid, 1, 100);
	
	$header['title'] = '喜欢过的用户：'.$post['agrees'].'人';
	
	include './pc/view/agree_list.htm';
	
} elseif($action == 'update') {
	
	$pid = param(2);
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(1, '板块不存在:'.$fid);
	
	$r = agree_update($post['uid'], $pid, $tid, $fid, $post['isfirst']);
	message($errno, $errstr);
	
}

?>