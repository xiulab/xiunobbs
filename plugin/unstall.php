<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件卸载
*/

!defined('DEBUG') AND exit('Forbidden');

// 安装后，额外的操作
// 将会被 http://bbs.domain.com/admin/plugin-xn_friendlink-install.htm 调用

// 执行 SQL
$tablepre = $db->tablepre;
$sql = "DROP TABLE IF EXISTS {$tablepre}friendlink;";

$r = db_exec($sql);
$r === FALSE AND message(-1, '卸载友情链接表失败');

?>