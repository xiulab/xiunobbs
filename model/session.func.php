<?php 

$g_session = array();	
$g_session_invalid = 0; // 0: 有效， 1：无效

// 如果是管理员, sid, 与 ip 绑定，一旦 IP 发生变化，则需要重新登录

function sess_open($save_path, $session_name) { 
	//echo "sess_open($save_path,$session_name) \r\n";
	return true;
}

function sess_close($apppath = '') {
	global $sid, $uid, $fid, $time, $g_session, $g_session_invalid;
	//echo "sess_close() \r\n";
	
	if($g_session_invalid) return TRUE;
	
	if(!empty($_SERVER['APP_PATH'])) chdir($_SERVER['APP_PATH']);
	
	$update = array(
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$_SERVER['REQUEST_URI_NO_PATH'],
		'last_date'=>$time,
	);
	$update = array_diff_value($update, $g_session);
	
	db_update('bbs_session', array('sid'=>$sid), $update);
	
	return true;
}

function sess_read($sidarg) { 
	global $g_session, $sid, $longip, $time;
	$sid = $sidarg;
	if(empty($sid)) {
		// 查找刚才是不是已经插入一条了？  如果相隔时间特别短，并且 data 为空，则删除。
		// 测试是否支持 cookie，如果不支持 cookie，则不生成 sid
		$sid = session_id();
		sess_new();
		return '';
	}
	$arr = db_find_one('bbs_session', array('sid'=>$sid));
	if(empty($arr)) {
		sess_new();
		return '';
	}
	if($arr['bigdata'] == 1) {
		$arr2 = db_find_one('bbs_session_data', array('sid'=>$sid));
		$arr['data'] = $arr2['data'];
	}
	$g_session = $arr;
	return $arr ? $arr['data'] : '';
}

function sess_new() {
	global $sid, $uid, $fid, $time, $longip, $conf;
	
	$agent = _SERVER('HTTP_USER_AGENT');
	
	// 干掉同 ip 的 sid，仅仅在遭受攻击的时候
	//db_delete('bbs_session', array('ip'=>$longip));
	
	$cookie_test = _COOKIE('cookie_test');
	if($cookie_test) {
		$cookie_test_decode = xn_decrypt($cookie_test, $conf['auth_key']);
		if($cookie_test_decode != md5($agent.$longip)) {
			// 无效的请求，可能受到攻击
			$g_session_invalid = 1;
			return;
		} else {
			setcookie('cookie_test', $cookie_test, $time - 86400, '');
		}
	} else {
		$cookie_test = xn_encrypt(md5($agent.$longip), $conf['auth_key']);
		setcookie('cookie_test', $cookie_test, $time + 86400, '');
		$g_session_invalid = 1;
		return;
	}
	
	// 可能会暴涨
	$url = _SERVER('REQUEST_URI_NO_PATH');
	
	$arr = array(
		'sid'=>$sid,
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$url,
		'last_date'=>$time,
		'fid'=>$fid,
		'data'=> '',
		'ip'=> $longip,
		'useragent'=> $agent,
		'bigdata'=> 0,
	);
	db_insert('bbs_session', $arr);
	
}

function sess_write($sid, $data) {
	global $g_session, $sid, $time, $uid, $fid, $longip, $g_session_invalid;
	
	if($g_session_invalid) return TRUE;
	
	if(!empty($_SERVER['APP_PATH'])) chdir($_SERVER['APP_PATH']);
	
	$url = _SERVER('REQUEST_URI_NO_PATH');
	$agent = _SERVER('HTTP_USER_AGENT');
	$arr = array(
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$url,
		'last_date'=>$time,
		'fid'=>$fid,
		'data'=> $data,
		'ip'=> $longip,
		'useragent'=> $agent,
		'bigdata'=> 0,
	);
	
	// 判断数据是否超长
	$data = addslashes($data);
	$len = strlen($data);
	if($len > 255 && $g_session['bigdata'] == 0) {
		db_insert('bbs_session_data', array('sid'=>$sid));
	}
	if($len <= 255) {
		$update = array_diff_value($arr, $g_session);
		db_update('bbs_session', array('sid'=>$sid), $update);
		if(!empty($g_session) && $g_session['bigdata'] == 1) {
			db_delete('bbs_session_data', array('sid'=>$sid));
		}
	} else {
		$arr['data'] = '';
		$arr['bigdata'] = 1;
		$update = array_diff_value($arr, $g_session);
		$update AND db_update('bbs_session', array('sid'=>$sid), $update);
		$update2 = array_diff_value(array('data'=>$data, 'last_date'=>$time), $g_session);
		$update2 AND db_update('bbs_session_data', array('sid'=>$sid), $update2);
	}
	return TRUE;
}

function sess_destroy($sid) { 
	//echo "sess_destroy($sid) \r\n";
	db_delete('bbs_session', array('sid'=>$sid));
	db_delete('bbs_session_data', array('sid'=>$sid));
	return TRUE; 
}

function sess_gc($maxlifetime) {
	//echo "sess_gc($maxlifetime) \r\n";
	global $time;
	$expiry = $time - $maxlifetime;
	db_delete('bbs_session', array('last_date'=>array('<'=>$expiry)));
	db_delete('bbs_session_data', array('last_date'=>array('<'=>$expiry)));
	return TRUE; 
}

function sess_start() {
	global $conf;
	ini_set('session.name', 'bbs_sid');
	
	ini_set('session.use_cookies', 'On');
	ini_set('session.use_only_cookies', 'On');
	ini_set('session.cookie_domain', '');
	ini_set('session.cookie_path', '.');	// 当前目录及下子目录，如果为空则表示仅为当前目录
	ini_set('session.cookie_secure', 'Off'); // 打开后，只有通过 https 才有效。
	ini_set('session.cookie_lifetime', 86400);
	ini_set('session.cookie_httponly', 'On'); // 打开后 js 获取不到 HTTP 设置的 cookie, 有效防止 XSS，这个对于安全很重要，除非有 BUG，否则不要关闭。
	
	ini_set('session.gc_maxlifetime', $conf['online_hold_time']);	// 活动时间 $conf['online_hold_time']
	ini_set('session.gc_probability', 1); 	// 垃圾回收概率 = gc_probability/gc_divisor
	ini_set('session.gc_divisor', 500); 	// 垃圾回收时间 5 秒，在线人数 * 10 
	
	session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc'); 
	
	// 这个比须有，否则 ZEND 会提前释放 $db 资源
	$_SERVER['APP_PATH'] = getcwd();
	
	register_shutdown_function('session_write_close', getcwd());
	
	session_start();
	
	return session_id();

}

?>