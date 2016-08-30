<?php

/*
	Xiuno BBS 4.0 插件实例：搜索
	admin/plugin-install-xn_search.htm
*/

!defined('DEBUG') AND exit('Forbidden');

# 论坛帖子数据，一页显示，不分页。
$sql = "CREATE TABLE IF NOT EXISTS bbs_thread_search (
  tid int(11) unsigned NOT NULL default '0',		# 主题 id
  message longtext NOT NULL,				# 回帖内容合并后切词，存放于此，FULLTEXT
  UNIQUE KEY (tid),
  FULLTEXT(message)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS bbs_post_search (
  pid int(11) unsigned NOT NULL default '0',		# 主题帖子 id
  message longtext NOT NULL,				# 回帖内容合并后切词，存放于此，FULLTEXT
  UNIQUE KEY (pid),
  FULLTEXT(message)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
db_exec($sql);

// 默认为 FULLTEXT 搜索
kv_set('xn_search_type', 'like'); // LIKE|FULLTEXT
kv_set('xn_search_range', 0); // LIKE|FULLTEXT
kv_set('xn_search_cutword_url', 'http://plugin.xiuno.com/cutword.php');

?>