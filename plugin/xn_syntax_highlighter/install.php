<?php

/*
	Xiuno BBS 3.0 插件实例
	代码高亮插件安装程序
*/

define('DEBUG', 0); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的名称

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$pconf = xn_json_decode(file_get_contents('./plugin/xn_syntax_highlighter/conf.json'));
$pconf['installed'] == 1 AND message(-1, '插件已经安装，请不要重复安装。');

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, jump('需要管理员权限才能完成安装。', 'user-login.htm'));


//---------------> 第一处插入，末尾追加
plugin_install_append('./pc/view/thread.htm', file_get_contents('./plugin/xn_syntax_highlighter/thread_end.htm'));


json_conf_set('installed', 1, './plugin/xn_syntax_highlighter/conf.json');

message(0, '安装完成！');


?>