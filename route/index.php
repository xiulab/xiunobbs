<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

include './model/friendlink.func.php';

$order = param(1, $conf['order_default']);
$order != 'tid' AND $order = 'lastpid';

$fid = 0;

// 主题列表
$toplist = thread_top_find_cache();
$threadlist = $order == 'tid' ? thread_new_find_cache() : thread_lastpid_find_cache();
$conf['order_default'] == $order AND $threadlist = $toplist + $threadlist;

// 去除无权限的主题
thread_list_access_filter($threadlist, $gid);

// 友情链接，在线列表
//$linklist = friendlink_find_cache();
$onlinelist = online_find_cache();

$runtime['onlines'] = count($onlinelist);

// SEO 相关
empty($setting) AND $setting = array('sitebrief'=>'');
$sitebrief = $setting['sitebrief'];
$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = ''; 			// 关键词
$header['description'] = ''; 			// 描述

include './view/htm/index.htm';

?>