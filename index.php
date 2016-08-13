<?php

/*
 * Copyright (C) xiuno.com
 */

// 0: Production mode; 1: Developer mode; 2: Detail mode;
!defined('DEBUG') AND define('DEBUG', 0);		 

ob_start('ob_gzhandler');

$conf = (@include './conf/conf.php') OR exit(header('Location: install/'));

if(DEBUG) {
	include './xiunophp/xiunophp.php';
} else {
	include './xiunophp/xiunophp.min.php';
}

// 测试数据库连接 / try to connect database
db_connect() OR exit($errstr);

include './model.inc.php';

$sid = sess_start();

// 语言 / Language
$lang = include("./lang/$conf[lang]/bbs.php");

// 支持 Token 接口（token 与 session 双重登陆机制，方便 REST 接口设计，也方便 $_SESSION 使用）
// Support Token interface (token and session dual landing mechanism, to facilitate the design of the REST interface, but also to facilitate the use of $_SESSION)
$uid = user_token_get();
empty($uid) AND $uid = intval(_SESSION('uid'));
$user = user_read($uid);

// 用户组 / Group
$gid = empty($user) ? 0 : intval($user['gid']);
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : $grouplist[0];

// 版块 / Forum
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块 / filter no permission forum
$forumarr = arrlist_key_values($forumlist_show, 'fid', 'name');

// 头部 header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'mobile_title'=>'',
	'mobile_link'=>'./',
	'keywords'=>'', // 搜索引擎自行分析 keywords, 自己指定没用 / Search engine automatic analysis of key words, their own designation is not used
	'description'=>$conf['sitebrief'],
	'navs'=>array(),
);

// 运行时数据，存放于 cache_set() / runteime data
$runtime = runtime_init();

// 默认为 NULL，全局设置，存放于 kv_cache_set()
$setting = FALSE;

// 检测站点运行级别 / restricted access
check_runlevel();

// 全站的设置数据，站点名称，描述，关键词
// $setting = kv_get('setting');

$route = param(0, 'index');

// hook index_route_before.php

if(!defined('SKIP_ROUTE')) {
	
	// 按照使用的频次排序，增加命中率，提高效率
	// According to the frequency of the use of sorting, increase the hit rate, improve efficiency
	switch ($route) {
		case 'index': 	include './route/index.php'; 	break;
		case 'thread':	include './route/thread.php'; 	break;
		case 'forum': 	include './route/forum.php'; 	break;
		case 'user': 	include './route/user.php'; 	break;
		case 'my': 	include './route/my.php'; 	break;
		case 'attach': 	include './route/attach.php'; 	break;
		case 'search': 	include './route/search.php'; 	break;
		case 'post': 	include './route/post.php'; 	break;
		case 'mod': 	include './route/mod.php'; 	break;
		case 'browser': include './route/browser.php'; 	break;
		default: 
			// 为了支持插件，此处不利于编译优化
			// In order to support / plug-in, here is not conducive to compiler optimization
			(!is_word($route) || !is_file("./route/$route.php")) && http_404();
			include "./route/$route.php";
	}
}

?>