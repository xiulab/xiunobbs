<?php

/*
	Xiuno BBS 4.0 插件实例：导航栏更多插件设置
	admin/plugin-setting-xn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	// 导航栏超过多少开始显示
	$limit = setting_get('nav_link_limit');
	
	$input = array();
	$input['limit'] = form_text('limit', $limit);
	
	include _include(APP_PATH.'plugin/xn_nav_more/setting.htm');
	
} else {

	$limit = param('limit');
	
	setting_set('nav_link_limit', $limit);
	
	message(0, '修改成功');
}
	
?>