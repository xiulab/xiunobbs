<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件卸载
	admin/plugin-unstall-xn_friendlink.htm
*/

!defined('DEBUG') AND exit( 'Forbidden' );

$tablepre = $db->tablepre;
$sql = "DROP TABLE IF EXISTS {$tablepre}user_open_wechat;";

$r = db_exec($sql);

$r === false AND message(-1, '卸载微信公众号插件表失败');
?>