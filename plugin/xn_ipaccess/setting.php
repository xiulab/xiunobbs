<?php

/*
	Xiuno BBS 4.0 插件实例：每日 IP 登陆插件设置
	admin/plugin-setting-xn_ipaccess.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('ipaccess');
	
	$input = array();
	$input['users'] = form_text('users', $kv['users']);
	$input['logins'] = form_text('logins', $kv['logins']);
	$input['mails'] = form_text('mails', $kv['mails']);
	$input['threads'] = form_text('threads', $kv['threads']);
	$input['posts'] = form_text('posts', $kv['posts']);
	$input['attachs'] = form_text('attachs', $kv['attachs']);
	$input['attachsizes'] = form_text('attachsizes', $kv['attachsizes']);
	$input['seriate_threads'] = form_text('seriate_threads', $kv['seriate_threads']);
	$input['seriate_posts'] = form_text('seriate_posts', $kv['seriate_posts']);
	$input['seriate_users'] = form_text('seriate_users', $kv['seriate_users']);
	
	include _include(APP_PATH.'plugin/xn_ipaccess/setting.htm');
	
} else {

	$kv = array();
	$kv['users'] = param('users', 0);
	$kv['logins'] = param('logins', 0);
	$kv['mails'] = param('mails', 0);
	$kv['threads'] = param('threads', 0);
	$kv['posts'] = param('posts', 0);
	$kv['attachs'] = param('attachs', 0);
	$kv['attachsizes'] = param('attachsizes', 0);
	$kv['seriate_threads'] = param('seriate_threads', 0);
	$kv['seriate_posts'] = param('seriate_posts', 0);
	$kv['seriate_users'] = param('seriate_users', 0);
	
	kv_set('ipaccess', $kv);
	
	message(0, '修改成功');
}
	
?>