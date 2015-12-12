<?php

// ------------> 最原生的 CURD，无关联其他数据。

function banip__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_banip` SET $sqladd");
}

function banip__update($banid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_banip` SET $sqladd WHERE banid='$banid'");
}

function banip__read($banid) {
	return db_find_one("SELECT * FROM `bbs_banip` WHERE banid='$banid'");
}

function banip__delete($banid) {
	return db_exec("DELETE FROM `bbs_banip` WHERE banid='$banid'");
}

function banip__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_banip` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function banip_create($arr) {
	$r = banip__create($arr);
	return $r;
}

function banip_update($banid, $arr) {
	$r = banip__update($banid, $arr);
	return $r;
}

function banip_read($banid) {
	$banip = banip__read($banid);
	banip_format($banip);
	return $banip;
}

function banip_delete($banid) {
	$r = banip__delete($banid);
	return $r;
}

function banip_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$baniplist = banip__find($cond, $orderby, $page, $pagesize);
	if($baniplist) foreach ($baniplist as &$banip) banip_format($banip);
	return $baniplist;
}

function banip_read_by_ip($ip) {
	$ip = long2ip(ip2long($ip)); // 安全过滤
	$arr = explode('.', $ip);
	$banip = db_find_one("SELECT * FROM `bbs_banip` WHERE ip0='$arr[0]' AND ip1='$arr[1]' AND ip2='$arr[2]' AND ip3='$arr[3]' LIMIT 1");
	banip_format($banip);
	return $banip;
}

// ------------> 其他方法

function banip_format(&$banip) {
	if(empty($banip)) return;
	$banip['create_date_fmt'] = date('Y-n-j', $banip['create_date']);
	$banip['expiry_fmt'] = date('Y-n-j', $banip['expiry']);
}

function banip_maxid() {
	$n = db_maxid('bbs_banip', 'banid');
	return $n;
}

function banip_count() {
	$n = db_count('bbs_banip');
	return $n;
}

?>