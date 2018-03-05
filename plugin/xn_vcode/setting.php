<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('vcode');
	
	
	$input = array();
	$input['vcode_user_login_on'] = form_radio_yes_no('vcode_user_login_on', $kv['vcode_user_login_on']);
	$input['vcode_user_create_on'] = form_radio_yes_no('vcode_user_create_on', $kv['vcode_user_create_on']);
	$input['vcode_user_findpw_on'] = form_radio_yes_no('vcode_user_findpw_on', $kv['vcode_user_findpw_on']);
	$input['vcode_thread_create_on'] = form_radio_yes_no('vcode_thread_create_on', $kv['vcode_thread_create_on']);
	
	// hook plugin_vcode_setting_get_end.htm
	
	include _include(APP_PATH.'plugin/xn_vcode/setting.htm');
	
} else {

	$kv = array();
	$kv['vcode_user_login_on'] = param('vcode_user_login_on');
	$kv['vcode_user_create_on'] = param('vcode_user_create_on');
	$kv['vcode_user_findpw_on'] = param('vcode_user_findpw_on');
	$kv['vcode_thread_create_on'] = param('vcode_thread_create_on');
	
	// hook plugin_vcode_setting_kv_set_before.htm
	kv_set('vcode', $kv);
	
	// hook plugin_vcode_setting_post_end.htm
	message(0, '修改成功');
}
	
?>