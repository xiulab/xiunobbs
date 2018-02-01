<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$setting['footer_end_htm'] = setting_get('footer_end_htm');
	$setting['footer_footer_left_end_htm'] = setting_get('footer_footer_left_end_htm');
	$setting['footer_footer_right_end_htm'] = setting_get('footer_footer_right_end_htm');
	$setting['footer_start_htm'] = setting_get('footer_start_htm');
	$setting['index_main_start_htm'] = setting_get('index_main_start_htm');
	$setting['index_site_brief_after_htm'] = setting_get('index_site_brief_after_htm');
	$setting['thread_user_after_htm'] = setting_get('thread_user_after_htm');
	
	include _include(APP_PATH.'plugin/xn_insert_code/setting.htm');
	
} else {

	setting_set('footer_end_htm', param('footer_end_htm', '', FALSE));
	setting_set('footer_footer_left_end_htm', param('footer_footer_left_end_htm', '', FALSE));
	setting_set('footer_footer_right_end_htm', param('footer_footer_right_end_htm', '', FALSE));
	setting_set('footer_start_htm', param('footer_start_htm', '', FALSE));
	setting_set('index_main_start_htm', param('index_main_start_htm', '', FALSE));
	setting_set('index_site_brief_after_htm', param('index_site_brief_after_htm', '', FALSE));
	setting_set('thread_user_after_htm', param('thread_user_after_htm', '', FALSE));
	
	message(0, '修改成功');
}
	
?>