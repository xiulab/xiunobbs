<?php

define('DEBUG', 1);
define('APP_NAME', 'bbs_admin');
define('APP_PATH', '../');

chdir(APP_PATH);

$conf = (@include './conf/conf.php') OR exit(header('Location: ../install/'));

include './xiunophp/xiunophp.php';
include './model.inc.php';

// 测试数据库连接
db_connect($err) OR exit($err);

$grouplist = group_list_cache();
$forumlist = forum_list_cache();

$uid = $user['uid'];				// 全局的 uid
$gid = $user['gid'];				// 全局的 gid

$header = array();				// 头部需要的变量
$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = $conf['sitename']; 	// 关键词
$header['description'] = $conf['sitename']; 	// 描述

// 检测浏览器
$browser = get__browser();
check_browser($browser);

$runtime = runtime_init();

// 记录 POST 数据
DEBUG AND log_post_data();

if($gid != 1) {
	$_REQUEST[0] = 'index';
	$_REQUEST[1] = 'login';
}

//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

$route = param(0, 'index');

// todo: HHVM 不支持动态 include
switch ($route) {
	case 'article': 	include './admin/route/article.php'; 		break;
	case 'setting': 	include './admin/route/setting.php'; 		break;
	case 'forum': 		include './admin/route/forum.php'; 		break;
	case 'friendlink': 	include './admin/route/friendlink.php'; 	break;
	case 'group': 		include './admin/route/group.php'; 		break;
	case 'index':		include './admin/route/index.php'; 		break;
	case 'user':		include './admin/route/user.php'; 		break;
	default:
		$route = preg_match("/^\w+$/", $route) ? $route : 'index';
		$filename = "./admin/route/$route.php";
		is_file($filename) ? include($filename) : message(-1, '该功能未实现。');
}

?>