<?php
!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('sg_hide');
    $hide1 = $kv['hide1'];
	$hide2 = $kv['hide2'];
	
	include _include(APP_PATH.'plugin/sg_hide/setting.htm');
	
} else {

	$kv = array();
	$kv['hide1'] = param('hide1');
	$kv['hide2'] = param('hide2');
	
	kv_set('sg_hide', $kv);
	
	message(0, '修改成功');
}
	
?>