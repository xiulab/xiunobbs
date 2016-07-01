<?php

// hook thread_new_func_php_start.php

function thread_new_create($tid) {
	// hook thread_new_create_start.php
	$r = db_exec("INSERT INTO `bbs_thread_new` SET tid='$tid'");
	// hook thread_new_create_end.php
	return $r;
}

function thread_new_delete($tid) {
	// hook thread_new_delete_start.php
	$r = db_exec("DELETE FROM `bbs_thread_new` WHERE tid='$tid'");
	// hook thread_new_delete_end.php
	return $r;
}

function thread_new_count() {
	// hook thread_new_count_start.php
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_thread_new`");
	// hook thread_new_count_end.php
	return $arr['num'];
}

// 最新主题
function thread_new_find() {
	// hook thread_new_find_start.php
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
	
	// hook thread_new_find_end.php
	return $threadlist;
}

function thread_new_truncate() {
	// hook thread_new_truncate_start.php
	db_exec("TRUNCATE `bbs_thread_new`");
	thread_new_cache_delete();
	// hook thread_new_truncate_end.php
}

function thread_new_find_cache() {
	// hook thread_new_find_cache_start.php
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
	// hook thread_new_find_cache_end.php
	return $threadlist;
}

function thread_new_cache_delete() {
	// hook thread_new_cache_delete_start.php
	global $conf;
	static $deleted = FALSE;
	if($deleted) return;
	cache_delete('thread_new_list');
	$deleted = TRUE;
	// hook thread_new_cache_delete_end.php
}

// 清理最新发帖
function thread_new_gc() {
	// hook thread_new_gc_start.php
	if(thread_new_count() > 100) {
		$threadlist = thread_new_find();
		thread_new_truncate();
		foreach ($threadlist as $v) {
			thread_new_create($v['tid']);
		}
	}
	// hook thread_new_gc_end.php
}

// 生成 sitemap
function thread_new_sitemap() {
	// hook thread_new_sitemap_start.php
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
	// hook thread_new_sitemap_end.php
}


// hook thread_new_func_php_end.php

?>