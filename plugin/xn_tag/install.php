<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件安装
	admin/plugin-install-xn_tag.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;


$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_cate (
	cateid int(11) unsigned NOT NULL AUTO_INCREMENT,
	fid int(11) unsigned NOT NULL DEFAULT '0',		# 属于哪个版块
	name char(32) NOT NULL DEFAULT '',
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (cateid),
	KEY (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag (
	tagid int(11) unsigned NOT NULL AUTO_INCREMENT,
	cateid int(11) unsigned NOT NULL DEFAULT '0',
	name char(32) NOT NULL DEFAULT '',
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (tagid),
	KEY (cateid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_thread (
	tagid int(11) unsigned NOT NULL DEFAULT '0',
	tid int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (tagid, tid),
	KEY (tid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

// 缓存 tagid 10000,10000,10000,10000
$sql = "ALTER TABLE {$tablepre}thread ADD COLUMN tagids char(32) NOT NULL DEFAULT ''";
$r = db_exec($sql);

// 缓存的时间，用来和 setting('tag_update_time') 对比
$sql = "ALTER TABLE {$tablepre}thread ADD COLUMN tagids_time int(11) unsigned NOT NULL DEFAULT '0'";
$r = db_exec($sql);


//$r === FALSE AND message(-1, '创建表结构失败');

?>