<?php

/*
	Xiuno BBS 4.0 插件实例：搜索设置
	admin/plugin-setting-xn_search.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);
empty($action) AND $action = 'set';

$search_conf = kv_get('search_conf');
if(empty($search_conf)) {
	$search_conf = array(
		'type'=>'like', // like|fulltext|site
		'range'=>1, // 0: all, 1: subject, 2: post
		'site_url' => 'https://www.baidu.com/s?wd=site%3A'._SERVER('HTTP_HOST').'%20{keyword}',
	);
	kv_set('search_conf', $search_conf);
}

if($action == 'set') {
	
	if($method == 'GET') {
		
		// 站内搜索：https://www.baidu.com/s?wd=site%3Abbs.xiuno.com%20%E6%96%B0%E7%89%88%E6%9C%AC
		
		$input = array();
		$input['type'] = form_radio('type', array('like'=>lang('search_type_like'), 'fulltext'=>lang('search_type_fulltext'), 'site_url'=>lang('search_type_site_url')), $search_conf['type']);
		$input['range'] = form_radio('range', array(0=>lang('all'), 1=>lang('subject'), 2=>lang('search_range_post')), $search_conf['range']);
		$input['site_url'] = form_text('site_url', $search_conf['site_url'], '100%');
		include _include(APP_PATH.'plugin/xn_search/htm/setting.htm');
		
	} else {
	
		$search_conf['type'] = param('type');
		$search_conf['range'] = param('range');
		$search_conf['site_url'] = param('site_url');
		kv_set('search_conf', $search_conf);
		
		message(0, '修改成功');
	}
	
// 切词、索引，跳转的方式开始执行任务，一次执行 10 条，如果超时，则重新开始任务。
} elseif($action == 'cn_encode') {
	
	$posts = $runtime['posts'] + $runtime['threads'];
	$input = array();
	$subject_start = intval(kv_get('xn_search_subject_start'));
	$post_start = intval(kv_get('xn_search_post_start'));
	$input['post_start'] = form_text('post_start', $post_start);
	$input['subject_start'] = form_text('subject_start', $subject_start);
	$input['range'] = form_radio('range', array(1=>lang('search_range_subject'), 2=>lang('search_range_post')), 1);
	include _include(APP_PATH.'plugin/xn_search/htm/setting_cn_encode.htm');
	
} elseif($action == 'rebuild') {
	
	$range = param(4, 1);
	$start = param(5, 0);
	$limit = $range == 0 ? 2000 : 1000;
	
	// 标题
	if($range == 1) {
		
		//empty($start) AND $start = intval(kv_get('xn_search_subject_start'));
		
		$threads = $runtime['threads'];
		$page = max(1, ceil(($start + 1) / $limit));
		$tidlist = db_find('thread', array(), array('tid'=>1), $page, $limit, 'tid', array('tid'));
		if(empty($tidlist)) {
			$start = $threads;
			kv_set('xn_search_subject_start', $start);
			message(0, jump('重建索引完毕。', url('plugin-setting-xn_search-cn_encode')));
		} else {
			$tids = arrlist_values($tidlist, 'tid');
			$threadlist = db_find('thread', array('tid'=>$tids), array(), 1, 1000, 'tid');
			foreach ($threadlist as &$thread) {
				$tid = $thread['tid'];
				$subject_cn_encode = search_cn_encode($thread['subject']);
				db_replace('thread_search', array('tid'=>$tid, 'message'=>$subject_cn_encode));
			}
			
			$start += $limit;
			kv_set('xn_search_subject_start', $start);
		}
		$url = url("plugin-setting-xn_search-rebuild-$range-$start");
		message(0, jump("正在对标题建立全文索引，主题帖总数：$threads, 当前：".($start - $limit), $url, 1));

	// 帖子
	} elseif($range == 2) {

		//empty($start) AND $start = intval(kv_get('xn_search_post_start'));
		
		$posts = $runtime['posts'] + $runtime['threads'];
		$page = max(1, ceil(($start + 1) / $limit));
		$pidlist = db_find('post', array(), array('pid'=>1), $page, $limit, 'pid', array('pid'));
		
		if(empty($pidlist)) {
			$start = $posts;
			kv_set('xn_search_post_start', $start);
			message(0, jump('重建索引完毕。', url('plugin-setting-xn_search-cn_encode')));
		} else {
			$pids = arrlist_values($pidlist, 'pid');
			$postlist = db_find('post', array('pid'=>$pids), array(), 1, $limit);
			foreach($postlist as $post) {
				$pid = $post['pid'];
				$s = strip_tags($post['message_fmt']);
				$s = preg_replace('#\[.*?\]#', '', $s);
				$message_cn_encode = search_cn_encode(strip_tags($s));
				db_replace('post_search', array('pid'=>$pid, 'message'=>$message_cn_encode));
			}
			$start += $limit;
			kv_set('xn_search_post_start', $start);
		}
		$url = url("plugin-setting-xn_search-rebuild-$range-$start");
		message(0, jump("正在建立全文索引，总贴数：$posts, 当前：".($start - $limit), $url, 5));

		
	}
}
	
?>