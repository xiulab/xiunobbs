<?php

/*
	Xiuno BBS 4.0 插件实例：幸运楼层
	admin/plugin-install-xn_lucky_post.htm
*/

!defined('DEBUG') AND exit('Forbidden');

//$setting = setting_get('xn_lucky_post');
//if(empty($setting)) {
//	$setting = array('body_start'=>'', 'body_end'=>'', 'footer_end'=>'');
//	setting_set('xn_lucky_post', $setting);
//}

// 幸运楼层
$tablepre = $db->tablepre;
$sql = "ALTER TABLE {$tablepre}thread ADD COLUMN is_lucky_thread tinyint(3) NOT NULL default '0'";
db_exec($sql);

$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}thread_lucky_post (
	tid int(11) unsigned NOT NULL default '0',
	pids text NOT NULL DEFAULT '',
	success_template text NOT NULL DEFAULT '',	# 成功以后的提示语言
	PRIMARY KEY (tid)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。



?>