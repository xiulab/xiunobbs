<?php

/*
	Xiuno BBS 3.0 插件实例
	贴图库插件卸载程序
*/

define('DEBUG', 0); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$pconf = xn_json_decode(file_get_contents('./plugin/xn_tietuku/conf.json'));
$pconf['installed'] == 0 AND message(-1, '插件已经卸载。');

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, jump('需要管理员权限才能完成卸载。', 'user-login.htm'));


//---------------> 第一处卸载
plugin_install_remove('./route/post.php', file_get_contents('./plugin/xn_tietuku/route_post_insert.inc.txt'));


json_conf_set('installed', 0, './plugin/xn_tietuku/conf.json');

message(0, '卸载完成！');


?>