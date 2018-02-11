<?php

/*
	Xiuno BBS 4.0 插件实例：编辑增强
	admin/plugin-install-xn_mod_enhance.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

$sql = "ALTER TABLE {$tablepre}post ADD COLUMN updates int NOT NULL default '0', ADD COLUMN last_update_date int NOT NULL default '0', ADD COLUMN last_update_uid int NOT NULL default '0', ADD COLUMN last_update_reason varchar(128) NOT NULL default ''";
$r = db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}post_update_log (
	logid int(10) unsigned NOT NULL auto_increment,
	pid int(10) unsigned NOT NULL default '0',
	reason varchar(128) NOT NULL DEFAULT '',
	message text NOT NULL DEFAULT '',
	create_date int(10) unsigned NOT NULL default '0',
	uid int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (logid),
	KEY (pid),
	KEY (uid)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

$r = db_exec($sql);
// $r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。

?>