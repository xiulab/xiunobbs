<?php

/*
	QQ 登陆插件
*/

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$pconf = xn_json_decode(file_get_contents('./plugin/xn_qq_login/conf.json'));
$pconf['installed'] == 1 AND message(-1, '插件已经安装，请不要重复安装。');

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, '需要管理员权限才能完成安装。');


//---------------> 第一处插入
plugin_install_after('./pc/view/user_login.htm', '</dl>', file_get_contents('./plugin/xn_qq_login/user_login.htm.1.inc.htm'));


//---------------> 第二处插入
plugin_install_after('./mobile/view/user_login.htm', '</dl>', file_get_contents('./plugin/xn_qq_login/user_login.htm.1.inc.htm'));


$runtime = runtime_init();
runtime_set('qq_login_enable', 1, TRUE);

// 执行 sql
db_exec("CREATE TABLE IF NOT EXISTS bbs_user_open_plat (
	  uid int(11) unsigned NOT NULL DEFAULT '0',
	  platid tinyint(1) NOT NULL DEFAULT '0' COMMENT '平台编号', # 0:本站 1:QQ 登录 2:微信登陆 3:支付宝登录 
	  openid char(32) NOT NULL DEFAULT '',
	  PRIMARY KEY(uid),
	  UNIQUE KEY(openid)
);");








json_conf_set('installed', 1, './plugin/xn_qq_login/conf.json');

message(0, '安装完成！');


?>