<?php

// 把所有的编辑历史都列出来。
function post_update_log_find_by_pid($pid) {
	// 最多 50 条
	$arrlist = db_find('post_update_log', array('pid'=>$pid), array('logid'=>-1), 1, 50, '', array('logid', 'pid', 'create_date', 'uid', 'reason'));
	foreach ($arrlist as &$arr) {
		post_update_log_format($arr);
	}
	return $arrlist;
}

// 增加一条编辑历史 $pid, $uid, $time, $message
function post_update_log_create($arr) {
	global $time;
	$pid = $arr['pid'];
	
	if(!DEBUG) {
		// 如果才发的帖子
		$post = post_read($pid);
		if($time - $post['create_date'] < 1800) {
			return 0;
		}
		
		// 如果两条记录相隔时间太短，则只记录一条
		$last = post_update_log_find_last_by_pid($pid);
		if($last && $arr['create_date'] - $last['create_date'] < 600) {
			db_update('post_update_log', array('logid'=>$last['logid']), array('message'=>$arr['message']));
			return 0;
		}
	}
	$logid = db_create('post_update_log', $arr);
	
	return $logid;
}

// 读取一条编辑历史
function post_update_log_read($logid) {
	$arr = db_find_one('post_update_log', array('logid'=>$logid));
	post_update_log_format($arr);
	return $arr;
}

// 删除一条编辑历史
function post_update_log_delete($logid) {
	$r = db_delete('post_update_log', array('logid'=>$logid));
	return $r;
}

// 查找最后一条编辑历史
function post_update_log_find_last_by_pid($pid) {
	$arr = db_find_one('post_update_log', array('pid'=>$pid), array('logid'=>-1));
	return $arr;
}

function post_update_log_format(&$arr) {
	$arr['create_date_fmt'] = humandate($arr['create_date']);
	$arr['user'] = user_read($arr['uid']);
}

?>