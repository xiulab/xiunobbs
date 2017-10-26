<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

// hook index_start.php

// fids 白名单，如果设置后，将从指定的版块 fids 中读取
$fids = array();

if(empty($fids)) {
	$page = param(1, 1);
	$order = $conf['order_default'];
	$order != 'tid' AND $order = 'lastpid';
	$pagesize = $conf['pagesize'];
	$active = 'default';

	$pagination = pagination(url("index-{page}"), $runtime['threads'], $page, $pagesize);

	/*$toplist = thread_top_find_cache();

	print_r($toplist);exit;*/

	// 从默认的地方读取主题列表
	$thread_list_from_default = 1;

	// hook index_thread_list_before.php

	if($thread_list_from_default) {
		$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);
	}
	//$conf['order_default'] == $order AND $threadlist = $toplist + $threadlist;

} else {
	$page = param(1, 1);
	$order = $conf['order_default'];
	$order != 'tid' AND $order = 'lastpid';
	foreach($fids as $_fid) {
		$threads += $forumlist[$fid]['threads'];

	}
	$pagesize = $conf['pagesize'];
	$active = 'default';

	$pagination = pagination(url("index-{page}"), $runtime['threads'], $page, $pagesize);

	// 从默认的地方读取主题列表
	$thread_list_from_default = 1;

	if($thread_list_from_default) {
		$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);
	}

}

// 过滤没有权限访问的主题 / filter no permission thread
thread_list_access_filter($threadlist, $gid);

// SEO
$header['title'] = $conf['sitename']; 				// site title
$header['keywords'] = ''; 					// site keyword
$header['description'] = $conf['sitebrief']; 			// site description
$_SESSION['fid'] = 0;

// hook index_end.php

if($ajax) {
	foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
	message(0, $threadlist);
} else {
	include _include(APP_PATH.'view/htm/index.htm');
}
?>