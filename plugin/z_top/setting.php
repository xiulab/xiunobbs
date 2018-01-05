<?php

/*
	这里是选择角色时的处理文件..
	虽然只是记录一个数值还是分开写吧..
*/

!defined('DEBUG') AND exit('Access Denied.');

$setting = setting_get('z_top_setting');

if($method == 'GET') {
	
	$input = array();
	$input['body_start'] = form_textarea('body_start', $setting['body_start'], '100%', '100px');
	
	include _include(APP_PATH.'plugin/z_top/setting.htm');
	
} else {

	$setting['body_start'] = param('body_start', '', FALSE);
	
	setting_set('z_top_setting', $setting);
	
	message(0, '修改成功');
}
	
?>