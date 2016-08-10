<?php

/*
	Xiuno BBS 4.0 插件实例：每日 IP 限制插件卸载
	admin/plugin-unstall-xn_ipaccess.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "DROP TABLE IF EXISTS {$tablepre}ipaccess;";

$r = db_exec($sql);
$r === FALSE AND message(-1, '卸载表失败');

?>