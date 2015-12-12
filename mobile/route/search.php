<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$action = param(1);

// 模板初始化依赖
$keyword = param('keyword');

$threadlist = thread_find_by_keyword($keyword);

// 去除无权限的主题
thread_list_access_filter($threadlist, $gid);

if(empty($threadlist)) {
	$fid = 0;
	$tid = 0;
	$thread = array();
	$postlist = array();
	$first = array();
} else {
	$thread = $threadlist[0];
	$tid = $thread['tid'];
	$fid = $thread['fid'];
	
	$postlist = post_find_by_tid($tid);
	$first = $postlist[$thread['firstpid']];
	unset($postlist[$thread['firstpid']]);
	
	$allowpost = forum_access_user($fid, $gid, 'allowpost');
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
}

$header['title'] = $keyword.'-'.$conf['sitename']; 		// 网站标题
$header['keywords'] = $keyword; 		// 关键词

$order = 'tid';

include './mobile/view/search.htm';

?>