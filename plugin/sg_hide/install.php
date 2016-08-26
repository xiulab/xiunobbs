<?php
!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE `{$tablepre}thread` ADD `hide2` TINYINT(0) NULL DEFAULT NULL AFTER `lastpid`;
ALTER TABLE `{$tablepre}thread` ADD `hide1` TINYINT(0) NULL DEFAULT NULL AFTER `lastpid`;";

$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。

// 初始化
$kv = kv_get('sg_hide');
if(!$kv) {
	$kv = array('hide1'=>'', 'hide2'=>'');
	kv_set('sg_hide', $kv);
}
?>