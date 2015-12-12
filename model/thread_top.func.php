<?php

// 置顶主题

function thread_top_change($tid, $top = 0) {
	$thread = thread__read($tid);
	if(empty($thread)) return FALSE;
	if($top != $thread['top']) {
		thread__update($tid, array('top'=>$top));
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		thread_top_cache_delete();
		thread_tids_cache_delete($fid);
		thread_new_cache_delete();
		return db_exec("REPLACE INTO `bbs_thread_top` SET fid='$fid', tid='$tid', top='$top'");
	}
	return FALSE;
}

function thread_top_delete($tid) {
	return db_exec("DELETE FROM `bbs_thread_top` WHERE tid='$tid'");
}

/*
function thread_top_read($tid) {
	return db_find_one("SELECT * FROM `bbs_thread_top` WHERE tid='$tid'");
}



// 保留 500 条最新贴
function thread_top_gc($fid) {
	return thread_top_delete($fid);
}

function thread_top_count() {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_top` WHERE top>0");
	return $arr['num'];
}

*/

function thread_top_find($fid = 0) {
	if($fid == 0) {
		$threadlist = db_find("SELECT * FROM `bbs_thread_top` WHERE top=3 ORDER BY tid DESC LIMIT 100", 'tid');
	} else {
		$threadlist = db_find("SELECT * FROM `bbs_thread_top` WHERE fid='$fid' AND top=1 ORDER BY tid DESC LIMIT 100", 'tid');
	}
	$tids = arrlist_values($threadlist, 'tid');
	$threadlist = thread_find_by_tids($tids, 1, 100);
	return $threadlist;
}

function thread_top_find_cache() {
	global $conf;
	$threadlist = cache_get('thread_top_list');
	if($threadlist === NULL) {
		$threadlist = thread_top_find();
		cache_set('thread_top_list', $threadlist);
	} else {
		// 重新格式化时间
		foreach($threadlist as &$thread) {
			thread_format_last_date($thread);
		}
	}
	return $threadlist;
}

function thread_top_cache_delete() {
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_top_list');
	$deleted = TRUE;
}

function thread_top_update_by_tid($tid, $newfid) {
	return db_exec("UPDATE bbs_thread_top SET fid='$newfid' WHERE tid='$tid'");
}
?>