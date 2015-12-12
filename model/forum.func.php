<?php

// ------------> 最原生的 CURD，无关联其他数据。

function forum__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_forum` SET $sqladd");
}

function forum__update($fid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_forum` SET $sqladd WHERE fid='$fid'");
}

function forum__read($fid) {
	$forum = db_find_one("SELECT * FROM `bbs_forum` WHERE fid='$fid'");
	return $forum;
}

function forum__delete($fid) {
	return db_exec("DELETE FROM `bbs_forum` WHERE fid='$fid'");
}

function forum__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$forumlist = db_find("SELECT * FROM `bbs_forum` $cond$orderby LIMIT $offset,$pagesize", 'fid');
	return $forumlist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function forum_create($arr) {
	$r = forum__create($arr);
	forum_list_cache_delete();
	return $r;
}

function forum_update($fid, $arr) {
	$r = forum__update($fid, $arr);
	forum_list_cache_delete();
	return $r;
}

function forum_read($fid) {
	global $conf, $forumlist;
	if($conf['cache']['enable']) {
		return empty($forumlist[$fid]) ? array() : $forumlist[$fid];
	} else {
		$forum = forum__read($fid);
		forum_format($forum);
		return $forum;
	}
}

// 关联数据删除
function forum_delete($fid) {
	
	//  把板块下所有的帖子都查找出来，此处数据量大可能会超时，所以不要删除帖子特别多的板块
	$threadlist = db_find("SELECT tid, uid FROM `bbs_thread` WHERE fid='$fid'");
	foreach ($threadlist as $thread) {
		thread_delete($thread['tid']);
	}
	
	$r = forum__delete($fid);
	
	forum_list_cache_delete();
	return $r;
}

function forum_find($cond = array(), $orderby = array('rank'=>-1), $page = 1, $pagesize = 1000) {
	$forumlist = forum__find($cond, $orderby, $page, $pagesize);
	if($forumlist) foreach ($forumlist as &$forum) forum_format($forum);
	return $forumlist;
}

// ------------> 其他方法

function forum_format(&$forum) {
	global $conf;
	if(empty($forum)) return;
	$forum['create_date_fmt'] = date('Y-n-j', $forum['create_date']);
	$forum['icon_url'] = $forum['icon'] ? $conf['upload_url']."forum/$forum[fid].png" : 'static/forum.png';
	$forum['accesslist'] = $forum['accesson'] ? forum_access_find_by_fid($forum['fid']) : array();
	$forum['modlist'] = array();
	if($forum['moduids']) {
		$modlist = user_find_by_uids($forum['moduids']);
		foreach($modlist as &$mod) $mod = user_safe_info($mod);
		$forum['modlist'] = $modlist;
	}
}

function forum_count($cond = array()) {
	return db_count('bbs_forum', $cond);
}

function forum_maxid() {
	return db_maxid('bbs_forum', 'fid');
}

// 从缓存中读取 forum_list 数据x
function forum_list_cache() {
	global $conf, $forumlist;
	$forumlist = cache_get('forumlist');
	if($forumlist === NULL) {
		$forumlist = forum_find();
		$newtids = forum_new_tids();
		foreach($forumlist as &$forum) {
			$forum['newtids'] = empty($newtids[$forum['fid']]) ? array() : $newtids[$forum['fid']];
		}
		cache_set('forumlist', $forumlist, 60); // 最新发帖
	}
	
	return $forumlist;
}

// 更新 forumlist 缓存
function forum_list_cache_delete() {
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('forumlist');
	$deleted = TRUE;
}

// 获取板块的最新 tid
/*function forum_new_tids($fid) {
	global $conf, $time;
	$maxtid = table_day_maxid('bbs_thread', $time - $conf['new_thread_days'] * 86400);
	$arrlist = db_find("SELECT tid,last_date FROM `bbs_thread` WHERE fid='$fid' AND tid>'$maxtid'");
	$r = array();
	foreach($arrlist as $arr) $r[$arr['tid']] = intval($arr['last_date']);
	return $r;
}
*/

// 获取最新回复的 tid，返回数据结构： $arrlist[$fid][$tid] = $last_date;
function forum_new_tids($threadlist = array()) {
	global $conf, $time;
	empty($threadlist) AND $threadlist = thread_lastpid_find_cache();
	$r = array();
	$difftime = 86400 * $conf['new_thread_days'];
	foreach($threadlist as $arr) {
		$last_date = intval(max($arr['last_date'], $arr['create_date']));
		if($time - $last_date > $difftime) continue; // 跳过
		$r[$arr['fid']][$arr['tid']] = $last_date;
	}
	return $r;
}

// 对 $forumlist 权限过滤，查看权限没有，则隐藏
function forum_list_access_filter($forumlist, $gid, $allow = 'allowread') {
	global $conf, $group;
	if(empty($forumlist)) return array();
	$forumlist_filter = $forumlist;
	foreach($forumlist_filter as $fid=>$forum) {
		if(empty($forum['accesson']) && empty($group[$allow]) || !empty($forum['accesson']) && empty($forum['accesslist'][$gid][$allow])) {
			unset($forumlist_filter[$fid]);
		}
	}
	return $forumlist_filter;
}

?>