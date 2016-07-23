<?php

// ------------> 最原生的 CURD，无关联其他数据。

function modlog__create($arr) {
	// hook modlog__create_start.php
	$r = db_create('bbs_modlog', $arr);
	// hook modlog__create_end.php
	return $r;
}

function modlog__update($logid, $arr) {
	// hook modlog__update_start.php
	$r = db_update('bbs_modlog', array('logid'=>$logid), $arr);
	// hook modlog__update_end.php
	return $r;
}

function modlog__read($logid) {
	// hook modlog__read_start.php
	$modlog = db_find_one('bbs_modlog', array('logid'=>$logid));
	// hook modlog__read_end.php
	return $modlog;
}

function modlog__delete($logid) {
	// hook modlog__delete_start.php
	$r = db_delete('bbs_modlog', array('logid'=>$logid));
	// hook modlog__delete_end.php
	return $r;
}

function modlog__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook modlog__find_start.php
	$modloglist = db_find('bbs_modlog', $cond, $orderby, $page, $pagesize);
	// hook modlog__find_end.php
	return $modloglist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function modlog_create($arr) {
	// hook modlog_create_start.php
	$r = modlog__create($arr);
	// hook modlog_create_end.php
	return $r;
}

function modlog_update($logid, $arr) {
	// hook modlog_update_start.php
	$r = modlog__update($logid, $arr);
	// hook modlog_update_end.php
	return $r;
}

function modlog_read($logid) {
	// hook modlog_read_start.php
	$modlog = modlog__read($logid);
	$modlog AND modlog_format($modlog);
	// hook modlog_read_end.php
	return $modlog;
}

function modlog_delete($logid) {
	// hook modlog_delete_start.php
	$r = modlog__delete($logid);
	// hook modlog_delete_end.php
	return $r;
}

function modlog_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook modlog_find_start.php
	$modloglist = modlog__find($cond, $orderby, $page, $pagesize);
	if($modloglist) foreach ($modloglist as &$modlog) modlog_format($modlog);
	// hook modlog_find_end.php
	return $modloglist;
}

// ----------------> 其他方法

function modlog_format(&$modlog) {
	// hook modlog_format_start.php
	global $conf;
	$modlog['create_date_fmt'] = date('Y-n-j', $modlog['create_date']);
	// hook modlog_format_end.php
}

function modlog_count($cond = array()) {
	// hook modlog_count_start.php
	$n = db_count('bbs_modlog', $cond);
	// hook modlog_count_end.php
	return $n;
}

function modlog_maxid() {
	// hook modlog_maxid_start.php
	$n = db_maxid('bbs_modlog', 'logid');
	// hook modlog_maxid_end.php
	return $n;
}

?>