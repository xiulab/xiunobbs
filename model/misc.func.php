<?php

// 谨慎的保存配置文件，先备份，再保存。
// 这里要小心覆盖 $conf;
/*
function conf_save($replace, $file) {
	
	//file_replace_var(
	// hook conf_save_start.php
	$conf = include $file;
	$conf = array_merge($conf, $replace);
	
	file_put_contents_try($file, $s);
	global $time;
	$dir = dirname($file);
	
	$backfile = $dir.'/conf-'.date('Y-n-j', $time).'.php';
	
	$s = "<?php\r\nreturn ".var_export($conf2, true).";\r\n?>";
	// 备份文件，如果备份失败，则直接返回
	$r = copy($file, $backfile);
	if(!$r) return FALSE;
	$r = file_put_contents($file, $s, LOCK_EX); // 独占锁，防止并发写乱
	
	// 写入后，清除缓存 file 状态
	clearstatcache();
	
	if(!$r) {
		// 还原
		if(copy($backfile, $file)) {
			unlink($backfile);
		}
		return FALSE;
	}
	
	// 大致校验是否写入成功，如果写入失败，还原
	$s = file_get_contents_try($file);
	if(substr(trim($s), -2) != '?>') {
		copy($backfile, $file); // 还原
		return FALSE;
	} else {
		unlink($backfile);
	}
	
	// hook conf_save_end.php
	return TRUE;
}*/

// 正则的方式修改配置文件，不害怕 web shell 写入
// json_encode($json, JSON_PRETTY_PRINT);
/*function json_conf_set($replace, $conffile = './conf.json') {
	$s = file_get_contents($conffile);
	$arr = xn_json_decode($s);
	$arr = array_merge($arr, $replace);
	$s = xn_json_encode($arr, TRUE);
	return file_put_contents($conffile, $s, LOCK_EX);
	// hook conf_set_end.php
}*/

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

// 获取 referer
function http_referer() {
	$len = strlen(http_url_path());
	$referer = param('referer');
	empty($referer) AND $referer = _SERVER('HTTP_REFERER');
	$referer = substr($referer, $len);
	if(strpos($referer, url('user-login')) !== FALSE || strpos($referer, url('user-logout')) !== FALSE || strpos($referer, url('user-create')) !== FALSE) {
		$referer = './';
	}
	// 安全过滤，只支持站内跳转，不允许跳到外部，否则可能会被 XSS
	// $referer = str_replace('\'', '', $referer);
	if(!preg_match('#^\\??[\w\-/]+\.htm$#', $referer)) {
		$referer = './';
	}
	return $referer;
}

?>