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
$linklist = friendlink_find_cache();
$onlinelist = online_find_cache();

// SEO 相关
empty($setting) AND $setting = array('sitebrief'=>'', 'seo_title'=>'', 'seo_keywords'=>'', 'seo_description'=>'', 'footer_code'=>'');
$sitebrief = $setting['sitebrief'];
$header['title'] = $setting['seo_title'] ? $setting['seo_title'] : $conf['sitename']; 		// 网站标题
$header['keywords'] = $setting['seo_keywords']; 		// 关键词
$header['description'] = $setting['seo_description']; 	// 描述

// 最新主题
//$new_tids = forum_new_tids(($order == 'lastpid' ? $threadlist : array()));

include './mobile/view/index.htm';

?>