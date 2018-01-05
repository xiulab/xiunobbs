<?php

/*
	Xiuno BBS 4.0 插件实例：精华主题卸载
	admin/plugin-unstall-highlight.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "DROP TABLE {$tablepre}subject_style";
db_exec($sql);

$sql = "ALTER TABLE {$tablepre}thread drop column style_id";
db_exec($sql);
?>