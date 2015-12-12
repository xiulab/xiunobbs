<?php

// ------------> 最原生的 CURD，无关联其他数据。

function modlog__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_modlog` SET $sqladd");
}

function modlog__update($logid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_modlog` SET $sqladd WHERE logid='$logid'");
}

function modlog__read($logid) {
	return db_find_one("SELECT * FROM `bbs_modlog` WHERE logid='$logid'");
}

function modlog__delete($logid) {
	return db_exec("DELETE FROM `bbs_modlog` WHERE logid='$logid'");
}

function modlog__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_modlog` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function modlog_create($arr) {
	$r = modlog__create($arr);
	return $r;
}

function modlog_update($logid, $arr) {
	$r = modlog__update($logid, $arr);
	return $r;
}

function modlog_read($logid) {
	$modlog = modlog__read($logid);
	$modlog AND modlog_format($modlog);
	return $modlog;
}

function modlog_delete($logid) {
	$r = modlog__delete($logid);
	return $r;
}

function modlog_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$modloglist = modlog__find($cond, $orderby, $page, $pagesize);
	if($modloglist) foreach ($modloglist as &$modlog) modlog_format($modlog);
	return $modloglist;
}

// ----------------> 其他方法

function modlog_format(&$modlog) {
	global $conf;
	$modlog['create_date_fmt'] = date('Y-n-j', $modlog['create_date']);
}

function modlog_count($cond = array()) {
	return db_count('bbs_modlog', $cond);
}

function modlog_maxid() {
	return db_maxid('bbs_modlog', 'logid');
}

?>