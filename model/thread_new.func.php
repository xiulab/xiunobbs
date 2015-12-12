<?php

function thread_new_create($tid) {
	return db_exec("INSERT INTO `bbs_thread_new` SET tid='$tid'");
}

function thread_new_delete($tid) {
	return db_exec("DELETE FROM `bbs_thread_new` WHERE tid='$tid'");
}

function thread_new_count() {
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_new`");
	return $arr['num'];
}

// 最新主题
function thread_new_find() {
	$threadlist = db_find("SELECT * FROM `bbs_thread_new` ORDER BY tid DESC LIMIT 100");
	if(empty($threadlist)) {
		$threadlist = thread_find(array(), array('tid'=>-1), 1, 100);
		foreach($threadlist as $thread) {
			thread_new_create($thread['tid']);
		}
	} else {
		$tids = arrlist_values($threadlist, 'tid');
		$threadlist = thread_find_by_tids($tids, 1, 100, 'tid');
	}
	
	return $threadlist;
}

function thread_new_truncate() {
	db_exec("TRUNCATE `bbs_thread_new`");
	thread_new_cache_delete();
}

function thread_new_find_cache() {
	global $conf, $time;
	$threadlist = cache_get('thread_new_list');
	if($threadlist === NULL) {
		$threadlist = thread_new_find();
		cache_set('thread_new_list', $threadlist);
	} else {
		foreach($threadlist as &$thread) {
			thread_format_last_date($thread);
		}
		
		// 重新格式化时间
		foreach($threadlist as &$thread) {
			$time - $thread['last_date'] < 86400 AND thread_format_last_date($thread);
		}
	}
	return $threadlist;
}

function thread_new_cache_delete() {
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_new_list');
	$deleted = TRUE;
}

// 清理最新发帖
function thread_new_gc() {
	if(thread_new_count() > 100) {
		$threadlist = thread_new_find();
		thread_new_truncate();
		foreach ($threadlist as $v) {
			thread_new_create($v['tid']);
		}
	}
}

// 生成 sitemap
function thread_new_sitemap() {
	global $conf;
	$sitemap = $conf['upload_path'].'sitemap.xml';
	!is_file($sitemap) AND @touch($sitemap);
	if(!is_writable($sitemap)) return;
	$s = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".'<urlset>';
	$threadlist = thread_new_find();
	foreach($threadlist as $thread) {
		$s .= '
		<url>
			  <loc>'.http_url_path().$thread['url'].'</loc>
			   <lastmod>'.date('Y-m-d', $thread['last_date']).'</lastmod>
			    <changefreq>daily</changefreq>
			    <priority>1.0</priority>
		 </url>';
	}
	$s .= "\r\n</urlset>";
	file_put_contents($sitemap, $s);
}

?>