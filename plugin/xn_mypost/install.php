<?php

/*
	Xiuno BBS 4.0 插件实例：我的回帖
	admin/plugin-install-xn_mypost.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

xn_unlink(APP_PATH.'./view/htm/my_post.htm');
xn_unlink(APP_PATH.'./view/htm/user_post.htm');

$sql = "DROP TABLE IF EXISTS bbs_mypost;";
$r = db_exec($sql);

$sql = "CREATE TABLE bbs_mypost (
  uid int(11) unsigned NOT NULL default '0',		# uid
  tid int(11) unsigned NOT NULL default '0',		# 用来清理
  pid int(11) unsigned NOT NULL default '0',		#
  KEY (tid),						#
  PRIMARY KEY (uid, pid)				#
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
$r = db_exec($sql);

?>