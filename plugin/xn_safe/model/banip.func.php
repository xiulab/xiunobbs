<?php

function banip_create($arr) {
	$r = db_insert('banip', $arr);
	return $r;
}

function banip_update($banid, $arr) {
	$r = db_update('banip', array('banid'=>$banid), $arr);
	return $r;
}

function banip_read($banid) {
	$banip = db_read('banip', array('banid'=>$banid));
	banip_format($banip);
	return $banip;
}

function banip_delete($banid) {
	$r = db_delete('banip', array('banid'=>$banid));
	return $r;
}

function banip_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$baniplist = db_find('banip', $cond, $orderby, $page, $pagesize);
	if($baniplist) foreach ($baniplist as &$banip) banip_format($banip);
	return $baniplist;
}

function banip_read_by_ip($ip) {
	$ip = sprintf('%u', long2ip(ip2long($ip))); // 安全过滤
	$arr = explode('.', $ip);
	$banip = db_find_one('banip', array('ip0'=>$arr[0], 'ip1'=>$arr[1], 'ip2'=>$arr[2], 'ip3'=>$arr[3]));
	banip_format($banip);
	return $banip;
}

// ------------> 其他方法

function banip_format(&$banip) {
	if(empty($banip)) return $banip;
	$banip['create_date_fmt'] = date('Y-n-j H:i:s', $banip['create_date']);
	$banip['expiry_fmt'] = date('Y-n-j H:i:s', $banip['expiry']);
}

function banip_maxid() {
	$n = db_maxid('banip', 'banid');
	return $n;
}

function banip_count() {
	$n = db_count('banip');
	return $n;
}

?>