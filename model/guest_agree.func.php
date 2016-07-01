<?php


// ------------> 最原生的 CURD，无关联其他数据。

function guest_agree_create($sid, $ip, $pid, $touid, $tid) {
	// hook guest_agree_create_start.php
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
	// hook guest_agree_create_end.php
}

function guest_agree_read($sid, $pid) {
	// hook guest_agree_read_start.php
	$pid = intval($pid);
	$sid = addslashes($sid);
	$agree = db_find_one("SELECT * FROM `bbs_guest_agree` WHERE sid='$sid' AND pid='$pid' LIMIT 1");
	// hook guest_agree_read_end.php
	return $agree;
}

function guest_agree_read_by_ip_pid($ip, $pid) {
	// hook guest_agree_read_by_ip_pid_start.php
	$ip = intval($ip);
	$pid = intval($pid);
	$agree = db_find_one("SELECT * FROM `bbs_guest_agree` WHERE ip='$ip' AND pid='$pid' LIMIT 1");
	// hook guest_agree_read_by_ip_pid_end.php
	return $agree;
}

function guest_agree_delete($sid, $pid, $touid, $tid) {
	// hook guest_agree_delete_start.php
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
	// hook guest_agree_delete_end.php
}

function guest_agree_delete_by_ip($ip) {
	// hook guest_agree_delete_by_ip_start.php
	$ip = intval($ip);
	$r = db_exec("DELETE FROM `bbs_guest_agree` WHERE ip='$ip'");
	// hook guest_agree_delete_by_ip_end.php
	return $r;
}

function guest_agree_count_by_sid($sid) {
	// hook guest_agree_count_by_sid_start.php
	$sid = addslashes($sid);
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_guest_agree` WHERE sid='$sid'");
	// hook guest_agree_count_by_sid_end.php
	return $arr['num'];
}

function guest_agree_count_by_ip($ip) {
	// hook guest_agree_count_by_ip_start.php
	$ip = intval($ip);
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_guest_agree` WHERE ip='$ip'");
	// hook guest_agree_count_by_ip_end.php
	return $arr['num'];
}

function guest_agree_truncate() {
	// hook guest_agree_truncate_start.php
	$r = db_exec('TRUNCATE `bbs_guest_agree`');
	// hook guest_agree_truncate_end.php
	return $r;
}

?>