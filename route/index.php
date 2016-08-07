<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

// hook index_start.php

$page = param(1, 1);
$order = param(2, $conf['order_default']);
$order != 'tid' AND $order = 'lastpid';
$pagesize = $conf['pagesize'];

$pagination = pagination(url("index-{page}"), $runtime['threads'], $page, $pagesize);

$toplist = thread_top_find_cache();

$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);
//$conf['order_default'] == $order AND $threadlist = $toplist + $threadlist;

// filter no privilege thread
thread_list_access_filter($threadlist, $gid);

// SEO
$header['title'] = $conf['sitename']; 		// site title
$header['keywords'] = ''; 			// site keyword
$header['description'] = ''; 			// site description

// hook index_end.php

include './view/htm/index.htm';

?>