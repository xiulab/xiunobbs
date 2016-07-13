<?php

/*
	TAB = 8 个空格
*/

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
!defined('APP_PATH') AND define('APP_PATH', '../'); // 判断是否被 include

chdir(APP_PATH);

$conf = (@include './conf/conf.php') OR exit(header('Location: install/'));
//define('CACHE_PRE', $conf['cache_pre']); 	// 缓存的头

include './xiunophp/xiunophp.php';
include './model.inc.php';

// 测试数据库连接
db_connect($err) OR exit($err);

$grouplist = group_list_cache();
$forumlist = forum_list_cache();
$user = user_token_get(); 			// 全局的 user
$uid = $user['uid'];				// 全局的 uid
$gid = $user['gid'];				// 全局的 gid
$group = $grouplist[$gid]; 			// 全局的 group

$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块

$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = $conf['sitename']; 	// 关键词
$header['description'] = $conf['sitename']; 	// 描述
$header['navs'] = array(); 			// 描述

$runtime = runtime_init();
$fid = 0;					// 当前所在的板块

// 检测浏览器
$browser = get__browser();
check_browser($browser);

$browser['device'] != 'pc' AND $method != 'POST' AND exit(header('Location: mobile/'.$_SERVER['REQUEST_URI_NO_PATH']));

// 检测站点运行级别
check_runlevel();
check_banip($ip);

// 记录 POST 数据
DEBUG AND log_post_data();

// 全站的设置数据，站点名称，描述，关键词，页脚代码等
$setting = cache_get('setting', TRUE);

//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

$route = param(0, 'index');

//thread_new_sitemap();

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'agree': 	include './pc/route/agree.php'; 	break;
	case 'browser': include './pc/route/browser.php'; 	break;
	case 'forum': 	include './pc/route/forum.php'; 	break;
	case 'index': 	include './pc/route/index.php'; 	break;
	case 'mod': 	include './pc/route/mod.php'; 		break;
	case 'my': 	include './pc/route/my.php'; 		break;
	case 'post': 	include './pc/route/post.php'; 		break;
	case 'search': 	include './pc/route/search.php'; 	break;
	case 'test': 	include './pc/route/test.php'; 		break;
	case 'thread':	include './pc/route/thread.php'; 	break;
	case 'user': 	include './pc/route/user.php'; 		break;
	default: message(-1, '该功能未实现。');
	/*
	default:
		$route = preg_match("/^\w+$/", $route) ? $route : 'index';
		$filename = "./pc/route/$route.php";
		is_file($filename) ? include($filename) : message(-1, '该功能未实现。');
	*/
}

/*
	message(0, '登录成功');
	message(1, '密码错误');
	message(-1, '数据库连接失败');
	
	code:
		< 0 全局错误，比如：系统错误：数据库丢失连接/文件不可读写
		= 0 正确
		> 0 一般业务逻辑错误，可以定位到具体控件，比如：用户名为空/密码为空
*/
function message($code, $message) {
	global $db, $cache, $starttime, $conf, $browser, $ajax, $uid, $gid, $user, $header, $forumlist_show, $fid;
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit("code: $code, message: $message") : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode(array('code'=>$code, 'message'=>$message));
		runtime_save();
	} else {
		$header['title'] = '提示信息';
		include "./pc/view/message.htm";
	}
	exit;
}

?>