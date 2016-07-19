<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/form.func.php';

$action = param(1);

if(empty($action) || $action == 'list') {

	$method != 'GET' AND message(-1, 'Method error');

	$header['title'] = '禁止IP';

	$baniplist = banip_find();
	$maxbanid = banip_maxid();
	
	$input_banip_on = form_radio_yes_no('banip_on', $conf['banip_on']);
	
	empty($baniplist) AND $baniplist = array(array(
		'banid'=>1, 'ip0'=>0, 'ip1'=>0, 'ip2'=>0, 'ip3'=>0, 'create_date_fmt'=>'', 'expiry_fmt'=>'', 
	));
	
	include "./admin/view/banip.htm";

} elseif($action == 'update') {
	
	$method != 'POST' AND message(-1, 'Method error');
	
	$banid = param(2, 0);
	$ip0 = param('ip0', 0);
	$ip1 = param('ip1', 0);
	$ip2 = param('ip2', 0);
	$ip3 = param('ip3', 0);
	$expiry = param('expiry');
	$expiry = strtotime($expiry);
	
	$ip0 = mid($ip0, 0, 255);
	$ip1 = mid($ip1, 0, 255);
	$ip2 = mid($ip2, 0, 255);
	$ip3 = mid($ip3, 0, 255);
	
	$banip = banip_read($banid);
	
	if(empty($banip)) {
		$r = banip_create(array(
			'banid'=>$banid,
			'ip0'=>$ip0,
			'ip1'=>$ip1,
			'ip2'=>$ip2,
			'ip3'=>$ip3,
			'expiry'=>$expiry,
		));
		$r !== FALSE ? message(0, '创建成功') : message(-1, '创建失败');
	}
	
	$r = banip_update($banid, array('ip0'=>$ip0, 'ip1'=>$ip1, 'ip2'=>$ip2, 'ip3'=>$ip3, 'expiry'=>$expiry));
	
	$r !== FALSE ? message(0, '更新成功') : message(-1, '更新失败');
	
} elseif($action == 'enable') {
	
	$method != 'POST' AND message(-1, 'Method error');
	
	$banip_on = param('banip_on', 0);
	
	$r = conf_set('banip_on', $banip_on);
	
	$r !== FALSE ? message(0, '更新成功') : message(-1, '更新失败，请检查 conf/conf.php 是否可写！');
	
} elseif($action == 'delete') {
	
	$method != 'POST' AND message(-1, 'Method error');
	
	$banid = param(2, 0);
	$banip = banip_read($banid);
	empty($banip) AND message(0, '已经删除');
	
	$r = banip_delete($banid);
	$r !== FALSE ? message(0, '更新成功') : message(-1, '更新失败');
	
}

?>