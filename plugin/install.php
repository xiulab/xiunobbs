<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件安装
*/

!defined('DEBUG') AND exit('Forbidden');

// 安装后，额外的操作
// 将会被 http://bbs.domain.com/admin/plugin-xn_friendlink-install.htm 调用

// 执行 SQL
$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}friendlink (
  linkid bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  type smallint(11) NOT NULL DEFAULT '0',
  rank smallint(11) NOT NULL DEFAULT '0',
  create_date int(11) unsigned NOT NULL DEFAULT '0',
  name char(32) NOT NULL DEFAULT '',
  url char(64) NOT NULL DEFAULT '',
  PRIMARY KEY (linkid),
  KEY type (type)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8
";

$r = db_exec($sql);
$r === FALSE AND message(-1, '创建友情链接表结构失败');

?>