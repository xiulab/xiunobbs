<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件安装
	admin/plugin-install-xn_tag.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

/*
# 消息的记录，记录A给B发，如果很多，可以根据 recvuid 分区，多对多的关系, N*N(N = user.count())，控制在40*N
# 新消息 recvuid=123 AND count>0
# 删除某人关系 recvuid=123, senduid=222 (可能没有这个必要，造成碎片？)，最近联系人。保留最后40个！  一次取1000个，删除掉后面的。
# dateline 为最后更新的时间，可以用来排序。
*/
// DROP TABLE IF EXISTS bbs_pm_recent;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}pm_recent (
  recvuid int(11) unsigned NOT NULL default '0',	# 接受者 UID，与 user.newpms 配合使用，非唯一主键
  senduid int(11) unsigned NOT NULL default '0',	# 发送者 UID
  count int(11) unsigned NOT NULL default '0',		# 新消息的条数
  last_date int(11) unsigned NOT NULL default '0',	# 最后更新的时间， php 排序
  PRIMARY KEY (recvuid, senduid),
  KEY (recvuid, last_date),					# recvuid=123 order by last_date desc
  KEY (senduid, last_date)					# senduid=123 order by last_date desc
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
";
$r = db_exec($sql);

/*
# 根据 pmid 分区，变长表。没有全表扫描操作。
DROP TABLE IF EXISTS bbs_pm;
*/
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}pm (
  pmid bigint(11) unsigned NOT NULL auto_increment,	# pmid
  uid1 int(11) unsigned NOT NULL default '0',		# 用户id small uid
  uid2 int(11) unsigned NOT NULL default '0',		# 用户id big uid
  senduid int(11) unsigned NOT NULL default '0',		# 由谁发出
  username1 char(16) NOT NULL default '',		# 用户名	未登录为空
  username2 char(16) NOT NULL default '',		# 用户名	未登录为空
  create_date int(11) unsigned NOT NULL default '0',	# 时间
  message text NOT NULL,		        # 内容，没有编辑操作。避免碎片产生
  message_search text NOT NULL,		# 全文索引 search_uid_123 search_uid_234
  PRIMARY KEY (pmid),
  KEY (uid1, uid2, pmid),
  FULLTEXT (message_search)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}user ADD COLUMN newpms int NOT NULL default '0'";
$r = db_exec($sql);

//$r === FALSE AND message(-1, '创建表结构失败');

?>


