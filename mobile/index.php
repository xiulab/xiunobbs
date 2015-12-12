<?php

/*
	TAB = 8 个空格
	手机版无刷新，支持手滑脚滑
*/

chdir('../');

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'mobile');			// 应用的名称
define('IN_SAE', class_exists('SaeKV'));	// 一般应用不需要支持 SAE，可以删掉

$conf = (@include './conf/conf.php') OR exit(header('Location: ../install/'));
IN_SAE AND include './conf/sae.conf.php'; 	// 支持 SAE

include './xiunophp/xiunophp.php';
include './model.inc.php';

// 测试数据库连接
db_connect($err) OR message(-1, $err);

$grouplist = group_list_cache();
$forumlist = forum_list_cache();
$user = user_token_get('', 'bbs'); 			// 全局的 user，全局变量除了 $conf, 其他全部加下划线
$uid = $user['uid'];					// 全局的 uid
$gid = $user['gid'];					// 全局的 gid
$group = $grouplist[$gid]; 				// 全局的 user，全局变量除了 $conf, 其他全部加下划线

$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块

$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = $conf['sitename']; 	// 关键词
$header['description'] = $conf['sitename']; 	// 描述

// 启动在线，将清理函数注册，不能写日志。
$runtime = runtime_init();
$sid = online_init();
$fid = 0;

// 检测浏览器
$browser = get__browser();
check_browser($browser);

// 检测站点运行级别
check_runlevel();
check_banip($ip);

// 记录 POST 数据
DEBUG AND log_post_data();

// 全站的设置数据
$setting = cache_get('setting');

//DEBUG AND online_end();
//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

list($tid, $thread) = parse_seo_url();

$route = param(0, 'index');

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'agree': 	include './mobile/route/agree.php'; 	break;
	case 'browser': include './mobile/route/browser.php'; 	break;
	case 'forum': 	include './mobile/route/forum.php'; 	break;
	case 'index': 	include './mobile/route/index.php'; 	break;
	case 'mod': 	include './mobile/route/mod.php'; 	break;
	case 'my': 	include './mobile/route/my.php'; 	break;
	case 'post': 	include './mobile/route/post.php'; 	break;
	case 'search': 	include './mobile/route/search.php'; 	break;
	case 'test': 	include './mobile/route/test.php'; 	break;
	case 'thread':	include './mobile/route/thread.php'; 	break;
	case 'user': 	include './mobile/route/user.php'; 	break;
	default: message(-1, '该功能未实现。');
	/*
	default:
		$route = preg_match("/^\w+$/", $route) ? $route : 'index';
		$filename = "./mobile/route/$route.php";
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
	global $db, $cache, $starttime, $conf, $browser, $ajax, $uid, $gid, $user, $header, $forumlist, $forumlist_show, $fid;
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit("code: $code, message: $message") : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode(array('code'=>$code, 'message'=>$message));
		runtime_save();
		online_save();
	} else {
		$header['title'] = '提示信息';
		include "./mobile/view/message.htm";
	}
	exit;
}

?>