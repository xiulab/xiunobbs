<?php

function friendlink_create($arr) {
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_friendlink` SET $sqladd");
	friendlink_list_cache_delete();
	return $r;
}

function friendlink_update($linkid, $arr) {
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_friendlink` SET $sqladd WHERE linkid='$linkid'");
	friendlink_list_cache_delete();
	return $r;
}

function friendlink_read($linkid) {
	return db_find_one("SELECT * FROM `bbs_friendlink` WHERE linkid='$linkid'");
}

function friendlink_delete($linkid) {
	$r = db_exec("DELETE FROM `bbs_friendlink` WHERE linkid='$linkid'");
	friendlink_list_cache_delete();
	return $r;
}

function friendlink_find($cond = array(), $orderby = array('rank'=>-1), $page = 1, $pagesize = 1000) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$friendlinklist = db_find("SELECT * FROM `bbs_friendlink` $cond$orderby LIMIT $offset,$pagesize", 'linkid');
	if($friendlinklist) foreach ($friendlinklist as &$friendlink) friendlink_format($friendlink);
	return $friendlinklist;
}

function friendlink_find_cache($life = 300) {
	$friendlinklist = cache_get('friendlinklist');
	if($friendlinklist === NULL) {
		$friendlinklist = friendlink_find();
		cache_set('friendlinklist', $friendlinklist, $life);
	}
	return $friendlinklist;
}

function friendlink_format(&$friendlink) {
	// 判断是否为二维数组
	$friendlink['create_date_fmt'] = date('Y-n-j', $friendlink['create_date']);
}

function friendlink_count($cond = array()) {
	return db_count('bbs_friendlink', $cond);
}

function friendlink_maxid() {
	return db_maxid('bbs_friendlink', 'linkid');
}

// 更新 friendlinklist 缓存
function friendlink_list_cache_delete() {
	return cache_delete('friendlinklist');
}

?>