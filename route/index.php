<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

$page = param(1, 1);
$order = param(2, $conf['order_default']);	// 默认不允许用户修改排序顺序，插件可以做，该参数给插件预留
$order != 'tid' AND $order = 'lastpid';		// 默认按照顶贴时间排序
$pagesize = $conf['pagesize'];

$pagination = pagination(url("index-{page}"), $runtime['threads'], $page, $pagesize);

// 主题列表
$toplist = thread_top_find_cache();

$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);
//$conf['order_default'] == $order AND $threadlist = $toplist + $threadlist;

// 去除无权限的主题
thread_list_access_filter($threadlist, $gid);

// SEO 相关
$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = ''; 			// 关键词
$header['description'] = ''; 			// 描述

include './view/htm/index.htm';

?>