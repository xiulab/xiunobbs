<?php

// hook group_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function group__create($arr) {
	// hook group__create_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_group` SET $sqladd");
	// hook group__create_end.php
	return $r;
}

function group__update($gid, $arr) {
	// hook group__update_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_group` SET $sqladd WHERE gid='$gid'");
	// hook group__update_end.php
	return $r;
}

function group__read($gid) {
	// hook group__read_start.php
	$group = db_find_one("SELECT * FROM `bbs_group` WHERE gid='$gid'");
	// hook group__read_end.php
	return $group;
}

function group__delete($gid) {
	// hook group__delete_start.php
	$r = db_exec("DELETE FROM `bbs_group` WHERE gid='$gid'");
	// hook group__delete_end.php
	return $r;
}

function group__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	// hook group__find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$grouplist = db_find("SELECT * FROM `bbs_group` $cond$orderby LIMIT $offset,$pagesize", 'gid');
	// hook group__find_end.php
	return $grouplist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function group_create($arr) {
	// hook group_create_start.php
	$r = group__create($arr);
	group_list_cache_delete();
	forum_access_padding($arr['gid'], TRUE); // 填充
	// hook group_create_end.php
	return $r;
}

function group_update($gid, $arr) {
	// hook group_update_start.php
	$r = group__update($gid, $arr);
	group_list_cache_delete();
	// hook group_update_end.php
	return $r;
}

function group_read($gid) {
	// hook group_read_start.php
	$group = group__read($gid);
	group_format($group);
	// hook group_read_end.php
	return $group;
}

function group_delete($gid) {
	// hook group_delete_start.php
	$r = group__delete($gid);
	group_list_cache_delete();
	forum_access_padding($gid, FALSE); // 删除
	// hook group_delete_end.php
	return $r;
}

function group_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	// hook group_find_start.php
	$grouplist = group__find($cond, $orderby, $page, $pagesize);
	if($grouplist) foreach ($grouplist as &$group) group_format($group);
	// hook group_find_end.php
	return $grouplist;
}

// ------------> 其他方法

function group_format(&$group) {
	// hook group_format_start.php
	
}

function group_name($gid) {
	global $grouplist;
	return isset($grouplist[$gid]['name']) ? $grouplist[$gid]['name'] : '';
}


function group_count($cond = array()) {
	$n = db_count('bbs_group', $cond);
	// hook group_format_end.php
	return $n;
}

function group_maxid() {
	// hook group_maxid_start.php
	$n = db_maxid('bbs_group', 'gid');
	// hook group_maxid_end.php
	return $n;
}

// 从缓存中读取 forum_list 数据
function group_list_cache() {
	// hook group_list_cache_start.php
	$grouplist = cache_get('grouplist');
	if($grouplist === NULL) {
		$grouplist = group_find();
		cache_set('grouplist', $grouplist);
	}
	// hook group_list_cache_end.php
	return $grouplist;
}

// 更新 forumlist 缓存
function group_list_cache_delete() {
	// hook group_list_cache_delete_start.php
	$r = cache_delete('grouplist');
	// hook group_list_cache_delete_end.php
	return $r;
}


// hook group_func_php_end.php

?>