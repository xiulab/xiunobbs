<?php

/*
	Xiuno BBS 4.0 插件实例：搜索设置
	admin/plugin-setting-xn_search.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);
empty($action) AND $action = 'set';

if($action == 'set') {
	if($method == 'GET') {
		
		// 站内搜索：https://www.baidu.com/s?wd=site%3Abbs.xiuno.com%20%E6%96%B0%E7%89%88%E6%9C%AC
		
		$input = array();
		$input['search_type'] = form_radio('search_type', array('like'=>lang('search_type_like'), 'fulltext'=>lang('search_type_fulltext'), 'sphinx'=>lang('search_type_sphinx')), kv_get('xn_search_type'));
		$input['search_range'] = form_radio('search_range', array(0=>lang('all'), 1=>lang('search_range_thread'), 2=>lang('search_range_post'), ), kv_get('xn_search_range'));
		$input['search_cutword_url'] = form_text('search_cutword_url', kv_get('xn_search_cutword_url'), '100%');
		$input['search_sphinx_url'] = form_text('search_sphinx_url', kv_get('xn_search_sphinx_url'), '100%');
		include _include(APP_PATH.'plugin/xn_search/setting.htm');
		
	} else {
	
		kv_set('xn_search_type', param('search_type'));
		kv_set('xn_search_cutword_url', param('search_cutword_url'));
		kv_set('xn_search_sphinx_url', param('search_sphinx_url'));
		
		message(0, '修改成功');
	}
	
// 切词、索引，跳转的方式开始执行任务，一次执行 10 条，如果超时，则重新开始任务。
} elseif($action == 'cutword') {
	
	$posts = $runtime['posts'] + $runtime['threads'];
	$input = array();
	$all_start = intval(kv_get('xn_search_cut_all_start'));
	$post_start = intval(kv_get('xn_search_cut_post_start'));
	$input['post_start'] = form_text('post_start', $post_start);
	$input['all_start'] = form_text('all_start', $all_start);
	$input['range'] = form_radio('range', array(0=>lang('all'), 1=>lang('search_range_thread')), kv_get('xn_search_range'));
	include _include(APP_PATH.'plugin/xn_search/htm/setting_cutword.htm');
	
} elseif($action == 'cutstep') {
	
	//$arr = db_sql_find("SELECT * FROM bbs_post_search WHERE   MATCH(message) AGAINST('另外 每次 ') LIMIT 10;");
	
	// 跳转的方式，对所有帖子进行切词。
	$limit = 20;
	$range = param(4, 0);
	$start = param(5, 0);
	// 对回帖进行切词
	if($range == 0) {
		empty($start) AND $start = intval(kv_get('xn_search_cut_post_start'));
		
		// 可以批量提交，一次提交 20 篇回复。
		$posts = $runtime['posts'] + $runtime['threads'];
		$page = max(1, ceil(($start + 1) / $limit));
		$pidlist = db_find('post', array(), array('pid'=>1), $page, $limit, 'pid', array('pid'));
		
		if(empty($pidlist)) {
			$start = $posts;
			kv_set('xn_search_cut_post_start', $start);
			message(0, '切词完毕。');
		} else {
			$pids = arrlist_values($pidlist, 'pid');
			$postlist = db_find('post', array('pid'=>$pids), array(), 1, $limit);
			$messagearr = arrlist_key_values($postlist, 'pid', 'message');
			foreach($messagearr as &$message) $message = strip_tags($message);
			
			$postdata = array('text'=>$messagearr);
			$cutword_url = kv_get('xn_search_cutword_url');
			$r = http_post($cutword_url, $postdata, 30, 3);
			$arrlist2 = xn_json_decode($r);
			if(!is_array($arrlist2)) {
				message(-1, '服务端返回数据出错：'.$r);
			}
			foreach($arrlist2 as $pid=>$arrlist) {
				$wordarr = arrlist_values($arrlist, 'word');
				$words = implode(' ', $wordarr);
				db_replace('post_search', array('pid'=>$pid, 'message'=>$words));
			}
			$start += $limit;
			kv_set('xn_search_cut_post_start', $start);
		}
		$url = url("plugin-setting-xn_search-cutstep-$range-$start");
		message(0, jump("正在切词，总贴数：$posts, 当前：".($start - $limit), $url, 5));
	} else {
		empty($start) AND $start = intval(kv_get('xn_search_cut_all_start'));
		
		// 可以批量提交，一次提交 20 篇回复。
		$threads = $runtime['threads'];
		$page = max(1, ceil(($start + 1) / $limit));
		$tidlist = db_find('thread', array(), array('tid'=>1), $page, $limit, 'tid', array('tid'));
		if(empty($tidlist)) {
			$start = $threads;
			kv_set('xn_search_cut_all_start', $start);
			message(0, '切词完毕，去前台体验搜索吧。');
		} else {
			$tids = arrlist_values($tidlist, 'tid');
			$threadlist = db_find('thread', array('tid'=>$tids), array(), 1, 1000, 'tid');
			foreach ($threadlist as &$thread) $thread['message'] = thread_firstpid_message($thread['firstpid']);
			$messagearr = arrlist_key_values($threadlist, 'tid', 'message');
			foreach($messagearr as &$message) $message = strip_tags($message);
			
			$postdata = array('text'=>$messagearr);
			$cutword_url = kv_get('xn_search_cutword_url');
			$r = http_post($cutword_url, $postdata, 30, 3);
			$arrlist2 = xn_json_decode($r);
			if(!is_array($arrlist2)) {
				message(-1, '服务端返回数据出错：'.$r);
			}
			foreach($arrlist2 as $tid=>$arrlist) {
				$wordarr = arrlist_values($arrlist, 'word');
				$words = implode(' ', $wordarr);
				db_replace('thread_search', array('tid'=>$tid, 'message'=>$words));
			}
			$start += $limit;
			kv_set('xn_search_cut_all_start', $start);
		}
		$url = url("plugin-setting-xn_search-cutstep-$range-$start");
		message(0, jump("正在切词，主题帖总数：$threads, 当前：".($start - $limit), $url, 5));
	
	}
	
	
}

function thread_firstpid_message($firstpid) {
	$post = post__read($firstpid);
	return array_value($post, 'message');
}
	
?>