<?php

// ------------> 最原生的 CURD，无关联其他数据。

function ipaccess_create($arr) {
	$r = db_insert('ipaccess', $arr);
	return $r;
}

function ipaccess_update($ip, $update) {
	$r = db_update('ipaccess', array('ip'=>$ip), $update);
	return $r;
}

function ipaccess_read($ip) {
	$r = db_find_one('ipaccess', array('ip'=>$ip));
	return $r;
}

function ipaccess_delete($ip) {
	$r = db_delete('ipaccess', array('ip'=>$ip));
	return $r;
}

function ipaccess_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$arrlist = db_find('ipaccess', $cond, $orderby, $page, $pagesize);
	return $arrlist;
}


// ipaccess +1
function ipaccess_inc($ip, $action, $n = 1) {
	global $time;
	$arr = ipaccess_read($ip);
	if(empty($arr)) {
		$arr = array(
			'ip'=>$ip,
			'actions'=>1,
			'last_date'=>$time,
			$action => $n,
		);
		$r = ipaccess_create($arr);
	} else {
		$arr = array($action.'+' => $n, 'actions+'=>1, 'last_date'=>$time);
		$r = ipaccess_update($ip, $arr);
	}
	
	return $r;
}

function ipaccess_check($ip, $action) {
	
	$arr = ipaccess_read($ip);
	if(empty($arr)) return TRUE;
	if(empty($arr[$action])) return TRUE;
	
	$ipaccess = kv_get('ipaccess');
	
	if(empty($ipaccess)) return TRUE;
	if(empty($ipaccess[$action])) return TRUE;
	if($arr[$action] >= $ipaccess[$action]) return FALSE;
	return TRUE;
}

// 检测连续发帖
function ipaccess_check_seriate_threads() {
	global $uid, $gid, $conf;
	
	$ipaccess = kv_get('ipaccess');
	if(!$ipaccess['seriate_threads']) return TRUE;
	if($gid > 0 AND $gid < 5) return TRUE;
	
	$threads = 0;
	$threadlist = thread__find(array(), array(), 1, $ipaccess['seriate_threads'] * 2);
	if(empty($threadlist)) return TRUE;
	foreach($threadlist as $thread) {
		if($thread['uid'] == $uid || $uid == 0) {
			$threads++;
		}
	}
	if($threads > $ipaccess['seriate_threads']) return FALSE;
	return TRUE;
}

function ipaccess_check_seriate_posts($tid) {
	global $uid, $gid, $conf;
	
	$ipaccess = kv_get('ipaccess');
	if(!$ipaccess['seriate_posts']) return TRUE;
	if($gid > 0 AND $gid < 5) return TRUE;
	
	$posts = 0;
	$postlist = post__find(array('tid'=>$tid), array('pid'=>-1), 1, $ipaccess['seriate_posts'] * 2);
	if(empty($postlist)) return TRUE;
	foreach($postlist as $post) {
		if($post['uid'] == $uid || $uid == 0) {
			$posts++;
		}
	}
	if($posts > $ipaccess['seriate_posts']) return FALSE;
	return TRUE;
}

function ipaccess_check_seriate_users() {
	global $longip, $conf;
	
	$ipaccess = kv_get('ipaccess');
	if(!$ipaccess['seriate_users']) return TRUE;
	
	$users = 0;
	$userlist = user__find(array(), array('uid'=>-1), 1, $ipaccess['seriate_users'] * 2);
	if(empty($userlist)) return TRUE;
	foreach($userlist as $_user) {
		if($_user['create_ip'] == $longip) {
			$users++;
		}
	}
	if($users > $ipaccess['seriate_users']) return FALSE;
	return TRUE;
}

// ------------> 其他方法
function ipaccess_count() {
	$n = db_count('ipaccess');
	return $n;
}

function ipaccess_truncate() {
	return  db_truncate('ipaccess');
}

?>