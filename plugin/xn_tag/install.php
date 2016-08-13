<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件安装
	admin/plugin-install-xn_tag.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;


$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_cate (
	cateid smallint(11) unsigned NOT NULL AUTO_INCREMENT,
	fid smallint(11) unsigned NOT NULL DEFAULT '0',		# 属于哪个版块
	name char(32) NOT NULL DEFAULT '',
	PRIMARY KEY (cateid)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8";
$r = db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_name (
	tagid smallint(11) unsigned NOT NULL AUTO_INCREMENT,
	cateid smallint(11) unsigned NOT NULL DEFAULT '0',
	name char(32) NOT NULL DEFAULT '',
	PRIMARY KEY (cateid)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8";
$r = db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag (
	tagid smallint(11) unsigned NOT NULL DEFAULT '0',
	tid smallint(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (tagid),
	KEY (tid)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8";
$r = db_exec($sql);


$r === FALSE AND message(-1, '创建友情链接表结构失败');

?>