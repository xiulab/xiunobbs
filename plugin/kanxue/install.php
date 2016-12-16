<?php

/*
	Xiuno BBS 4.0 插件实例：在线时间
	admin/plugin-install-xn_online_time.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE {$tablepre}user ADD COLUMN online_time INT NOT NULL DEFAULT '0'";

$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败');

?>