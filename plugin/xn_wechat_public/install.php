<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件安装
	admin/plugin-install-xn_friendlink.htm
*/

!defined('DEBUG') AND exit( 'Forbidden' );

$tablepre = $db->tablepre;
$sql = "CREATE TABLE {$tablepre}user_open_wechat (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL COMMENT '用户编号',
  openid char(40) NOT NULL COMMENT '微信 OPENID 标识',
  PRIMARY KEY (id),
  KEY openid (openid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;8";

$r = db_exec($sql);

// 初始化
$r === false AND message(-1, '创建微信登录表结构失败');
?>