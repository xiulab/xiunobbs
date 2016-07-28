<?php

/*
	Xiuno BBS 4.0 插件实例
	广告插件设置程序
*/

// 切换到上级目录
define('BASE_HREF', '../../');
define('SKIP_ROUTE', TRUE);
chdir('../../');
include './index.php';

$setting = kv_get('xn_ad_setting');

message(-1, 'err');

exit;
if($method == 'GET') {
	
	$input = array();
	$input['body_start'] = form_textarea('body_start', $setting['body_start'], '100%', '100px');
	$input['body_end'] = form_textarea('body_end', $setting['body_end'], '100%', '100px');
	
	include './plugin/xn_ad/setting.htm';
	
} else {

	$setting['body_start'] = param('body_start', '', FALSE);
	$setting['body_end'] = param('body_end', '', FALSE);
	
	kv_set('xn_ad_setting', $setting);
	
	message(0, '修改成功');
}
	
?>