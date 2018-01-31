<?php

// ------------> 关联的 CURD，无关联其他数据。

// hook model_mypost_start.php

function mypost_create($uid, $tid) {
	// hook model_mypost_create_start.php
	if($uid == 0) return TRUE; // 匿名发帖
	$post = mypost_read($uid, $tid);
	if(empty($post)) {
		$r = db_create('mypost', array('uid'=>$uid, 'tid'=>$tid));
		return $r;
	} else {
		return TRUE;
	}
	// hook model_mypost_create_end.php
}

function mypost_read($uid, $tid) {
	// hook model_mypost_read_start.php
	$mypost = db_find_one('mypost', array('uid'=>$uid, 'tid'=>$tid));
	// hook model_mypost_read_end.php
	return $mypost;
}

function mypost_delete($uid, $tid) {
	// hook model_mypost_delete_start.php
	$r = db_delete('mypost', array('uid'=>$uid, 'tid'=>$tid));
	// hook model_mypost_delete_end.php
	return $r;
}

function mypost_delete_by_uid($uid) {
	// hook model_mypost_delete_by_uid_start.php
	$r = db_delete('mypost', array('uid'=>$uid));
	// hook model_mypost_delete_by_uid_end.php
	return $r;
}

function mypost_delete_by_fid($fid) {
	// hook model_mypost_delete_by_fid_start.php
	$r = db_delete('mypost', array('fid'=>$fid));
	// hook model_mypost_delete_by_fid_end.php
	return $r;
}

function mypost_delete_by_tid($tid) {
	// hook model_mypost_delete_by_tid_start.php
	$r = db_delete('mypost', array('tid'=>$tid));
	// hook model_mypost_delete_by_tid_end.php
	return $r;
}

function mypost_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook model_mypost_find_start.php
	$mypostlist = db_find('mypost', $cond, $orderby, $page, $pagesize);
	// hook model_mypost_find_end.php
	return $mypostlist;
}

function mypost_find_by_uid($uid, $page = 1, $pagesize = 20) {
	// hook model_mypost_find_by_uid_start.php
	$mypostlist = mypost_find(array('uid'=>$uid), array('tid'=>-1), $page, $pagesize);
	if(empty($mypostlist)) return array();
	$postlist = array();
	foreach ($mypostlist as &$mypost) {
		$postlist[$mypost['tid']] = post_read($mypost['tid']);
	}
	// hook model_mypost_find_by_uid_end.php
	return $postlist;
}

// hook model_mypost_end.php

?>