<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$keyword = param('keyword');
!$keyword AND $keyword = xn_urldecode(param(1));

$threadlist = thread_find_by_keyword($keyword);

thread_list_access_filter($threadlist, $gid);

if(empty($threadlist) || empty($threadlist[0])) {
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

$header['title'] = $keyword.'-'.$conf['sitename'];
$header['keywords'] = $keyword;

$order = 'tid';

include './pc/view/search.htm';

?>