<?php

chdir('../');

define('DEBUG', 1);
define('APP_NAME', 'install');

$conf = (@include './conf/conf.default.php');

include './xiunophp/xiunophp.php';
include './model.inc.php';
include './install/install.func.php';

$browser = get__browser();
check_browser($browser);

$action = param('action');

// 安装初始化检测,放这里
is_file('./conf/conf.php') AND empty($action) AND !DEBUG AND install_message(0, jump('程序已经安装过了，如需重新安装，请删除 conf/conf.php ！', '../'));

// 第一步，阅读
if(empty($action)) {
	include "./install/view/index.htm";
} elseif($action == 'env') {
	if($method == 'GET') {
		$succeed = 1;
		$env = $write = array();
		get_env($env, $write);
		include "./install/view/env.htm";
	} else {
	
	}
} elseif($action == 'db') {
	if($method == 'GET') {
		$succeed = 1;
		$mysql_support = function_exists('mysql_connect');
		$pdo_mysql_support = extension_loaded('pdo_mysql');
		(!$mysql_support && !$pdo_mysql_support) AND install_message(0, '当前 PHP 环境不支持 mysql 和 pdo_mysql，无法继续安装。');

		include "./install/view/db.htm";
	} else {
		install_message(0, 'ok');
	}
} elseif($action == 'browser') {
	include "./install/view/browser.htm";
}


?>
