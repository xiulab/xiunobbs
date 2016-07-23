<?php

// hook friendlink_func_php_start.php

function friendlink_create($arr) {
	// hook friendlink_create_start.php
	$r = db_create('bbs_friendlink', $arr);
	friendlink_list_cache_delete();
	// hook friendlink_create_end.php
	return $r;
}

function friendlink_update($linkid, $arr) {
	// hook friendlink_update_start.php
	$r = db_update('bbs_friendlink', array('linkid'=>$linkid), $arr);
	friendlink_list_cache_delete();
	// hook friendlink_update_end.php
	return $r;
}

function friendlink_read($linkid) {
	// hook friendlink_read_start.php
	$friendlink = db_find_one('bbs_friendlink', array('linkid'=>$linkid));
	// hook friendlink_read_end.php
	return $friendlink;
}

function friendlink_delete($linkid) {
	// hook friendlink_delete_start.php
	$r = db_delete('bbs_friendlink', array('linkid'=>$linkid));
	friendlink_list_cache_delete();
	// hook friendlink_delete_end.php
	return $r;
}

function friendlink_find($cond = array(), $orderby = array('rank'=>-1), $page = 1, $pagesize = 1000) {
	// hook friendlink_find_start.php
	$friendlinklist = db_find('bbs_friendlink', $cond, $orderby, $page, $pagesize, 'linkid');
	if($friendlinklist) foreach ($friendlinklist as &$friendlink) friendlink_format($friendlink);
	// hook friendlink_find_end.php
	return $friendlinklist;
}

function friendlink_find_cache($life = 300) {
	// hook friendlink_find_cache_start.php
	$friendlinklist = cache_get('friendlinklist');
	if($friendlinklist === NULL) {
		$friendlinklist = friendlink_find();
		cache_set('friendlinklist', $friendlinklist, $life);
	}
	// hook friendlink_find_cache_end.php
	return $friendlinklist;
}

function friendlink_format(&$friendlink) {
	// hook friendlink_format_start.php
	// 判断是否为二维数组
	$friendlink['create_date_fmt'] = date('Y-n-j', $friendlink['create_date']);
	// hook friendlink_format_end.php
}

function friendlink_count($cond = array()) {
	// hook friendlink_count_start.php
	$n = db_count('bbs_friendlink', $cond);
	// hook friendlink_count_end.php
	return $n;
}

function friendlink_maxid() {
	// hook friendlink_maxid_start.php
	$n = db_maxid('bbs_friendlink', 'linkid');
	// hook friendlink_maxid_end.php
	return $n;
}

// 更新 friendlinklist 缓存
function friendlink_list_cache_delete() {
	// hook friendlink_list_cache_delete_start.php
	$r = cache_delete('friendlinklist');
	// hook friendlink_list_cache_delete_end.php
	return $r;
}


// hook friendlink_func_php_end.php

?>