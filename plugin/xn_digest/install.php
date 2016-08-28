<?php

/*
	Xiuno BBS 4.0 插件实例：精华主题安装
	admin/plugin-install-xn_digest.htm
*/

!defined('DEBUG') AND exit('Forbidden');

# 精华主题，小表代替大索引，bbs_thread 的扩展表
$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}thread_digest (
  fid smallint(6) NOT NULL default '0',			# 版块id
  tid int(11) unsigned NOT NULL default '0',		# 主题id
  uid int(11) unsigned NOT NULL default '0',		# uid
  digest tinyint(3) unsigned NOT NULL default '0',	# 精华等级
  PRIMARY KEY (tid),					# 
  KEY (uid),						# 
  UNIQUE KEY (fid, tid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
db_exec($sql);

$sql = "ALTER TABLE {$tablepre}forum ADD COLUMN digests tinyint(3) unsigned NOT NULL default '0';";
db_exec($sql);

$sql = "ALTER TABLE {$tablepre}thread ADD COLUMN digest tinyint(3) unsigned NOT NULL default '0';";
db_exec($sql);

$sql = "ALTER TABLE {$tablepre}user ADD COLUMN digests tinyint(3) unsigned NOT NULL default '0';";
db_exec($sql);


?>