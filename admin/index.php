<?php

//$_SERVER['REQUEST_URI'] = '/?plugin-unstall-xn_nav_more.htm';
//$_SERVER['REQUEST_URI'] = '/?plugin-install-xn_user_recent_thread.htm';



//$_SERVER['REQUEST_URI'] = '/?forum-update-1.htm';
//$_SERVER['REQUEST_METHOD'] = 'POST';
//parse_str(urldecode('name=Default+Forum&rank=0&brief=Default+Forum+Brief&announcement=&moduids=&allowread%5B0%5D=1&allowpost%5B0%5D=1&allowdown%5B0%5D=1&allowread%5B1%5D=1&allowthread%5B1%5D=1&allowpost%5B1%5D=1&allowattach%5B1%5D=1&allowdown%5B1%5D=1&allowread%5B2%5D=1&allowthread%5B2%5D=1&allowpost%5B2%5D=1&allowattach%5B2%5D=1&allowdown%5B2%5D=1&allowread%5B4%5D=1&allowthread%5B4%5D=1&allowpost%5B4%5D=1&allowattach%5B4%5D=1&allowdown%5B4%5D=1&allowread%5B5%5D=1&allowthread%5B5%5D=1&allowpost%5B5%5D=1&allowattach%5B5%5D=1&allowdown%5B5%5D=1&allowread%5B6%5D=1&allowpost%5B6%5D=1&allowdown%5B6%5D=1&allowread%5B101%5D=1&allowthread%5B101%5D=1&allowpost%5B101%5D=1&allowattach%5B101%5D=1&allowdown%5B101%5D=1&allowread%5B102%5D=1&allowthread%5B102%5D=1&allowpost%5B102%5D=1&allowattach%5B102%5D=1&allowdown%5B102%5D=1&allowread%5B103%5D=1&allowthread%5B103%5D=1&allowpost%5B103%5D=1&allowattach%5B103%5D=1&allowdown%5B103%5D=1&allowread%5B104%5D=1&allowthread%5B104%5D=1&allowpost%5B104%5D=1&allowattach%5B104%5D=1&allowdown%5B104%5D=1&allowread%5B105%5D=1&allowthread%5B105%5D=1&allowpost%5B105%5D=1&allowattach%5B105%5D=1&allowdown%5B105%5D=1&cate_name%5B12%5D=AAA&cate_rank%5B12%5D=3&cate_enable%5B12%5D=1&tag_cate_id%5B23%5D=12&tag_name%5B23%5D=A1&tag_rank%5B23%5D=2&tag_enable%5B23%5D=1&tag_cate_id%5B24%5D=12&tag_name%5B24%5D=A2&tag_rank%5B24%5D=1&tag_enable%5B24%5D=1&cate_name%5B13%5D=BBB&cate_rank%5B13%5D=2&cate_enable%5B13%5D=1&tag_cate_id%5B25%5D=13&tag_name%5B25%5D=B1&tag_rank%5B25%5D=2&tag_enable%5B25%5D=1&tag_cate_id%5B26%5D=13&tag_name%5B26%5D=B2&tag_rank%5B26%5D=1&tag_enable%5B26%5D=1&cate_name%5B14%5D=CCC&cate_rank%5B14%5D=1&cate_enable%5B14%5D=1&tag_cate_id%5B27%5D=14&tag_name%5B27%5D=C1&tag_rank%5B27%5D=0&tag_enable%5B27%5D=1&tag_cate_id%5B28%5D=14&tag_name%5B28%5D=C2&tag_rank%5B28%5D=0&tag_enable%5B28%5D=1'), $_POST);
//



// 切换到上级目录 / chdir to up one directory
//define('BASE_HREF', '../');

define('ADMIN_PATH', dirname(__FILE__).'/'); // __DIR__
define('MESSAGE_HTM_PATH', ADMIN_PATH.'view/htm/message.htm');

define('SKIP_ROUTE', TRUE);
include '../index.php';

$lang += include _include(APP_PATH."lang/$conf[lang]/bbs_admin.php");
include _include(ADMIN_PATH."admin.func.php");
$menu = include _include(ADMIN_PATH.'menu.conf.php');

// hook admin_index_menu_after.php

// 只允许管理员登陆后台
// Only allow administrators to log in the background

// 对于越权访问，可以默认为黑客企图，不用友好提示。
// For unauthorized access, can default to the hacking attempt, without a friendly reminder.
if(DEBUG < 3) {
	// 管理组检查 / check admin group
	if($gid != 1) {
		setcookie('bbs_sid', '', $time - 86400);
		//http_403();
		http_location(url('../user-login'));
	}
	
	// 管理员令牌检查 / check admin token
	admin_token_check();
}

$route = param(0, 'index');

switch ($route) {
	case 'index':		include _include(ADMIN_PATH.'route/index.php'); 		break;
	case 'setting': 	include _include(ADMIN_PATH.'route/setting.php'); 	break;
	case 'forum': 		include _include(ADMIN_PATH.'route/forum.php'); 		break;
	case 'friendlink': 	include _include(ADMIN_PATH.'route/friendlink.php'); 	break;
	case 'group': 		include _include(ADMIN_PATH.'route/group.php'); 		break;
	case 'user':		include _include(ADMIN_PATH.'route/user.php'); 		break;
	case 'plugin':		include _include(ADMIN_PATH.'route/plugin.php'); 	break;
	// hook admin_index_route_case.php
	default: 
		// 为了支持插件，此处不利于编译优化
		// In order to support / plug-in, here is not conducive to compiler optimization
		(!is_word($route) || !is_file(ADMIN_PATH."route/$route.php")) && http_404();
		include _include(ADMIN_PATH."route/$route.php");
}

?>