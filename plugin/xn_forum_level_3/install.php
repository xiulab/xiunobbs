<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件安装
	admin/plugin-install-xn_friendlink.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE {$tablepre}forum ADD COLUMN fup int(11) unsigned NOT NULL default '0'";

$r = db_exec($sql);

cache_truncate();

message(0, '创建成功');
?>