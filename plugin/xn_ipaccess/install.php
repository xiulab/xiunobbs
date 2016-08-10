<?php

/*
	Xiuno BBS 4.0 插件实例：每日 IP 限制安装
	admin/plugin-install-xn_ipaccess.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}ipaccess (
  ip int(11) unsigned NOT NULL,			# ip 地址
  mails smallint(11) NOT NULL default '0',	# 每日发送邮件数
  users smallint(11) NOT NULL default '0',	# 注册用户个数
  logins smallint(11) NOT NULL default '0',	# 每日登陆次数，防止暴力密码猜测
  threads smallint(11) NOT NULL default '0',	# 发表主题数
  posts smallint(11) NOT NULL default '0',	# 发表回帖数
  attachs smallint(11) NOT NULL default '0',	# 附件数
  attachsizes smallint(11) NOT NULL default '0',# 附件总大小
  last_date int(11) NOT NULL default '0',	# 最后一次操作的时间，用来检测频度
  actions int(11) NOT NULL default '0',		# 今日总共操作的次数
  action1 int(11) NOT NULL default '0',		# 预留1，供插件使用
  action2 int(11) NOT NULL default '0',		# 预留2，供插件使用
  action3 int(11) NOT NULL default '0',		# 预留3，供插件使用
  action4 int(11) NOT NULL default '0',		# 预留4，供插件使用
  PRIMARY KEY (ip)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
";
$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。

$kv = array(
	'users'=>0,
	'logins'=>0,
	'threads'=>0,
	'posts'=>0,
	'mails'=>0,
	'attachs'=>0,
	'attachsizes'=>0,
	'seriate_threads'=>0,
	'seriate_posts'=>0,
	'seriate_users'=>0,
);

kv_set('ipaccess', $kv);

?>