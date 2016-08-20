<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('qq_login');
	
	$input = array();
	$input['meta'] = form_text('meta', $kv['meta']);
	$input['appid'] = form_text('appid', $kv['appid']);
	$input['appkey'] = form_text('appkey', $kv['appkey']);
	
	include _include(APP_PATH.'plugin/xn_qq_login/setting.htm');
	
} else {

	$kv = array();
	$kv['meta'] = param('meta');
	$kv['appid'] = param('appid');
	$kv['appkey'] = param('appkey');
	
	kv_set('qq_login', $kv);
	
	message(0, '修改成功');
}
	
?>