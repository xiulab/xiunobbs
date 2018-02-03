<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆安装
	admin/plugin-install-xn_friendlink.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE ADD INDEX {$tablepre}user mobile(mobile)";

$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。

// 初始化
$kv = kv_get('mobile');
if(!$kv) {
	$kv = array('meta'=>'', 'appid'=>'', 'appkey'=>'');
	kv_set('qq_login', $kv);
}

?>