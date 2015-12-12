<?php


// ------------> 最原生的 CURD，无关联其他数据。因为业务逻辑简单，不需要这一层。


// ------------> 关联的 CURD，无关联其他数据。

function myagree_create($fromuid, $touid, $pid, $tid = 0, $isfirst = 0) {
	global $time;
	if($fromuid == 0) return TRUE; // 匿名发帖
	$post = post_read($pid);
	$r = db_exec("INSERT INTO `bbs_myagree` SET uid='$fromuid', touid='$touid', pid='$pid', tid='$tid', create_date='$time'");
	$r = db_exec("INSERT INTO `bbs_post_agree` SET uid='$fromuid', touid='$touid', pid='$pid', tid='$tid', create_date='$time'");
	if($r !== FALSE) {
		user_update($fromuid, array('myagrees+'=>1));
		user_update($touid, array('agrees+'=>1));
		post_update($pid, array('agrees+'=>1));
		$isfirst AND thread_update($tid, array('agrees+'=>1));
		
		// 改变用户组
		user_update_group($touid);
		
		return TRUE; // 0
	} else {
		return FALSE;
	}
}

function myagree_read($pid, $uid) {
	return db_find_one("SELECT * FROM `bbs_post_agree` WHERE pid='$pid' AND uid='$uid' LIMIT 1");
}

function myagree_delete($uid, $pid, $isfirst) {
	$agree = myagree_read($pid, $uid);
	if(empty($agree)) return 0;
	
	$fromuid = $agree['uid'];
	$touid = $agree['touid'];
	$tid = $agree['tid'];
	$r = db_exec("DELETE FROM `bbs_myagree` WHERE uid='$uid' AND pid='$pid' LIMIT 1");
	db_exec("DELETE FROM `bbs_post_agree` WHERE pid='$pid' AND uid='$uid' LIMIT 1");
	if($r !== FALSE) {
		user_update($fromuid, array('myagrees-'=>1));
		user_update($touid, array('agrees-'=>1));
		post_update($pid, array('agrees-'=>1));
		$isfirst AND thread_update($tid, array('agrees-'=>1));
		
		// 改变用户组
		user_update_group($touid);
		
		return $r; // 0
	} else {
		return FALSE;
	}
}

function myagree_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_myagree` $cond$orderby LIMIT $offset,$pagesize");
}

function myagree_find_by_uid($uid, $page = 1, $pagesize = 20) {
	$mylist = myagree_find(array('uid'=>$uid), array('pid'=>-1), $page, $pagesize);
	if(empty($mylist)) return array();
	$threadlist = array();
	foreach ($mylist as &$myagree) {
		$thread =  thread_read($myagree['tid']);
		if(empty($thread)) continue;
		$post =  post_read($myagree['pid']);
		if(empty($post)) continue;
		$thread['agree_create_date_fmt'] = humandate($myagree['create_date']);
		$thread['message'] = $post['isfirst'] ? '' : htmlspecialchars(mb_substr(strip_tags($post['message']), 0, 42, 'UTF-8'));
		$threadlist[$myagree['pid']] = $thread;
	}
	return $threadlist;
}

function myagree_find_pids_by_uid($uid, $page = 1, $pagesize = 20) {
	$mylist = myagree_find(array('uid'=>$uid), array('pid'=>-1), $page, $pagesize);
	if(empty($mylist)) return array();
	$threadlist = array();
	foreach ($mylist as &$myagree) {
		$thread =  thread_read($myagree['pid']);
		$thread['create_date_fmt'] = humandate($myagree['create_date']);
		$threadlist[$myagree['pid']] = $thread;
	}
	return $threadlist;
}

function myagree_count_by_uid($uid) {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_myagree` WHERE uid='$uid'");
	return intval($arr['num']);
}

function myagree_count_by_pid($pid) {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_myagree` WHERE pid='$pid'");
	return intval($arr['num']);
}


// ----------> post_agree 相关，合并到此文件，减少 include。

function post_agree_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_post_agree` $cond$orderby LIMIT $offset,$pagesize");
}

function post_agree_find_by_pid($pid, $page = 1, $pagesize = 100) {
	$agreelist = post_agree_find(array('pid'=>$pid), array('pid'=>1), $page, $pagesize);
	if(empty($agreelist)) return array();
	foreach ($agreelist as &$agree) {
		$agree['user'] = user_read_cache($agree['uid']);
		$agree['avatar_url'] = $agree['user']['avatar_url'];
		$agree['username'] = $agree['user']['username'];
		$agree['create_date_fmt'] = humandate($agree['create_date']);
	}
	return $agreelist;
}

// 更新 agree
function agree_update($touid, $pid, $tid, $fid, $isfirst) {
	global $conf, $time, $group, $longip, $sid, $uid, $gid, $user;
	
	//user_login_check($user);
	
	if(!forum_access_user($fid, $gid, 'allowagree')) return xn_error(10, '您（'.$user['groupname'].'）无权限在此版块点喜欢');
	
	if($uid > 0) {
		
		// 每日最大喜欢数限制
		if($time - $user['last_agree_date'] > 86400) {
			user__update($uid, array('last_agree_date'=>$time));
			$user['today_agrees'] = 0;
		}
		$user['last_agree_date'] = $time;
		$user['today_agrees']++;
		if($user['today_agrees'] > $group['maxagrees']) {
			return xn_error(-1, '请您休息会，您所在的用户组每日最大喜欢数为：'.$group['maxagrees']);
		}
		
		$agree = myagree_read($pid, $uid);
		if($agree) {
			// 取消喜欢
			$r = myagree_delete($uid, $pid, $isfirst);
			if($r ===  FALSE) return xn_error(2, '取消喜欢失败');
			
			thread_tids_cache_delete_by_order($fid, 'agree');
			return xn_error(1, '取消喜欢成功'); // 1 表示取喜欢喜欢，前台会根据此项判断减1
		}  else {
			// 点击喜欢
			$r = myagree_create($uid, $touid, $pid, $tid, $isfirst);
			if($r ===  FALSE) return xn_error(2, '点喜欢失败');
			
			thread_tids_cache_delete_by_order($fid, 'agree');
			return xn_error(0, '点喜欢成功');
		}
	} else {
		// ip 限制
		$n = guest_agree_count_by_ip($longip);
		if($n > $group['maxagrees']) {
			return xn_error(-1, '请您休息会，您所在的用户组每日最大喜欢数为：'.$group['maxagrees']);
		}
		
		// sid 限制
		$agree = guest_agree_read($sid, $pid);
		if($agree) {
			// 取消喜欢
			$r = guest_agree_delete($sid, $pid, $touid, ($isfirst ? $tid : 0));
			if($r ===  FALSE) return xn_error(2, '取消喜欢失败');
			thread_tids_cache_delete_by_order($fid, 'agree');
			return xn_error(1, '取消喜欢成功'); // 1 表示取消喜欢，前台会根据此项判断减1
		} else {
			// 点击喜欢
			$r = guest_agree_create($sid, $longip, $pid, $touid, ($isfirst ? $tid : 0));
			if($r ===  FALSE) return xn_error(2, '点喜欢失败');
			thread_tids_cache_delete_by_order($fid, 'agree');
			return xn_error(0, '点喜欢成功');
		}
	}
}
?>