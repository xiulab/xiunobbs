<?php

!defined('DEBUG') AND exit('Forbidden');

$keyword = param('keyword');
empty($keyword) AND $keyword = param(1);
$keyword = trim($keyword);
$range = param(2, 1);
$page = param(3, 1);

$keyword_decode = search_keyword_safe(xn_urldecode($keyword));
$keyword_arr = explode(' ', $keyword_decode);
$threadlist = $postlist = array();
$pagination = '';
$active = '';


$search_conf = kv_get('search_conf');
$search_type = $search_conf['type'];
$search_range = $search_conf['range'];

//$search_type = 'fulltext';

$pagesize = 20;
$threadlist = $postlist = array();

if($keyword) {
	// 搜索结果
	if($search_type == 'fulltext') {
		$keyword_decode_against = search_cn_encode($keyword_decode);
		$keyword_decode_against = '+'.str_replace(' ', ' +', $keyword_decode_against);
		
		if($range == 1) {
			$arr = db_sql_find_one("SELECT COUNT(*) AS num FROM bbs_thread_search WHERE MATCH(message) AGAINST ('$keyword_decode_against' IN BOOLEAN MODE)");
			$total = $arr['num'];

			$pagination = pagination(url("search-$keyword-$range-{page}"), $total, $page, $pagesize);

			$start = ($page - 1) * $pagesize;
			$arrlist = db_sql_find("SELECT * FROM bbs_thread_search WHERE MATCH(message) AGAINST ('$keyword_decode_against' IN BOOLEAN MODE) ORDER BY tid DESC LIMIT $start, $pagesize;");
			// echo "SELECT * FROM bbs_thread_search WHERE MATCH(message) AGAINST ('$keyword_decode_against' IN BOOLEAN MODE) LIMIT $start, $pagesize;";exit;
			$tids = arrlist_values($arrlist, 'tid');
			$threadlist = thread_find_by_tids($tids);
			$threadlist = arrlist_multisort($threadlist, 'tid', FALSE);

			// 过滤没有权限访问的主题 / filter no permission thread
			thread_list_access_filter($threadlist, $gid);

			foreach($threadlist as &$thread) {
				$thread['subject'] = search_keyword_highlight($thread['subject'], $keyword_arr);
			}
		} else if($range == 0) {

			
			//$arr = db_sql_find_one("SELECT COUNT(*) AS num FROM bbs_post_search WHERE MATCH(message) AGAINST ('$keyword_decode_against' IN BOOLEAN MODE)");
			//$total = $arr['num'];

			$total =10;

			$pagination = pagination(url("search-$keyword-$range-{page}"), $total, $page, $pagesize);

			$start = ($page - 1) * $pagesize;
			$arrlist = db_sql_find("SELECT * FROM bbs_post_search WHERE MATCH(message) AGAINST ('$keyword_decode_against' IN BOOLEAN MODE) LIMIT $start, $pagesize;"); //  ORDER BY pid DESC 
			
			$nextpage =  count($arrlist) == $pagesize ? $page + 1 : 0;
			
			$pids = arrlist_values($arrlist, 'pid');
			$postlist = post_find_by_pids($pids);
			$postlist = arrlist_multisort($postlist, 'pid', FALSE);
			
			// ...
			foreach($postlist as &$post) {
				$post['message_fmt'] = search_message_format($post['message_fmt']);
				$post['message_fmt'] = search_keyword_highlight($post['message_fmt'], $keyword_arr);
				$post['filelist'] = array();
				$post['floor'] = 0;
				$thread = thread_read_cache($post['tid']);
				$post['subject'] = search_keyword_highlight($thread['subject'], $keyword_arr);
			}

		}
		
	} elseif($search_type == 'like') {
		
		if($range == 1) {
			$threadlist = db_sql_find("SELECT * FROM bbs_thread WHERE subject LIKE '%$keyword_decode%' LIMIT 50;");
			$threadlist = arrlist_multisort($threadlist, 'tid', FALSE);
			foreach($threadlist as &$thread) {
				thread_format($thread);
				$thread['subject'] = search_keyword_highlight($thread['subject'], $keyword_arr);
			}
		} else if($range == 0) {
			$posts = 0;
			$postlist = db_sql_find("SELECT * FROM bbs_post WHERE message LIKE '%$keyword_decode%' LIMIT 50;");
			$postlist = arrlist_multisort($postlist, 'pid', FALSE);
			foreach($postlist as &$post) {
				post_format($post);
				$post['message_fmt'] = search_message_format($post['message_fmt']);
				$post['message_fmt'] = search_keyword_highlight($post['message_fmt'], $keyword_arr);
				$post['filelist'] = array();
				$post['floor'] = 0;
				$thread = thread_read_cache($post['tid']);
				$post['subject'] = search_keyword_highlight($thread['subject'], $keyword_arr);
			}
		}
		
	} elseif($search_type == 'site_url') {
		
		$range = 1;
		
		$url = str_replace('{keyword}', $keyword_decode, $search_conf['site_url']);
		http_location($url);
		
	}
}

if($ajax) {
	if($threadlist) {
		foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
		message(0, $threadlist);
	} else {
		foreach($postlist as &$post) $post = post_safe_info($post);
		message(0, $postlist);
	}
} else {
	include _include(APP_PATH.'plugin/xn_search/htm/search.htm');
}


?>