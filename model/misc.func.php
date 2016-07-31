<?php

// 谨慎的保存配置文件，先备份，再保存。
// 这里要小心覆盖 $conf;
function conf_save($file, $conf2, $relative_path = '../') {
	
	// hook conf_save_start.php
	
	global $time;
	$dir = dirname($file);
	
	// 特殊处理几个相对路径，因为要做切换。
	if($relative_path) {
		$len = strlen($relative_path);
		if(substr($conf2['tmp_path'], 0, $len) == $relative_path) {
			$conf2['tmp_path'] = substr($conf2['tmp_path'], $len);
		}
		if(substr($conf2['log_path'], 0, $len) == $relative_path) {
			$conf2['log_path'] = substr($conf2['log_path'], $len);
		}
		if(substr($conf2['upload_path'], 0, $len) == $relative_path) {
			$conf2['upload_path'] = substr($conf2['upload_path'], $len);
		}
	}
	
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
	$s = file_get_content_try($file);
	if(substr(trim($s), -2) != '?>') {
		copy($backfile, $file); // 还原
		return FALSE;
	} else {
		unlink($backfile);
	}
	
	// hook conf_save_end.php
	return TRUE;
}

// 正则的方式修改配置文件，不害怕 web shell 写入
function json_conf_set($k, $v, $conffile = './conf.json') {
	$s = file_get_contents($conffile);
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	
	$k2 = preg_quote($k);
	foreach($arr as $line=>&$s) {
		$s = preg_replace('#"'.$k2.'"\s*:\s*".*?"#ism', "\"$k\" : \"$v\"", $s);
		$s = preg_replace('#"'.$k2.'"\s*:\s*\d+\s*#ism', "\"$k\" : $v", $s);
	}
	
	$s = implode($sep, $arr);
	// hook conf_set_end.php
	return file_put_contents($conffile, $s, LOCK_EX);
}

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
		runtime_save();
	} else {
		include "./view/htm/message.htm";
	}
	exit;
}

?>