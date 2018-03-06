<?php

/*
	Xiuno BBS 4.0 消息
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}notice (
	nid int(11) unsigned NOT NULL auto_increment, 
	fromuid int(11) unsigned NOT NULL default '0',	
	recvuid int(11) unsigned NOT NULL default '0',	 
	create_date int(11) unsigned NOT NULL default '0',	
	isread tinyint(3) unsigned NOT NULL default '0',
	type tinyint(3) unsigned NOT NULL default '0',	
	message longtext NOT NULL,				      
	PRIMARY KEY (nid),
	KEY (fromuid, type),
	KEY (recvuid, type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
db_exec($sql);

// 消息数
$sql = "ALTER TABLE {$tablepre}user ADD COLUMN notices mediumint(8) unsigned NOT NULL default '0';";
db_exec($sql);

// 未读的消息数
$sql = "ALTER TABLE {$tablepre}user ADD COLUMN unread_notices mediumint(8) unsigned NOT NULL default '0';";
db_exec($sql);






?>