<?php

/*
	Xiuno BBS 4.0 插件实例：幸运楼层
	admin/plugin-install-xn_lucky_post.htm
*/

!defined('DEBUG') AND exit('Forbidden');

/*$setting = setting_get('xn_lucky_post');
if(empty($setting)) {
	$setting = array('body_start'=>'', 'body_end'=>'', 'footer_end'=>'');
	setting_set('xn_lucky_post', $setting);
}*/

// 幸运楼层
$tablepre = $db->tablepre;
$sql = "ALTER TABLE {$tablepre}thread DROP COLUMN is_lucky_thread";
db_exec($sql);

$sql = "DROP TABLE IF EXISTS {$tablepre}thread_lucky_post;";
$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。



?>