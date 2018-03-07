<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆安装
	admin/plugin-install-xn_friendlink.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}user_open_plat (
	uid int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
	platid tinyint(1) NOT NULL DEFAULT '0' COMMENT '平台编号  0:本站 1:QQ 登录 2:微信登陆 3:支付宝登录 ',
	openid char(40) NOT NULL DEFAULT '' COMMENT '第三方唯一标识',
	PRIMARY KEY (uid),
	KEY openid_platid (platid,openid)
) ENGINE=MyISAM AUTO_INCREMENT=8805 DEFAULT CHARSET=utf8";

$r = db_exec($sql);

// 初始化
$kv = kv_get('qq_login');
if(!$kv) {
	$kv = array('meta'=>'', 'appid'=>'', 'appkey'=>'');
	kv_set('qq_login', $kv);
}

?>