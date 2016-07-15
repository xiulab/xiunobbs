<?php 

// 如果数据不变，则不写入。
$session = array();

function sess_open($save_path, $session_name) { 
	//echo "sess_open($save_path,$session_name) \r\n";
	return true;
}

function sess_close() {
	//echo "sess_close() \r\n";
	return true; 
}

function sess_read($sid) { 
	global $session, $sid;
	
	// 为了 ab 测试导致的 session 暴涨，只有在 $_SESSION['xxx'] 设置有值得时候，才会查询 session 表。
	if(empty($sid)) return '';
	$arr = db_find_one('bbs_session', array('sid'=>$sid));
	if(empty($arr)) {
		setcookie('sid', '', 0, '');
		return '';
	}

	if($arr['bigdata'] == 1) {
		$arr2 = db_find_one('bbs_session_data', array('sid'=>$sid));
		$arr['data'] = $arr2['data'];
	}
	$session = $arr;
	return $arr ? $arr['data'] : '';
}

function sess_write($sid, $data) {
	global $session, $sid, $time, $uid, $fid, $longip;
	$url = $_SERVER['REQUEST_URI_NO_PATH'];
	
	$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	
	$arr = array(
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$_SERVER['REQUEST_URI_NO_PATH'],
		'last_date'=>$time,
		'fid'=>$fid,
		'data'=> $data,
		'ip'=> $longip,
		'useragent'=> $agent,
		'bigdata'=> 0,
	);
	
	// 不跟踪游客的状态，仅仅是需要的时候跟踪。
	if(empty($session)) {
		$session = db_find_one('bbs_session', array('sid'=>$sid));
		empty($session) AND db_insert('bbs_session', array('sid'=>$sid));
	}
	
	// 判断数据是否超长
	$data = addslashes($data);
	$len = strlen($data);
	if($len > 255 && $session['bigdata'] == 0) {
		db_insert('bbs_session_data', array('sid'=>$sid));
	}
	if($len <= 255) {
		db_update('bbs_session', array('sid'=>$sid), array_diff($arr, $session));
		if(!empty($session) && $session['bigdata'] == 1) {
			db_delete('bbs_session_data', array('sid'=>$sid));
		}
	} else {
		$arr['data'] = '';
		$arr['bigdata'] = 1;
		$update = array_diff($arr, $session);
		$update AND db_update('bbs_session', array('sid'=>$sid), $update);
		$update2 = array_diff(array('data'=>$data, 'last_date'=>$time), $session);
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


ini_set('session.name', 'sid');

ini_set('session.use_cookies', 'On');
ini_set('session.use_only_cookies', 'On');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_path', '');
ini_set('session.cookie_secure', 'Off'); // 打开以后 sid 每次刷新会变
ini_set('session.cookie_lifetime', 86400);
ini_set('session.cookie_httponly', 'On');

ini_set('session.gc_maxlifetime', $conf['online_hold_time']);	// 活动时间 $conf['online_hold_time']
ini_set('session.gc_probability', 1); 	// 垃圾回收概率 = gc_probability/gc_divisor
ini_set('session.gc_divisor', 100); 	// 垃圾回收时间 5 秒，在线人数 * 10 

session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc'); 

// 这个比须有，否则 ZEND 会提前释放 $db 资源
register_shutdown_function('session_write_close');

session_start();

$sid = session_id();

?>