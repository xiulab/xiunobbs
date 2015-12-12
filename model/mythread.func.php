<?php


// ------------> 最原生的 CURD，无关联其他数据。因为业务逻辑简单，不需要这一层。


// ------------> 关联的 CURD，无关联其他数据。

function mythread_create($uid, $tid) {
	if($uid == 0) return TRUE; // 匿名发帖
	$thread = mythread_read($uid, $tid);
	if(empty($thread)) {
		return db_exec("INSERT INTO `bbs_mythread` SET uid='$uid', tid='$tid'");
	} else {
		return TRUE;
	}
}

function mythread_read($uid, $tid) {
	return db_find_one("SELECT * FROM `bbs_mythread` WHERE uid='$uid' AND tid='$tid' LIMIT 1");
}

function mythread_delete($uid, $tid) {
	$r = db_exec("DELETE FROM `bbs_mythread` WHERE uid='$uid' AND tid='$tid' LIMIT 1");
	return $r;
}

function mythread_delete_by_uid($uid) {
	$r = db_exec("DELETE FROM `bbs_mythread` WHERE uid='$uid'");
	return $r;
}

function mythread_delete_by_fid($fid) {
	return db_exec("DELETE FROM `bbs_mythread` WHERE fid='$fid'");
}

function mythread_delete_by_tid($tid) {
	return db_exec("DELETE FROM `bbs_mythread` WHERE tid='$tid'");
}

function mythread_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_mythread` $cond$orderby LIMIT $offset,$pagesize");
}

function mythread_find_by_uid($uid, $page = 1, $pagesize = 20) {
	$mylist = mythread_find(array('uid'=>$uid), array('tid'=>-1), $page, $pagesize);
	if(empty($mylist)) return array();
	$threadlist = array();
	foreach ($mylist as &$mythread) {
		$threadlist[$mythread['tid']] = thread_read($mythread['tid']);
	}
	return $threadlist;
}

/*function mythread_count_by_uid($uid) {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_mythread` WHERE uid='$uid'");
	return intval($arr['num']);
}
*/
?>