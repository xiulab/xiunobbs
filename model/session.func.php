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
	//echo "sess_read($sid) \r\n";
	$arr = db_find_one("SELECT * FROM bbs_session WHERE sid='$sid'");
	if(empty($arr)) {
		db_exec("INSERT INTO bbs_session SET sid='$sid'");
		return '';
	}
	if($arr['bigdata'] == 1) {
		$arr2 = db_find_one("SELECT * FROM bbs_session_data WHERE sid='$sid'");
		$arr['data'] = $arr2['data'];
	}
	$session = $arr;
	return $arr ? $arr['data'] : '';
}

function sess_write($sid, $data) {
	global $session, $sid, $time, $uid, $fid;
	$url = $_SERVER['REQUEST_URI_NO_PATH'];
	
	//echo "sess_write($sid, $data) \r\n";
	$arr = array(
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$_SERVER['REQUEST_URI_NO_PATH'],
		'last_date'=>$time,
		'fid'=>$fid,
		'data'=> $data,
		'bigdata'=> 0,
	);
	
	// 判断数据是否超长
	$data = addslashes($data);
	$len = strlen($data);
	if($len <= 255) {
		$sqladd = array_to_sql_update($arr, $session);
		db_exec("UPDATE bbs_session SET $sqladd WHERE sid='$sid'");
		if(!empty($session) && $session['bigdata'] == 1) {
			db_exec("DELETE FROM bbs_session_data WHERE sid='$sid'");
		}
	} else {
		$arr['data'] = '';
		$arr['bigdata'] = 1;
		$sqladd = array_to_sql_update($arr, $session);
		db_exec("UPDATE bbs_session SET $sqladd WHERE sid='$sid'");
		$arr2 = array('data'=>$data);
		$sqladd2 = array_to_sql_update($arr2, $session);
		db_exec("UPDATE bbs_session_data SET $sqladd2  WHERE sid='$sid'");
	}
	return TRUE;
} 

function sess_destroy($sid) { 
	//echo "sess_destroy($sid) \r\n";
	db_exec("DELETE FROM bbs_session WHERE sid='$sid'");
	db_exec("DELETE FROM bbs_session_data WHERE sid='$sid'");
	return TRUE; 
} 

function sess_gc($maxlifetime) {
	//echo "sess_gc($maxlifetime) \r\n";
	global $time;
	$expiry = $time - $maxlifetime;
	db_exec("DELETE FROM bbs_session WHERE last_date<$expiry");
	db_exec("DELETE FROM bbs_session_data WHERE last_date<$expiry");
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
ini_set('session.gc_divisor', 100); 	// 垃圾回收时间 5 秒

session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc'); 

// 这个比须有，否则 ZEND 会提前释放 $db 资源
register_shutdown_function('session_write_close');

session_start();

$sid = session_id();

?>