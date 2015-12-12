<?php

define('APP_NAME', 'test');

chdir(getcwd().'/../');

include './XiunoPHP.3.0.php';
include './model.inc.php';

$forumlist = forum_list_cache();
$grouplist = group_list_cache();
$user = user_token_get('', 'bbs'); 	// 全局的 user，全局变量除了 $conf, 其他全部加下划线
$uid = $user['uid'];				// 全局的 uid
$gid = $user['gid'];				// 全局的 gid

$header['title'] = $conf['sitename']; 		// 网站标题
$header['keywords'] = $conf['sitename']; 		// 关键词
$header['description'] = $conf['sitename']; 	// 描述

// 启动在线，将清理函数注册，不能写日志。
runtime_init();
online_init();
register_shutdown_function('online_save');
register_shutdown_function('runtime_save');

user_create($arr);

// 资源清理，删除用户:

function x($info, $a, $b) {
	echo "$info: ... ".($a === $b ? 'true' : 'false'.", ".var_export($a, 1).", ".var_export($b, 1))."\r\n";
}
?>