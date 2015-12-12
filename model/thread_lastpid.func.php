<?php

function thread_lastpid_read($tid) {
	return db_find_one("SELECT * FROM `bbs_thread_lastpid` WHERE tid='$tid'");
}

function thread_lastpid__create($tid, $lastpid) {
	return db_exec("INSERT INTO `bbs_thread_lastpid` SET tid='$tid', lastpid='$lastpid'");
}

function thread_lastpid_create($tid, $lastpid) {
	$r = thread_lastpid_read($tid);
	if($r) {
		return db_exec("UPDATE `bbs_thread_lastpid` SET lastpid='$lastpid' WHERE tid='$tid'");
	} else {
		return thread_lastpid__create($tid, $lastpid);
	}
}

function thread_lastpid_delete($tid) {
	return db_exec("DELETE FROM `bbs_thread_lastpid` WHERE tid='$tid'");
}

function thread_lastpid_count() {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_lastpid`");
	return $arr['num'];
}

function thread_lastpid_find() {
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
	return $threadlist;
}

function thread_lastpid_truncate() {
	db_exec("TRUNCATE `bbs_thread_lastpid`");
	thread_lastpid_cache_delete();
}

function thread_lastpid_find_cache() {
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
	return $threadlist;
}

function thread_lastpid_cache_delete() {
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_lastpid_list');
	$deleted = TRUE;
}

// 清理最新发帖
function thread_lastpid_gc() {
	if(thread_lastpid_count() > 100) {
		$threadlist = thread_lastpid_find();
		thread_lastpid_truncate();
		foreach ($threadlist as $v) {
			thread_lastpid__create($v['tid'], $v['lastpid']);
		}
	}
}



?>