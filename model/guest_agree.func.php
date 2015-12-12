<?php


// ------------> 最原生的 CURD，无关联其他数据。

function guest_agree_create($sid, $ip, $pid, $touid, $tid) {
	$sid = addslashes($sid);
	$ip = intval($ip);
	$pid = intval($pid);
	$r = db_exec("INSERT INTO `bbs_guest_agree` SET sid='$sid', ip='$ip', pid='$pid'");
	if($r !== FALSE) {
		user_update($touid, array('agrees+'=>1));
		post_update($pid, array('agrees+'=>1));
		$tid AND thread_update($tid, array('agrees+'=>1));
		
		// 改变用户组
		user_update_group($touid);
		
		return TRUE; // 0
	} else {
		return FALSE;
	}
}

function guest_agree_read($sid, $pid) {
	$pid = intval($pid);
	$sid = addslashes($sid);
	return db_find_one("SELECT * FROM `bbs_guest_agree` WHERE sid='$sid' AND pid='$pid' LIMIT 1");
}

function guest_agree_read_by_ip_pid($ip, $pid) {
	$ip = intval($ip);
	$pid = intval($pid);
	return db_find_one("SELECT * FROM `bbs_guest_agree` WHERE ip='$ip' AND pid='$pid' LIMIT 1");
}

function guest_agree_delete($sid, $pid, $touid, $tid) {
	$pid = intval($pid);
	$sid = addslashes($sid);
	$r = db_exec("DELETE FROM `bbs_guest_agree` WHERE sid='$sid' AND pid='$pid'");
	if($r !== FALSE) {
		user_update($touid, array('agrees-'=>1));
		post_update($pid, array('agrees-'=>1));
		$tid AND thread_update($tid, array('agrees-'=>1));
		
		// 改变用户组
		user_update_group($touid);
		
		return TRUE; // 0
	} else {
		return FALSE;
	}
}

function guest_agree_delete_by_ip($ip) {
	$ip = intval($ip);
	return db_exec("DELETE FROM `bbs_guest_agree` WHERE ip='$ip'");
}

function guest_agree_count_by_sid($sid) {
	$sid = addslashes($sid);
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_guest_agree` WHERE sid='$sid'");
	return $arr['num'];
}

function guest_agree_count_by_ip($ip) {
	$ip = intval($ip);
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_guest_agree` WHERE ip='$ip'");
	return $arr['num'];
}

function guest_agree_truncate() {
	return db_exec('TRUNCATE `bbs_guest_agree`');
}

?>