<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件安装
	admin/plugin-install-xn_tag.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;


$sql = "ALTER TABLE {$tablepre}tag ADD COLUMN style char(32) NOT NULL DEFAULT ''";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}tag_cate ADD COLUMN defaulttagid int(10) unsigned NOT NULL DEFAULT '0'";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}tag_cate ADD COLUMN isforce int(10) unsigned NOT NULL DEFAULT '0'";
$r = db_exec($sql);

// tag 缓存的时间
setting_set('tag_update_time', $time);

//$r === FALSE AND message(-1, '创建表结构失败');

?>