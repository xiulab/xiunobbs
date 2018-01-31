<?php

/*
	Xiuno BBS 4.0 插件实例：我的回帖
	admin/plugin-install-xn_mypost.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

/*
$sql = "DROP TABLE IF EXISTS {$tablepre}mypost;";
$r = db_exec($sql);
*/

db_exec("ALTER TABLE {$tablepre}post DROP INDEX uid_pid;");
?>