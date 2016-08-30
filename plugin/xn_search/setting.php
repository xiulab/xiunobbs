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
		
		$input = array();
		$input['search_type'] = form_radio('search_type', array('fulltext'=>lang('search_type_fulltext'), 'like'=>lang('search_type_like'), 'sphinx'=>lang('search_type_sphinx')), kv_get('xn_search_type'));
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
	
	//$arr = db_sql_find("SELECT * FROM bbs_post_search WHERE   MATCH(message) AGAINST('另外 每次 ') LIMIT 10;");
	
	// 跳转的方式，对所有帖子进行切词。
	$submit = param(4, 0);
	$posts = $runtime['posts'] + $runtime['threads'];
	if(empty($submit)) {
		$start = intval(kv_get('xn_search_cutstr_start'));
		$input = array('start'=>form_text('start', $start));
		include _include(APP_PATH.'plugin/xn_search/htm/setting_cutword.htm');
	} else {
		$limit = 20;
		$start = param(5, 0);
		empty($start) AND $start = intval(kv_get('xn_search_cutstr_start'));
		$count = $runtime['posts'];
		// 可以批量提交，一次提交 20 篇回复。
		$page = max(1, ceil(($start + 1) / $limit));
		$pidlist = db_find('post', array(), array('pid'=>1), $page, $limit, 'pid', array('pid'));
		if(empty($pidlist)) {
			$start = $posts;
			kv_set('xn_search_cutstr_start', $start);
			message(0, '切词完毕，去前台体验搜索吧。');
		} else {
			$pids = arrlist_values($pidlist, 'pid');
			$postlist = db_find('post', array('pid'=>$pids), array(), 1, $limit);
			$cutword_url = kv_get('xn_search_cutword_url');
			$pidarr = arrlist_values($postlist, 'pid');
			$messagearr = arrlist_values($postlist, 'message');
			foreach($messagearr as &$message) {
				$message = strip_tags($message);
			}
			$postdata = array(
				'pid'=>$pidarr,
				'message'=>$messagearr,
			);
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
			kv_set('xn_search_cutstr_start', $start);
		}
		$url = url("plugin-setting-xn_search-cutword-1-$start");
		message(0, jump("正在切词，总数：$posts, 当前：".($start - $limit), $url, 5));
	}
	
}
	
?>