<?php

// hook banip_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function banip__create($arr) {
	// hook banip__create_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_banip` SET $sqladd");
	// hook banip__create_end.php
	return $r;
}

function banip__update($banid, $arr) {
	// hook banip__update_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_banip` SET $sqladd WHERE banid='$banid'");
	// hook banip__update_end.php
	return $r;
}

function banip__read($banid) {
	// hook banip__read_start.php
	$banip = db_find_one("SELECT * FROM `bbs_banip` WHERE banid='$banid'");
	// hook banip__read_end.php
	return $banip;
}

function banip__delete($banid) {
	// hook banip__delete_start.php
	$r = db_exec("DELETE FROM `bbs_banip` WHERE banid='$banid'");
	// hook banip__delete_end.php
	return $r;
}

function banip__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook banip__find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$banip = db_find("SELECT * FROM `bbs_banip` $cond$orderby LIMIT $offset,$pagesize");
	// hook banip__find_end.php
	return $banip;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function banip_create($arr) {
	// hook banip_create_start.php
	$r = banip__create($arr);
	// hook banip_create_end.php
	return $r;
}

function banip_update($banid, $arr) {
	// hook banip_update_start.php
	$r = banip__update($banid, $arr);
	// hook banip_update_end.php
	return $r;
}

function banip_read($banid) {
	// hook banip_read_start.php
	$banip = banip__read($banid);
	banip_format($banip);
	// hook banip_read_end.php
	return $banip;
}

function banip_delete($banid) {
	// hook banip_delete_start.php
	$r = banip__delete($banid);
	// hook banip_delete_end.php
	return $r;
}

function banip_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook banip_find_start.php
	$baniplist = banip__find($cond, $orderby, $page, $pagesize);
	if($baniplist) foreach ($baniplist as &$banip) banip_format($banip);
	// hook banip_find_end.php
	return $baniplist;
}

function banip_read_by_ip($ip) {
	// hook banip_read_by_ip_start.php
	$ip = long2ip(ip2long($ip)); // 安全过滤
	$arr = explode('.', $ip);
	$banip = db_find_one("SELECT * FROM `bbs_banip` WHERE ip0='$arr[0]' AND ip1='$arr[1]' AND ip2='$arr[2]' AND ip3='$arr[3]' LIMIT 1");
	banip_format($banip);
	// hook banip_read_by_ip_end.php
	return $banip;
}

// ------------> 其他方法

function banip_format(&$banip) {
	// hook banip_format_start.php
	if(empty($banip)) return;
	$banip['create_date_fmt'] = date('Y-n-j', $banip['create_date']);
	$banip['expiry_fmt'] = date('Y-n-j', $banip['expiry']);
	// hook banip_format_end.php
}

function banip_maxid() {
	// hook banip_maxid_start.php
	$n = db_maxid('bbs_banip', 'banid');
	// hook banip_maxid_end.php
	return $n;
}

function banip_count() {
	// hook banip_count_start.php
	$n = db_count('bbs_banip');
	// hook banip_count_end.php
	return $n;
}


// hook banip_func_php_end.php

?>