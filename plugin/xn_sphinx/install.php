<?php

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$pconf = xn_json_decode(file_get_contents('./plugin/xn_sphinx/conf.json'));
$pconf['installed'] == 1 AND message(-1, '插件已经安装，请不要重复安装。');

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, '需要管理员权限才能完成安装。');

//---------------> 第一处插入
plugin_install_replace('./pc/route/search.php', '$threadlist = thread_find_by_keyword($keyword);', file_get_contents('./plugin/xn_sphinx/pc_route_search_1.txt'));

json_conf_set('installed', 1, './plugin/xn_sphinx/conf.json');

message(0, '安装完成！');

?>