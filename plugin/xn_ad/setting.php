<?php

/*
	Xiuno BBS 4.0 插件实例：广告插件设置
	admin/plugin-setting-xn_ad.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$setting = kv_get('xn_ad_setting');

if($method == 'GET') {
	
	$input = array();
	$input['body_start'] = form_textarea('body_start', $setting['body_start'], '100%', '100px');
	$input['body_end'] = form_textarea('body_end', $setting['body_end'], '100%', '100px');
	
	include _include(APP_PATH.'plugin/xn_ad/setting.htm');
	
} else {

	$setting['body_start'] = param('body_start', '', FALSE);
	$setting['body_end'] = param('body_end', '', FALSE);
	
	kv_set('xn_ad_setting', $setting);
	
	message(0, '修改成功');
}
	
?>