<?php

/*
	Xiuno BBS 4.0 插件实例：搜索
	admin/plugin-install-xn_search.htm
*/

!defined('DEBUG') AND exit('Forbidden');

# 论坛帖子数据，一页显示，不分页。
$sql = "ALTER TABLE bbs_post ADD message_words text NOT NULL";

$sql = "
CREATE TABLE IF NOT EXISTS bbs_post_search (
  tid int(11) unsigned NOT NULL default '0',		# 主题id
  pid int(11) unsigned NOT NULL auto_increment,		# 帖子id
  message_words longtext NOT NULL,			# 内容，存放切词，空格隔开，FULLTEXT
  PRIMARY KEY (pid),
  KEY (tid, pid),
  FULLTEXT(message_words)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
// 只支持 mysql
$conf['db']['type'] != 'mysql' && $conf['db']['type'] != 'pdo_mysql' AND message(-1, '仅支持 MYSQL 数据库。');

// 判断版本
$db->innodb_first = FALSE;
db_exec($sql);

// 默认为 FULLTEXT 搜索
setting_set('xn_search_type', 'FULLTEXT'); // LIKE|FULLTEXT
setting_set('xn_search_cutword_url', 'http://plugin.xiuno.com/cutword.php');

?>