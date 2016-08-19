<?php

//$_SERVER['REQUEST_URI'] = '/?plugin-unstall-xn_nav_more.htm';
//$_SERVER['REQUEST_URI'] = '/?plugin-install-xn_user_recent_thread.htm';



//$_SERVER['REQUEST_URI'] = '/?forum-update-1.htm';
//$_SERVER['REQUEST_METHOD'] = 'POST';
//parse_str(urldecode('name=Default+Forum&rank=0&brief=Default+Forum+Brief&announcement=&moduids=&allowread%5B0%5D=1&allowpost%5B0%5D=1&allowdown%5B0%5D=1&allowread%5B1%5D=1&allowthread%5B1%5D=1&allowpost%5B1%5D=1&allowattach%5B1%5D=1&allowdown%5B1%5D=1&allowread%5B2%5D=1&allowthread%5B2%5D=1&allowpost%5B2%5D=1&allowattach%5B2%5D=1&allowdown%5B2%5D=1&allowread%5B4%5D=1&allowthread%5B4%5D=1&allowpost%5B4%5D=1&allowattach%5B4%5D=1&allowdown%5B4%5D=1&allowread%5B5%5D=1&allowthread%5B5%5D=1&allowpost%5B5%5D=1&allowattach%5B5%5D=1&allowdown%5B5%5D=1&allowread%5B6%5D=1&allowpost%5B6%5D=1&allowdown%5B6%5D=1&allowread%5B101%5D=1&allowthread%5B101%5D=1&allowpost%5B101%5D=1&allowattach%5B101%5D=1&allowdown%5B101%5D=1&allowread%5B102%5D=1&allowthread%5B102%5D=1&allowpost%5B102%5D=1&allowattach%5B102%5D=1&allowdown%5B102%5D=1&allowread%5B103%5D=1&allowthread%5B103%5D=1&allowpost%5B103%5D=1&allowattach%5B103%5D=1&allowdown%5B103%5D=1&allowread%5B104%5D=1&allowthread%5B104%5D=1&allowpost%5B104%5D=1&allowattach%5B104%5D=1&allowdown%5B104%5D=1&allowread%5B105%5D=1&allowthread%5B105%5D=1&allowpost%5B105%5D=1&allowattach%5B105%5D=1&allowdown%5B105%5D=1&cate_name%5B12%5D=AAA&cate_rank%5B12%5D=3&cate_enable%5B12%5D=1&tag_cate_id%5B23%5D=12&tag_name%5B23%5D=A1&tag_rank%5B23%5D=2&tag_enable%5B23%5D=1&tag_cate_id%5B24%5D=12&tag_name%5B24%5D=A2&tag_rank%5B24%5D=1&tag_enable%5B24%5D=1&cate_name%5B13%5D=BBB&cate_rank%5B13%5D=2&cate_enable%5B13%5D=1&tag_cate_id%5B25%5D=13&tag_name%5B25%5D=B1&tag_rank%5B25%5D=2&tag_enable%5B25%5D=1&tag_cate_id%5B26%5D=13&tag_name%5B26%5D=B2&tag_rank%5B26%5D=1&tag_enable%5B26%5D=1&cate_name%5B14%5D=CCC&cate_rank%5B14%5D=1&cate_enable%5B14%5D=1&tag_cate_id%5B27%5D=14&tag_name%5B27%5D=C1&tag_rank%5B27%5D=0&tag_enable%5B27%5D=1&tag_cate_id%5B28%5D=14&tag_name%5B28%5D=C2&tag_rank%5B28%5D=0&tag_enable%5B28%5D=1'), $_POST);
//



// 切换到上级目录 / chdir to up one directory
//define('BASE_HREF', '../');
define('SKIP_ROUTE', TRUE);
chdir('../');
include './index.php';

/*
	切换回来，主要为了方便 admin 目录改名，有利于安全
	Switch back, mainly in order to facilitate the admin directory renamed, is conducive to security
	
	处理相对路径比较麻烦一点，一般直接工作在根目录
	Handling the relative path is a bit more trouble, generally directly working in the root directory,
	
	像 install/index.php 属于一般写法，不用处理 $conf['tmp_path'] 等路径相对问题。
	Like install/index.php are generally written, do not handle $conf['tmp_path'] path relative problems.
*/
$currdir = dirname(__FILE__);
chdir($currdir);
$_SERVER['APP_PATH'] = $currdir;

$conf['log_path'][0] == '.' AND $conf['log_path'] = '../'.$conf['log_path'];
$conf['tmp_path'][0] == '.' AND $conf['tmp_path'] = '../'.$conf['tmp_path'];
$conf['upload_path'][0] == '.' AND $conf['upload_path'] = '../'.$conf['upload_path'];

$lang += include "../lang/$conf[lang]/bbs_admin.php";
include "./admin.func.php";
$menu = include './menu.conf.php';

// hook admin_index_menu_after.php

// 只允许管理员登陆后台
// Only allow administrators to log in the background

// 对于越权访问，可以默认为黑客企图，不用友好提示。
// For unauthorized access, can default to the hacking attempt, without a friendly reminder.
if(DEBUG < 2) {
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
	case 'index':		include './route/index.php'; 		break;
	case 'setting': 	include './route/setting.php'; 		break;
	case 'forum': 		include './route/forum.php'; 		break;
	case 'friendlink': 	include './route/friendlink.php'; 	break;
	case 'group': 		include './route/group.php'; 		break;
	case 'user':		include './route/user.php'; 		break;
	case 'plugin':		include './route/plugin.php'; 		break;
	// hook admin_index_route_case.php
	default: 
		// 为了支持插件，此处不利于编译优化
		// In order to support / plug-in, here is not conducive to compiler optimization
		(!is_word($route) || !is_file("./route/$route.php")) && http_404();
		include "./route/$route.php";
}

?>