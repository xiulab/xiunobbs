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

$fid = 1;
$uid = 1;
$subject = $message = 'test';
$seo_url = '';

// 检查总帖数
$forum1 = forum__read($fid);
$user1 = user__read($uid);

thread_create($fid, $uid, $subject, $message, $seo_url, $time, $longip);

$forum2 = forum__read($fid);
$user2 = user__read($uid);

x('forum.threads', $forum1['threads'] + 1, intval($forum2['threads']));
x('user.threads', $user1['threads'] + 1, intval($user2['threads']));

// 资源清理，删除用户:

function x($info, $a, $b) {
	echo "$info: ... ".($a === $b ? 'true' : 'false'.", ".var_export($a, 1).", ".var_export($b, 1))."\r\n";
}
?>