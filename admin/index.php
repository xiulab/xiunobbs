<?php

// 切换到上级目录
//define('BASE_HREF', '../');
define('SKIP_ROUTE', TRUE);
chdir('../');
include './index.php';

/*
	切换回来，主要为了方便 admin 目录改名，有利于安全
	处理相对路径比较麻烦一点，一般直接工作在根目录，
	像 install/index.php plugin/xn_umeditor/upload.php 那样属于一般写法，不用处理 $conf['tmp_path'] 等路径相对问题。
*/
chdir(dirname(__FILE__));
$conf['log_path'][0] == '.' AND $conf['log_path'] = '../'.$conf['log_path'];
$conf['tmp_path'][0] == '.' AND $conf['tmp_path'] = '../'.$conf['tmp_path'];
$conf['upload_path'][0] == '.' AND $conf['upload_path'] = '../'.$conf['upload_path'];

$lang += include "../lang/$conf[lang]/bbs_admin.php";
include "./admin.func.php";
$menu = include './menu.conf.php';

// 只允许管理员登陆后台
// 对于越权访问，可以默认为黑客企图，不用友好提示。
$gid != 1 AND http_403();

// 管理员令牌检查
admin_token_check();

$route = param(0, 'index');

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'index':		include './route/index.php'; 		break;
	case 'setting': 	include './route/setting.php'; 		break;
	case 'forum': 		include './route/forum.php'; 		break;
	case 'friendlink': 	include './route/friendlink.php'; 	break;
	case 'group': 		include './route/group.php'; 		break;
	case 'user':		include './route/user.php'; 		break;
	default: http_404();
}

?>