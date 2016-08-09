<?php

//$_SERVER['REQUEST_URI'] = '/?plugin-unstall-xn_nav_more.htm';

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
		http_403();
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
	default: 
		// 为了支持插件，此处不利于编译优化
		// In order to support / plug-in, here is not conducive to compiler optimization
		(!is_word($route) || !is_file("./route/$route.php")) && http_404();
		include "./route/$route.php";
}

?>