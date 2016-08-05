<?php

// 检测站点的运行级别
function check_runlevel() {
	// hook check_runlevel_start.php
	global $conf, $method, $gid;
	$is_user_action = (param(0) == 'user');
	switch ($conf['runlevel']) {
		case 0: $gid != 1 AND message(-1, $conf['runlevel_reason']); break;
		case 1: $gid != 1 AND message(-1, $conf['runlevel_reason']); break;
		case 2: ($gid == 0 OR ($gid != 1 AND $method != 'GET' AND !$is_user_action)) AND message(-1, '当前站点设置状态：会员只读'); break;
		case 3: $gid == 0 AND !$is_user_action AND message(-1, '当前站点设置状态：会员可读写，游客不允许访问'); break;
		case 4: $method != 'GET' AND message(-1, '当前站点设置状态：所有用户只读'); break;
		//case 5: break;
	}
	// hook check_runlevel_end.php
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
function message($code, $message, $extra = array()) {
	global $ajax;
	
	$arr = $extra;
	$arr['code'] = $code;
	$arr['message'] = $message;
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit(xn_json_encode($arr)) : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode($arr);
	} else {
		if(defined('MESSAGE_HTM_PATH')) {
			include MESSAGE_HTM_PATH;
		} else {
			include "./view/htm/message.htm";
		}
	}
	exit;
}

?>