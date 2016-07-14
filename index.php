<?php

// DEBUG
// $_SERVER['REQUEST_URI'] = '/forum-2.htm';
// $_SERVER['HTTP_USER_AGENT'] = 'Baiduspider+(+http://www.baidu.com/search/spider.htm”) ';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.40607)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; AhrefsBot/5.0; +http://ahrefs.com/robot/)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 5.1; rv:15.0) Gecko/20100101 Firefox/15.0.1';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; Maxthon; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
// $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.<OS build number>';
// $_COOKIE['bbs_sid'] = '5588c68700b8d';
// $_COOKIE['bbs_token'] = 'Ndh_2FySTsF4oWsGsh2y7ywcJRhI_2FqXEHw3G1aH8_2FnSzvOyXW79A5Xat_2F0qclUSnKi6CSW_2FHWh2mPEKpS00wZxfwaBS0tTNMxAjj_2b3FTzwR3I_3d';

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称

$conf = (@include './conf/conf.php') OR exit(header('Location: install/'));
//define('CACHE_PRE', $conf['cache_pre']); 	// 缓存的头

include './xiunophp/xiunophp.php';

// 测试数据库连接
db_connect($err) OR exit($err);

include './model.inc.php';

// 用户
$uid = $session['uid'];
$user = user_read($uid); 

// 用户组
$gid = empty($user) ? 0 : $user['gid'];
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : array();

// 版块
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块

// 头部
$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = $conf['sitename']; 	// 关键词
$header['description'] = $conf['sitename']; 	// 描述
$header['navs'] = array(); 			// 描述

// 运行时数据
$runtime = runtime_init();
				// 当前所在的板块
// 检测浏览器， 不支持 IE8
$browser = get__browser();
check_browser($browser);

// 检测站点运行级别
check_runlevel();

// 检测 IP 封锁
check_banip($ip);

// 记录 POST 数据
DEBUG AND log_post_data();

// 全站的设置数据，站点名称，描述，关键词，页脚代码等
$setting = cache_get('setting', TRUE);

//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

$route = param(0, 'index');

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'browser': include './route/browser.php'; 	break;
	case 'forum': 	include './route/forum.php'; 	break;
	case 'index': 	include './route/index.php'; 	break;
	case 'mod': 	include './route/mod.php'; 	break;
	case 'my': 	include './route/my.php'; 	break;
	case 'post': 	include './route/post.php'; 	break;
	case 'search': 	include './route/search.php'; 	break;
	case 'test': 	include './route/test.php'; 	break;
	case 'thread':	include './route/thread.php'; 	break;
	case 'user': 	include './route/user.php'; 	break;
	default: exit(header('HTTP/1.0 404 Not Found'));
}

?>