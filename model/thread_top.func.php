<?php

// hook thread_top_func_php_start.php

// 置顶主题

function thread_top_change($tid, $top = 0) {
	// hook thread_top_change_start.php
	$thread = thread__read($tid);
	if(empty($thread)) return FALSE;
	if($top != $thread['top']) {
		thread__update($tid, array('top'=>$top));
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		thread_top_cache_delete();
		thread_tids_cache_delete($fid);
		thread_new_cache_delete();
		$r = db_exec("REPLACE INTO `bbs_thread_top` SET fid='$fid', tid='$tid', top='$top'");
		return $r;
	}
	// hook thread_top_change_end.php
	return FALSE;
}

function thread_top_delete($tid) {
	// hook thread_top_delete_start.php
	$r = db_exec("DELETE FROM `bbs_thread_top` WHERE tid='$tid'");
	// hook thread_top_delete_end.php
	return $r;
}

/*
function thread_top_read($tid) {
	// hook thread_top_read_start.php
	// hook thread_top_read_end.php
	return db_find_one("SELECT * FROM `bbs_thread_top` WHERE tid='$tid'");
}



// 保留 500 条最新贴
function thread_top_gc($fid) {
	// hook thread_top_gc_start.php
	// hook thread_top_gc_end.php
	return thread_top_delete($fid);
}

function thread_top_count() {
	// hook thread_top_count_start.php
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_top` WHERE top>0");
	// hook thread_top_count_end.php
	return $arr['num'];
}

*/

function thread_top_find($fid = 0) {
	// hook thread_top_find_start.php
	if($fid == 0) {
		$threadlist = db_find("SELECT * FROM `bbs_thread_top` WHERE top=3 ORDER BY tid DESC LIMIT 100", 'tid');
	} else {
		$threadlist = db_find("SELECT * FROM `bbs_thread_top` WHERE fid='$fid' AND top=1 ORDER BY tid DESC LIMIT 100", 'tid');
	}
	$tids = arrlist_values($threadlist, 'tid');
	$threadlist = thread_find_by_tids($tids, 1, 100);
	// hook thread_top_find_end.php
	return $threadlist;
}

function thread_top_find_cache() {
	// hook thread_top_find_cache_start.php
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
	// hook thread_top_find_cache_end.php
	return $threadlist;
}

function thread_top_cache_delete() {
	// hook thread_top_cache_delete_start.php
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_top_list');
	$deleted = TRUE;
	// hook thread_top_cache_delete_end.php
}

function thread_top_update_by_tid($tid, $newfid) {
	// hook thread_top_update_by_tid_start.php
	$r = db_exec("UPDATE bbs_thread_top SET fid='$newfid' WHERE tid='$tid'");
	// hook thread_top_update_by_tid_end.php
	return $r;
}


// hook thread_top_func_php_end.php

?>