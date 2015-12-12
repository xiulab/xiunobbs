<?php

// ------------> 最原生的 CURD，无关联其他数据。

function ipaccess_create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_ipaccess` SET $sqladd");
}

function ipaccess_update($ip, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_ipaccess` SET $sqladd WHERE ip='$ip'");
}

function ipaccess_read($ip) {
	return db_find_one("SELECT * FROM `bbs_ipaccess` WHERE ip='$ip'");
}

function ipaccess_delete($ip) {
	return db_exec("DELETE FROM `bbs_ipaccess` WHERE ip='$ip'");
}

function ipaccess_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_ipaccess` $cond$orderby LIMIT $offset,$pagesize");
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
	global $conf;
	$arr = ipaccess_read($ip);
	if(empty($arr)) return TRUE;
	if($conf['ipaccess'][$action] == 0) return TRUE;
	if($arr[$action] >= $conf['ipaccess'][$action]) return FALSE;
	return TRUE;
}

function ipaccess_check_freq($ip) {
	global $time;
	$arr = ipaccess_read($ip);
	if(empty($arr)) return TRUE;
	return $time - $arr['last_date'] > 60 ? TRUE : FALSE;
}

// ------------> 其他方法
function ipaccess_count() {
	$n = db_count('bbs_ipaccess');
	return $n;
}

function ipaccess_truncate() {
	return  db_exec('TRUNCATE `bbs_ipaccess`');
}

?>