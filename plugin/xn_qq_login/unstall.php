<?php

/*
	广告插件卸载程序
*/

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$pconf = xn_json_decode(file_get_contents('./plugin/xn_qq_login/conf.json'));
$pconf['installed'] == 0 AND message(-1, '插件已经卸载。');

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, '需要管理员权限才能完成卸载。');


// 第一处卸载
plugin_unstall_after('./pc/view/user_login.htm', '</dl>', file_get_contents('./plugin/xn_qq_login/user_login.htm.1.inc.htm'));


// 第二处卸载
plugin_unstall_after('./mobile/view/user_login.htm', '</dl>', file_get_contents('./plugin/xn_qq_login/user_login.htm.1.inc.htm'));


$runtime = runtime_init();
runtime_set('qq_login_enable', 0, TRUE);

// 执行 sql
// db_exec('DROP TABLE IF EXISTS bbs_user_open_plat;');

json_conf_set('installed', 0, './plugin/xn_qq_login/conf.json');

message(0, '卸载完成！');


?>