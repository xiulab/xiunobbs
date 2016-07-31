<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

$page = param(1, 1);
$order = param(2, $conf['order_default']);
$order != 'tid' AND $order = 'lastpid';
$pagesize = 20;
$fid = 0;

// 主题列表
$toplist = thread_top_find_cache();
$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);
$conf['order_default'] == $order AND $threadlist = $toplist + $threadlist;

// 去除无权限的主题
thread_list_access_filter($threadlist, $gid);

// SEO 相关
empty($setting) AND $setting = array('sitebrief'=>'');
$sitebrief = $setting['sitebrief'];
$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = ''; 			// 关键词
$header['description'] = ''; 			// 描述

include './view/htm/index.htm';

?>