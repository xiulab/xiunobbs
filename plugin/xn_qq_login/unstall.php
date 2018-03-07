<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件卸载
	admin/plugin-unstall-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "DROP TABLE IF EXISTS {$tablepre}user_open_plat;";

$r = db_exec($sql);

?>