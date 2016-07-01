<?php

// hook ipaccess_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function ipaccess_create($arr) {
	// hook ipaccess_create_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_ipaccess` SET $sqladd");
	// hook ipaccess_create_end.php
	return $r;
}

function ipaccess_update($ip, $arr) {
	// hook ipaccess_update_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_ipaccess` SET $sqladd WHERE ip='$ip'");
	// hook ipaccess_update_end.php
	return $r;
}

function ipaccess_read($ip) {
	// hook ipaccess_read_start.php
	$ipaccess = db_find_one("SELECT * FROM `bbs_ipaccess` WHERE ip='$ip'");
	// hook ipaccess_read_end.php
	return $ipaccess;
}

function ipaccess_delete($ip) {
	// hook ipaccess_delete_start.php
	$r = db_exec("DELETE FROM `bbs_ipaccess` WHERE ip='$ip'");
	// hook ipaccess_delete_end.php
	return $r;
}

function ipaccess_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook ipaccess_find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$ipaccesslist = db_find("SELECT * FROM `bbs_ipaccess` $cond$orderby LIMIT $offset,$pagesize");
	// hook ipaccess_find_end.php
	return $ipacclist;
}


// ipaccess +1
function ipaccess_inc($ip, $action, $n = 1) {
	// hook ipaccess_inc_start.php
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
	
	// hook ipaccess_inc_end.php
	return $r;
}

function ipaccess_check($ip, $action) {
	// hook ipaccess_check_start.php
	global $conf;
	$arr = ipaccess_read($ip);
	if(empty($arr)) return TRUE;
	if($conf['ipaccess'][$action] == 0) return TRUE;
	if($arr[$action] >= $conf['ipaccess'][$action]) return FALSE;
	// hook ipaccess_check_end.php
	return TRUE;
}

function ipaccess_check_freq($ip) {
	// hook ipaccess_check_freq_start.php
	global $time;
	$arr = ipaccess_read($ip);
	if(empty($arr)) return TRUE;
	// hook ipaccess_check_freq_end.php
	return $time - $arr['last_date'] > 60 ? TRUE : FALSE;
}

// ------------> 其他方法
function ipaccess_count() {
	// hook ipaccess_count_start.php
	$n = db_count('bbs_ipaccess');
	// hook ipaccess_count_end.php
	return $n;
}

function ipaccess_truncate() {
	// hook ipaccess_truncate_start.php
	// hook ipaccess_truncate_end.php
	return  db_exec('TRUNCATE `bbs_ipaccess`');
}


// hook ipaccess_func_php_end.php

?>