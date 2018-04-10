<?php

/*
	Xiuno BBS 4.0 插件实例：二级导航
	admin/plugin-setting-xn_nav_2.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$input = array();
	$input['nav_2_bbs_on'] = form_radio_yes_no('nav_2_bbs_on', $conf['nav_2_bbs_on']);
	$input['nav_2_forum_list_pc_on'] = form_radio_yes_no('nav_2_forum_list_pc_on', $conf['nav_2_forum_list_pc_on']);
	$input['nav_2_forum_list_mobile_on'] = form_radio_yes_no('nav_2_forum_list_mobile_on', $conf['nav_2_forum_list_mobile_on']);
	
	include _include(APP_PATH.'plugin/xn_nav_2/setting.htm');
	
} else {

	$nav_2_bbs_on = param('nav_2_bbs_on', 0);
	$nav_2_forum_list_pc_on = param('nav_2_forum_list_pc_on', 0);
	$nav_2_forum_list_mobile_on = param('nav_2_forum_list_mobile_on', 0);
	
	$replace = array();
	$replace['nav_2_bbs_on'] = $nav_2_bbs_on;
	$replace['nav_2_forum_list_pc_on'] = $nav_2_forum_list_pc_on;
	$replace['nav_2_forum_list_mobile_on'] = $nav_2_forum_list_mobile_on;
	
	file_replace_var(APP_PATH.'conf/conf.php', $replace);
	
	message(0, '修改成功');
}
	
?>