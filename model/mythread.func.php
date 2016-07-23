<?php


// ------------> 最原生的 CURD，无关联其他数据。因为业务逻辑简单，不需要这一层。


// ------------> 关联的 CURD，无关联其他数据。

function mythread_create($uid, $tid) {
	// hook mythread_create_start.php
	if($uid == 0) return TRUE; // 匿名发帖
	$thread = mythread_read($uid, $tid);
	if(empty($thread)) {
		$r = db_create('bbs_mythread', array('uid'=>$uid, 'tid'=>$tid));
		return $r;
	} else {
		return TRUE;
	}
	// hook mythread_create_end.php
}

function mythread_read($uid, $tid) {
	// hook mythread_read_start.php
	$mythread = db_find_one('bbs_mythread', array('uid'=>$uid, 'tid'=>$tid));
	// hook mythread_read_end.php
	return $mythread;
}

function mythread_delete($uid, $tid) {
	// hook mythread_delete_start.php
	$r = db_delete('bbs_mythread', array('uid'=>$uid, 'tid'=>$tid));
	// hook mythread_delete_end.php
	return $r;
}

function mythread_delete_by_uid($uid) {
	// hook mythread_delete_by_uid_start.php
	$r = db_delete('bbs_mythread', array('uid'=>$uid));
	// hook mythread_delete_by_uid_end.php
	return $r;
}

function mythread_delete_by_fid($fid) {
	// hook mythread_delete_by_fid_start.php
	$r = db_delete('bbs_mythread', array('fid'=>$fid));
	// hook mythread_delete_by_fid_end.php
	return $r;
}

function mythread_delete_by_tid($tid) {
	// hook mythread_delete_by_tid_start.php
	$r = db_delete('bbs_mythread', array('tid'=>$tid));
	// hook mythread_delete_by_tid_end.php
	return $r;
}

function mythread_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook mythread_find_start.php
	$mythreadlist = db_find('bbs_mythread', $cond, $orderby, $page, $pagesize);
	// hook mythread_find_end.php
	return $mythreadlist;
}

function mythread_find_by_uid($uid, $page = 1, $pagesize = 20) {
	// hook mythread_find_by_uid_start.php
	$mythreadlist = mythread_find(array('uid'=>$uid), array('tid'=>-1), $page, $pagesize);
	if(empty($mythreadlist)) return array();
	$threadlist = array();
	foreach ($mythreadlist as &$mythread) {
		$threadlist[$mythread['tid']] = thread_read($mythread['tid']);
	}
	// hook mythread_find_by_uid_end.php
	return $threadlist;
}

?>