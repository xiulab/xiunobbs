<?php

// hook model_misc_start.php

// 检测站点的运行级别
function check_runlevel() {
	global $conf, $method, $gid;
	// hook model_check_runlevel_start.php
	
	if($gid == 1) return;
	$param0 = param(0);
	$param1 = param(1);
	if($param0 == 'user' && in_array($param1, array('login', 'craete', 'logout', 'sendinitpw', 'resetpw', 'resetpw_sendcode', 'resetpw_complete', 'synlogin'))) return;
	switch ($conf['runlevel']) {
		case 0: message(-1, $conf['runlevel_reason']); break;
		case 1: message(-1, lang('runlevel_reson_1')); break;
		case 2: ($gid == 0 || $method != 'GET') AND message(-1, lang('runlevel_reson_2')); break;
		case 3: $gid == 0 AND message(-1, lang('runlevel_reson_3')); break;
		case 4: $method != 'GET' AND message(-1, lang('runlevel_reson_4')); break;
		//case 5: break;
	}
	// hook model_check_runlevel_end.php
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
	$arr['code'] = $code.'';
	$arr['message'] = $message;
	
	// hook model_message_start.php
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit(xn_json_encode($arr)) : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode($arr);
	} else {
		if(defined('MESSAGE_HTM_PATH')) {
			include _include(MESSAGE_HTM_PATH);
		} else {
			include _include(APP_PATH."view/htm/message.htm");
		}
	}
	// hook model_message_end.php
	exit;
}

// 上锁
function xn_lock_start($lockname = '', $life = 10) {
	global $conf, $time;
	$lockfile = $conf['tmp_path'].'lock_'.$lockname.'.lock';
	if(is_file($lockfile)) {
		// 大于 $life 秒，删除锁
		if($time - filemtime($lockfile) > $life) {
			xn_unlink($lockfile);
		} else {
			// 锁存在，上锁失败。
			return FALSE;
		}
	}
	
	$r = file_put_contents($lockfile, $time, LOCK_EX);
	return $r;
}

// 删除锁
function xn_lock_end($lockname = '') {
	global $conf, $time;
	$lockfile = $conf['tmp_path'].'lock_'.$lockname.'.lock';
	xn_unlink($lockfile);
}

// hook model_misc_end.php

?>