<?php

/*
	Xiuno BBS 4.0 插件实例：逻辑删除插件安装
	admin/plugin-install-xn_logic_delete.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

$sql = "ALTER TABLE {$tablepre}thread ADD deleted tinyint NOT NULL DEFAULT '0';";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}post ADD deleted tinyint NOT NULL DEFAULT '0';";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}group ADD allowharddelete tinyint NOT NULL DEFAULT '0';";
$r = db_exec($sql);
// $r === FALSE AND message(-1, '修改 thread 表结构失败');

?>