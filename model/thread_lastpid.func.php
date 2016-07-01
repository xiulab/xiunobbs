<?php

// hook thread_lastpid_func_php_start.php

function thread_lastpid_read($tid) {
	// hook thread_lastpid_read_start.php
	$last = db_find_one("SELECT * FROM `bbs_thread_lastpid` WHERE tid='$tid'");
	// hook thread_lastpid_read_end.php
	return $last;
}

function thread_lastpid__create($tid, $lastpid) {
	// hook thread_lastpid__create_start.php
	$r = db_exec("INSERT INTO `bbs_thread_lastpid` SET tid='$tid', lastpid='$lastpid'");
	// hook thread_lastpid__create_end.php
	return $r;
}

function thread_lastpid_create($tid, $lastpid) {
	// hook thread_lastpid_create_start.php
	$r = thread_lastpid_read($tid);
	if($r) {
		$r = db_exec("UPDATE `bbs_thread_lastpid` SET lastpid='$lastpid' WHERE tid='$tid'");
	} else {
		$r = thread_lastpid__create($tid, $lastpid);
	}
	// hook thread_lastpid_create_end.php
	return $r;
}

function thread_lastpid_delete($tid) {
	// hook thread_lastpid_delete_start.php
	$r = db_exec("DELETE FROM `bbs_thread_lastpid` WHERE tid='$tid'");
	// hook thread_lastpid_delete_end.php
	return $r;
}

function thread_lastpid_count() {
	// hook thread_lastpid_count_start.php
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_lastpid`");
	// hook thread_lastpid_count_end.php
	return $arr['num'];
}

function thread_lastpid_find() {
	// hook thread_lastpid_find_start.php
	$threadlist = db_find("SELECT * FROM `bbs_thread_lastpid` ORDER BY lastpid DESC LIMIT 100");
	
	if(empty($threadlist)) {
		// 此处特殊情况，一般不会执行到此处，无须索引
		$threadlist = thread_find(array(), array('lastpid'=>-1), 1, 100);
		foreach($threadlist as $thread) {
			thread_lastpid_create($thread['tid'], $thread['lastpid']);
		}
	} else {
		$tids = arrlist_values($threadlist, 'tid');
		$threadlist = thread_find_by_tids($tids, 1, 100, 'lastpid');
	}
	// hook thread_lastpid_find_end.php
	return $threadlist;
}

function thread_lastpid_truncate() {
	// hook thread_lastpid_truncate_start.php
	db_exec("TRUNCATE `bbs_thread_lastpid`");
	thread_lastpid_cache_delete();
	// hook thread_lastpid_truncate_end.php
}

function thread_lastpid_find_cache() {
	// hook thread_lastpid_find_cache_start.php
	global $conf, $time;
	static $cache = FALSE;
	if($cache !== FALSE) return $cache;
	$threadlist = cache_get('thread_lastpid_list');
	if($threadlist === NULL) {
		$threadlist = thread_lastpid_find();
		cache_set('thread_lastpid_list', $threadlist);
	} else {
		foreach($threadlist as &$thread) {
			$time - $thread['last_date'] < 86400 AND thread_format_last_date($thread);
		}
		
		// 重新格式化时间
		foreach($threadlist as &$thread) {
			thread_format_last_date($thread);
		}
	}
	$cache = $threadlist;
	// hook thread_lastpid_find_cache_end.php
	return $threadlist;
}

function thread_lastpid_cache_delete() {
	// hook thread_lastpid_cache_delete_start.php
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_lastpid_list');
	$deleted = TRUE;
	// hook thread_lastpid_cache_delete_end.php
}

// 清理最新发帖
function thread_lastpid_gc() {
	// hook thread_lastpid_gc_start.php
	if(thread_lastpid_count() > 100) {
		$threadlist = thread_lastpid_find();
		thread_lastpid_truncate();
		foreach ($threadlist as $v) {
			thread_lastpid__create($v['tid'], $v['lastpid']);
		}
	}
	// hook thread_lastpid_gc_end.php
}




// hook thread_lastpid_func_php_end.php

?>