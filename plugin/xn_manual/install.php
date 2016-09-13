<?php

/*
	Xiuno BBS 4.0 插件实例：在线手册
	admin/plugin-install-xn_manual.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

// 支持多本手册
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}manual (
	manualid int(11) unsigned NOT NULL AUTO_INCREMENT,	
	name char(32) NOT NULL DEFAULT '',			# manual name
	cover char(32) NOT NULL DEFAULT '',			# cover image url: upload/manual/1/cover.png
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (manualid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

// 暂时支持一级，预留无限级
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}manual_cate (
	cateid int(11) unsigned NOT NULL AUTO_INCREMENT,
	catepid int(11) unsigned NOT NULL DEFAULT '0',
	manualid int(11) unsigned NOT NULL DEFAULT '0',
	name char(64) NOT NULL DEFAULT '',
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	articleid int(11) unsigned NOT NULL DEFAULT '0',	# 文章 id, 如果指定，则表示为最后一级
	PRIMARY KEY (cateid),
	KEY (manualid, rank),
	KEY (articleid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

// 附件存放于 upload/manual/1/123_xxx.jpg，文章编辑，删除时，遍历目录，删除附件（不建立附件表）
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}manual_article (
	articleid int(11) unsigned NOT NULL AUTO_INCREMENT,	# 文章 id
	cateid int(11) unsigned NOT NULL DEFAULT '0',		# 所属分类的 id
	manualid int(11) unsigned NOT NULL DEFAULT '0',		# 手册 id
	subject char(128) NOT NULL DEFAULT '',			# 标题
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	uid int(11) unsigned NOT NULL DEFAULT '0',		# 作者
	replies int(11) unsigned NOT NULL DEFAULT '0',		# 回复数
	message mediumtext NOT NULL DEFAULT '',			# 文章内容
	PRIMARY KEY (articleid),
	KEY (cateid, rank),
	KEY (manualid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

// 附件存放于 upload/manual/1/123_xxx.jpg，文章编辑，删除时，遍历目录，删除附件（不建立附件表）
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}manual_reply (
	replyid int(11) unsigned NOT NULL AUTO_INCREMENT,	# 评论 id
	articleid int(11) unsigned NOT NULL DEFAULT '0',	# 文章 id
	cateid int(11) unsigned NOT NULL DEFAULT '0',		# 所属分类的 id
	manualid int(11) unsigned NOT NULL DEFAULT '0',		# 手册 id
	uid int(11) unsigned NOT NULL DEFAULT '0',		#
	create_date int(11) unsigned NOT NULL DEFAULT '0',	#
	message mediumtext NOT NULL DEFAULT '',			#
	PRIMARY KEY (replyid),
	KEY (articleid, replyid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

//$r === FALSE AND message(-1, '创建表结构失败');

?>