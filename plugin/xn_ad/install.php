<?php

/*
	Xiuno BBS 3.0 插件实例
	广告插件安装程序
*/

!defined('DEBUG') AND exit('Forbidden');

// 安装后，额外的操作
// 将会被 http://bbs.domain.com/admin/plugin-xn_ad-install.htm 调用

// 执行 SQL
// db_exec('CREATE TABLE mytable();');

// 创建我的目录
// mkdir('./mydir');

$setting = kv_get('xn_ad_setting');
if(empty($setting)) {
	$setting = array('body_start'=>'', 'body_end'=>'');
	kv_set('xn_ad_setting', $setting);
}

?>