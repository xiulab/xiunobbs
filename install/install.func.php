<?php

function get_env(&$env, &$write) {
	$env['os']['name'] = '操作系统';
	$env['os']['must'] = TRUE;
	$env['os']['current'] = PHP_OS;
	$env['os']['need'] = '类UNIX';
	$env['os']['status'] = 1;
	
	$env['php_version']['name'] = 'PHP版本';
	$env['php_version']['must'] = TRUE;
	$env['php_version']['current'] = PHP_VERSION;
	$env['php_version']['need'] = '5.0';
	$env['php_version']['status'] = version_compare(PHP_VERSION , '5') > 0;

	// 目录可写
	$writedir = array(
		'conf/',
		'log/',
		'tmp/',
		'upload/',
		'plugin/'
	);

	$write = array();
	foreach($writedir as &$dir) {
		$write[$dir] = xn_is_writable('./'.$dir);
	}
}


/*
	message(0, '登录成功');
	message(1, '密码错误');
	message(-1, '数据库连接失败');
	
	code:
		< 0 全局错误，比如：系统错误：数据库丢失连接/文件不可读写
		= 0 正确
		> 0 一般业务逻辑错误，可以定位到具体控件，比如：用户名为空/密码为空
*/
function install_message($code, $message) {
	global $ajax;
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit(xn_json_encode(array('code'=>$code, 'message'=>$message))) : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode(array('code'=>$code, 'message'=>$message));
	} else {
		include "./install/view/message.htm";
	}
	exit;
}

?>