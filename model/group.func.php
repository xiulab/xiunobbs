<?php

// ------------> 最原生的 CURD，无关联其他数据。

function group__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_group` SET $sqladd");
}

function group__update($gid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_group` SET $sqladd WHERE gid='$gid'");
}

function group__read($gid) {
	return db_find_one("SELECT * FROM `bbs_group` WHERE gid='$gid'");
}

function group__delete($gid) {
	return db_exec("DELETE FROM `bbs_group` WHERE gid='$gid'");
}

function group__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$grouplist = db_find("SELECT * FROM `bbs_group` $cond$orderby LIMIT $offset,$pagesize", 'gid');
	return $grouplist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function group_create($arr) {
	$r = group__create($arr);
	group_list_cache_delete();
	forum_access_padding($arr['gid'], TRUE); // 填充
	return $r;
}

function group_update($gid, $arr) {
	$r = group__update($gid, $arr);
	group_list_cache_delete();
	return $r;
}

function group_read($gid) {
	$group = group__read($gid);
	group_format($group);
	return $group;
}

function group_delete($gid) {
	$r = group__delete($gid);
	group_list_cache_delete();
	forum_access_padding($gid, FALSE); // 删除
	return $r;
}

function group_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	$grouplist = group__find($cond, $orderby, $page, $pagesize);
	if($grouplist) foreach ($grouplist as &$group) group_format($group);
	return $grouplist;
}

// ------------> 其他方法

function group_format(&$group) {
	
}


function group_count($cond = array()) {
	return db_count('bbs_group', $cond);
}

function group_maxid() {
	return db_maxid('bbs_group', 'gid');
}

// 从缓存中读取 forum_list 数据
function group_list_cache() {
	$grouplist = cache_get('grouplist');
	if($grouplist === NULL) {
		$grouplist = group_find();
		cache_set('grouplist', $grouplist);
	}
	return $grouplist;
}

// 更新 forumlist 缓存
function group_list_cache_delete() {
	return cache_delete('grouplist');
}

?>